<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_eligibilities', function (Blueprint $table) {
            $table->index(['user_id', 'eligible'], 'cert_elig_user_eligible_idx');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_eligibilities', function (Blueprint $table) {
            $table->dropIndex('cert_elig_user_eligible_idx');
        });
    }
};
