<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('portfolio_slug')->nullable()->after('registration_number');
            $table->boolean('portfolio_is_public')->default(false)->after('portfolio_slug');
            $table->string('personal_website')->nullable()->after('linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['portfolio_slug', 'portfolio_is_public', 'personal_website']);
        });
    }
};
