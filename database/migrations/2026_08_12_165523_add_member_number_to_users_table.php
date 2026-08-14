<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('member_number')->nullable()->unique()->after('id');
        });

        $users = DB::table('users')
            ->where('membership_status', 'active')
            ->orderByRaw('CASE WHEN approved_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('approved_at')
            ->orderBy('id')
            ->get();

        $number = 1;

        foreach ($users as $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['member_number' => $number++]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['member_number']);
            $table->dropColumn('member_number');
        });
    }
};
