<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('membership_id')->nullable();
            $table->integer('amount');
            $table->integer('discount')->default(0);
            $table->integer('paid_amount')->default(0);
            $table->integer('due_amount')->virtualAs('COALESCE(amount, 0) - COALESCE(discount, 0) - COALESCE(paid_amount, 0)');
            $table->enum('status', ['Paid', 'Unpaid'])->virtualAs('CASE WHEN COALESCE(due_amount, 0) = 0 THEN "Paid" ELSE "Unpaid" END');
            $table->timestamp('paid_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->date('expire_date')->nullable(); 
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('membership_id')->references('membership_id')->on('memberships')
                  ->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

