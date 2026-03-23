<?php

namespace App\Http\Controllers;

use App\Models\MembershipApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MembershipApplicationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payloadInput = $request->input('payload');

        if (is_string($payloadInput)) {
            $decoded = json_decode($payloadInput, true);
            $payloadInput = is_array($decoded) ? $decoded : null;
        }

        if (! is_array($payloadInput)) {
            return response()->json(['message' => 'Invalid or missing payload.'], 422);
        }

        foreach (['joinedDate', 'gymLocation', 'membershipPlan', 'gender', 'personalMedicalOther', 'familyMedicalOther'] as $key) {
            if (array_key_exists($key, $payloadInput) && $payloadInput[$key] === '') {
                $payloadInput[$key] = null;
            }
        }

        $validator = Validator::make($payloadInput, [
            'fullName' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'phoneNumber' => ['required', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:32'],
            'emergencyContact' => ['required', 'string', 'max:255'],
            'emergencyPhone' => ['required', 'string', 'max:50'],
            'membershipPlan' => ['nullable', 'string', 'max:255'],
            'gymName' => ['nullable', 'string', 'max:255'],
            'gymLocation' => ['nullable', 'string', 'max:500'],
            'joinedDate' => ['nullable', 'date'],
            'personalMedicalHistory' => ['nullable', 'array'],
            'personalMedicalHistory.*' => ['string', 'max:255'],
            'personalMedicalOther' => ['nullable', 'string', 'max:500'],
            'familyMedicalHistory' => ['nullable', 'array'],
            'familyMedicalHistory.*' => ['string', 'max:255'],
            'familyMedicalOther' => ['nullable', 'string', 'max:500'],
        ]);

        $payload = $validator->validate();

        if ($request->hasFile('member_signature')) {
            $request->validate([
                'member_signature' => ['file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            ]);
            $payload['member_signature_path'] = $request->file('member_signature')->store('membership-applications', 'public');
        }

        if ($request->hasFile('guardian_signature')) {
            $request->validate([
                'guardian_signature' => ['file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            ]);
            $payload['guardian_signature_path'] = $request->file('guardian_signature')->store('membership-applications', 'public');
        }

        $application = MembershipApplication::create(['payload' => $payload]);

        return response()->json([
            'message' => 'Membership application submitted successfully.',
            'id' => $application->id,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || strcasecmp((string) ($user->role ?? ''), 'Admin') !== 0) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $applications = MembershipApplication::query()
            ->orderByDesc('id')
            ->get()
            ->map(function (MembershipApplication $row) {
                $p = $row->payload ?? [];

                return [
                    'id' => $row->id,
                    'full_name' => $p['fullName'] ?? null,
                    'phone' => $p['phoneNumber'] ?? null,
                    'membership_plan' => $p['membershipPlan'] ?? null,
                    'gym_name' => $p['gymName'] ?? null,
                    'created_at' => $row->created_at?->toIso8601String(),
                    'payload' => $p,
                ];
            });

        return response()->json($applications);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user || strcasecmp((string) ($user->role ?? ''), 'Admin') !== 0) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $application = MembershipApplication::findOrFail($id);
        $p = $application->payload ?? [];

        return response()->json([
            'id' => $application->id,
            'created_at' => $application->created_at?->toIso8601String(),
            'updated_at' => $application->updated_at?->toIso8601String(),
            'payload' => $p,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user || strcasecmp((string) ($user->role ?? ''), 'Admin') !== 0) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $application = MembershipApplication::findOrFail($id);
        $p = $application->payload ?? [];

        foreach (['member_signature_path', 'guardian_signature_path'] as $key) {
            if (! empty($p[$key])) {
                Storage::disk('public')->delete($p[$key]);
            }
        }

        $application->delete();

        return response()->json(['message' => 'Application deleted successfully.']);
    }
}
