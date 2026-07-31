<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        DB::table('content_pages')->insert([
            [
                'slug' => 'about',
                'title' => 'About Us',
                'content' => 'Welcome to SLAU CSIC — the Strathmore University Cybersecurity Club. We are a community of students passionate about cybersecurity, ethical hacking, and technology.',
                'meta_description' => 'About SLAU CSIC club',
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => 'This privacy policy outlines how SLAU CSIC collects, uses, and protects member information.',
                'meta_description' => 'Privacy policy',
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'terms-of-service',
                'title' => 'Terms of Service',
                'content' => 'These terms govern your membership and use of the SLAU CSIC platform.',
                'meta_description' => 'Terms of service',
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('content_pages');
    }
};
