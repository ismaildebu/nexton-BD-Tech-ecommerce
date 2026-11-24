<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 20)->default('home');
            $table->string('full_name', 150);
            $table->string('phone', 20);
            $table->string('address_line', 500);
            // Plain text columns, compatible with a future location module.
            $table->string('division', 100);
            $table->string('district', 100);
            $table->string('upazila', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->boolean('is_default')->default(false);

            // Holds user_id only for the default address, otherwise NULL.
            // The unique index below therefore guarantees one default address per user.
            $table->unsignedBigInteger('default_owner_id')
                ->nullable()
                ->storedAs(DB::getDriverName() === 'sqlite'
                    ? 'CASE WHEN is_default = 1 THEN user_id ELSE NULL END'
                    : 'IF(is_default = 1, user_id, NULL)');
            $table->unique('default_owner_id', 'addresses_one_default_per_user_unique');

            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
