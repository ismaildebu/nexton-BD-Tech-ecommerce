<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('appearance_preset', 30)->default('signature');
            $table->string('primary_color', 7)->default('#ff5b22');
            $table->string('accent_color', 7)->default('#7558e8');
            $table->string('neutral_color', 7)->default('#64748b');
            $table->string('font_family', 30)->default('jakarta');
            $table->unsignedTinyInteger('border_radius')->default(12);
            $table->unsignedSmallInteger('container_width')->default(1280);
            $table->string('density', 20)->default('comfortable');
            $table->string('header_style', 20)->default('classic');
            $table->string('footer_style', 20)->default('classic');
            $table->string('card_style', 20)->default('soft');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'appearance_preset',
                'primary_color',
                'accent_color',
                'neutral_color',
                'font_family',
                'border_radius',
                'container_width',
                'density',
                'header_style',
                'footer_style',
                'card_style',
            ]);
        });
    }
};
