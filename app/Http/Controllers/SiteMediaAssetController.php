<?php

namespace App\Http\Controllers;

use App\Models\SiteMediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteMediaAssetController extends Controller
{
    private const DEFAULT_ASSETS = [
        ['section' => 'Homepage', 'item_key' => 'homepage.main_video', 'label' => 'Main Video', 'usage_hint' => 'Homepage hero background video', 'media_type' => 'video', 'recommended_size' => '16:9, MP4, max 50MB', 'sort_order' => 1],
        ['section' => 'Homepage', 'item_key' => 'homepage.gallery_image_1', 'label' => 'Gallery Image 1', 'usage_hint' => 'Homepage gallery tile 1', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 2],
        ['section' => 'Homepage', 'item_key' => 'homepage.gallery_image_2', 'label' => 'Gallery Image 2', 'usage_hint' => 'Homepage gallery tile 2', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 3],
        ['section' => 'Homepage', 'item_key' => 'homepage.gallery_image_3', 'label' => 'Gallery Image 3', 'usage_hint' => 'Homepage gallery tile 3', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 4],
        ['section' => 'Homepage', 'item_key' => 'homepage.gallery_image_4', 'label' => 'Gallery Image 4', 'usage_hint' => 'Homepage gallery tile 4', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 5],
        ['section' => 'Homepage', 'item_key' => 'homepage.gallery_image_5', 'label' => 'Gallery Image 5', 'usage_hint' => 'Homepage gallery tile 5', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 6],
        ['section' => 'Homepage', 'item_key' => 'homepage.gallery_image_6', 'label' => 'Gallery Image 6', 'usage_hint' => 'Homepage gallery tile 6', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 7],

        ['section' => 'MMA Page', 'item_key' => 'mma.main_image', 'label' => 'Main Image', 'usage_hint' => 'MMA hero section background', 'media_type' => 'image', 'recommended_size' => '1920x1080 (16:9)', 'sort_order' => 8],
        ['section' => 'MMA Page', 'item_key' => 'mma.philosophy_image', 'label' => 'Philosophy Section Image', 'usage_hint' => 'MMA philosophy block image', 'media_type' => 'image', 'recommended_size' => '1200x1600 (3:4)', 'sort_order' => 9],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_coach_image_1', 'label' => 'Combat Coaches Image 1', 'usage_hint' => 'MMA coaches section gallery card 1', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 10],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_coach_image_2', 'label' => 'Combat Coaches Image 2', 'usage_hint' => 'MMA coaches section gallery card 2', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 11],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_coach_image_3', 'label' => 'Combat Coaches Image 3', 'usage_hint' => 'MMA coaches section gallery card 3', 'media_type' => 'image', 'recommended_size' => '1200x900 (4:3)', 'sort_order' => 12],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_gallery_image_1', 'label' => 'Combat Gallery Image 1', 'usage_hint' => 'MMA combat gallery slot 1', 'media_type' => 'image', 'recommended_size' => '1400x900', 'sort_order' => 13],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_gallery_image_2', 'label' => 'Combat Gallery Image 2', 'usage_hint' => 'MMA combat gallery slot 2', 'media_type' => 'image', 'recommended_size' => '1400x900', 'sort_order' => 14],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_gallery_image_3', 'label' => 'Combat Gallery Image 3', 'usage_hint' => 'MMA combat gallery slot 3', 'media_type' => 'image', 'recommended_size' => '1400x900', 'sort_order' => 15],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_gallery_image_4', 'label' => 'Combat Gallery Image 4', 'usage_hint' => 'MMA combat gallery slot 4', 'media_type' => 'image', 'recommended_size' => '1400x900', 'sort_order' => 16],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_gallery_image_5', 'label' => 'Combat Gallery Image 5', 'usage_hint' => 'MMA combat gallery slot 5', 'media_type' => 'image', 'recommended_size' => '1400x900', 'sort_order' => 17],
        ['section' => 'MMA Page', 'item_key' => 'mma.combat_gallery_image_6', 'label' => 'Combat Gallery Image 6', 'usage_hint' => 'MMA combat gallery slot 6', 'media_type' => 'image', 'recommended_size' => '1400x900', 'sort_order' => 18],
        ['section' => 'MMA Page', 'item_key' => 'mma.ready_to_commit_image', 'label' => 'Ready to Commit Section Image', 'usage_hint' => 'MMA final CTA background image', 'media_type' => 'image', 'recommended_size' => '1920x1080 (16:9)', 'sort_order' => 19],

        ['section' => 'Blog', 'item_key' => 'blog.main_image', 'label' => 'Main Image', 'usage_hint' => 'Blog listing page hero image', 'media_type' => 'image', 'recommended_size' => '1920x1080 (16:9)', 'sort_order' => 20],
        ['section' => 'Contact Page', 'item_key' => 'contact.main_image', 'label' => 'Main Image', 'usage_hint' => 'Contact page hero image', 'media_type' => 'image', 'recommended_size' => '1920x1080 (16:9)', 'sort_order' => 21],
        ['section' => 'Planning Page', 'item_key' => 'planning.main_image', 'label' => 'Main Image', 'usage_hint' => 'Pricing/planning page hero image', 'media_type' => 'image', 'recommended_size' => '1920x1080 (16:9)', 'sort_order' => 22],
    ];

    public function index()
    {
        $this->syncDefaults();

        $assets = SiteMediaAsset::orderBy('sort_order')->get()->map(fn (SiteMediaAsset $asset) => $this->transformAsset($asset));
        $grouped = $assets->groupBy('section')->map(fn ($items) => $items->values());
        $mapped = $assets->keyBy('item_key');

        return response()->json([
            'sections' => $grouped,
            'assets' => $mapped,
        ]);
    }

    public function update(Request $request, string $itemKey)
    {
        $asset = SiteMediaAsset::where('item_key', $itemKey)->firstOrFail();

        $rules = $asset->media_type === 'video'
            ? ['file' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:51200']]
            : ['file' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:8192']];

        $validated = $request->validate($rules);

        if ($asset->file_path) {
            Storage::disk('public')->delete($asset->file_path);
        }

        $folder = $asset->media_type === 'video' ? 'site_media/videos' : 'site_media/images';
        $asset->file_path = $validated['file']->store($folder, 'public');
        $asset->save();

        return response()->json([
            'message' => "{$asset->label} updated successfully.",
            'asset' => $this->transformAsset($asset),
        ]);
    }

    private function syncDefaults(): void
    {
        foreach (self::DEFAULT_ASSETS as $default) {
            SiteMediaAsset::updateOrCreate(
                ['item_key' => $default['item_key']],
                [
                    'section' => $default['section'],
                    'label' => $default['label'],
                    'usage_hint' => $default['usage_hint'],
                    'media_type' => $default['media_type'],
                    'recommended_size' => $default['recommended_size'],
                    'sort_order' => $default['sort_order'],
                ]
            );
        }
    }

    private function transformAsset(SiteMediaAsset $asset): array
    {
        return [
            'id' => $asset->id,
            'section' => $asset->section,
            'item_key' => $asset->item_key,
            'label' => $asset->label,
            'usage_hint' => $asset->usage_hint,
            'media_type' => $asset->media_type,
            'recommended_size' => $asset->recommended_size,
            'sort_order' => $asset->sort_order,
            'file_path' => $asset->file_path,
            'file_url' => $asset->file_path ? url('storage/' . $asset->file_path) : null,
        ];
    }
}

