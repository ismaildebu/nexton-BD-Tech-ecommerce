<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('vendor_code', 32)->unique();
            $table->string('business_name', 255);
            $table->string('business_type', 100)->nullable();
            $table->string('business_email', 255)->nullable();
            $table->string('business_phone', 32)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->text('business_address')->nullable();
            $table->string('district', 100)->nullable();
            $table->string('upazila', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->text('business_description')->nullable();
            $table->string('verification_status', 32)->default('draft');
            $table->string('status', 32)->default('inactive');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['owner_user_id']);
            $table->index(['verification_status', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
