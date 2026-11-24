<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 120);
            $table->string('logo_path', 255)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->string('announcement', 255)->nullable();
            $table->text('footer_description')->nullable();
            $table->string('copyright_text', 255);
            $table->timestamps();
        });

        DB::table('site_settings')->insert([
            'site_name' => 'NEXTON',
            'announcement' => 'Free nationwide delivery on orders over ৳2,500.',
            'footer_description' => 'The premium multivendor commerce platform in Bangladesh. Discover verified merchants, curated electronics, gadgets, and design essentials.',
            'copyright_text' => 'Nexton BD Tech E-Commerce Ltd. All rights reserved.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
