<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ctf_hint_purchases', function (Blueprint $table) {
            $table->unique(['ctf_challenge_id', 'user_id', 'hint_tier']);
            $table->dropUnique(['ctf_challenge_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ctf_hint_purchases', function (Blueprint $table) {
            $table->dropUnique(['ctf_challenge_id', 'user_id', 'hint_tier']);
            $table->unique(['ctf_challenge_id', 'user_id']);
        });
    }
};
