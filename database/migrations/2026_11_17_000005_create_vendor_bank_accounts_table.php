<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('account_holder_name', 150);
            $table->string('bank_name', 150);
            $table->string('branch_name', 150)->nullable();
            $table->text('account_number_encrypted');
            $table->string('account_number_last_four', 4);
            $table->string('routing_number', 64)->nullable();
            $table->string('account_type', 32)->nullable();
            $table->string('verification_status', 32)->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['vendor_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_bank_accounts');
    }
};
