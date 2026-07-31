<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_portfolios', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
            $table->string('tech_stack')->nullable()->after('category');
            $table->string('screenshot_path')->nullable()->after('tech_stack');
            $table->string('repo_url')->nullable()->after('screenshot_path');
            $table->string('live_url')->nullable()->after('repo_url');
            $table->unsignedInteger('sort_order')->default(0)->after('live_url');
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('student_portfolios', function (Blueprint $table) {
            $table->dropColumn(['slug', 'tech_stack', 'screenshot_path', 'repo_url', 'live_url', 'sort_order']);
        });
    }
};
