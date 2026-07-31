<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ctf_submissions', function (Blueprint $table) {
            $table->dropUnique('ctf_submissions_ctf_challenge_id_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ctf_submissions', function (Blueprint $table) {
            $table->unique(['ctf_challenge_id', 'user_id']);
        });
    }
};
