<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_eligibilities', function (Blueprint $table) {
            $table->string('verification_code', 36)->nullable()->unique()->after('id');
        });

        // Backfill existing records with unique verification codes
        $eligibilities = DB::table('certificate_eligibilities')->pluck('id');
        foreach ($eligibilities as $id) {
            DB::table('certificate_eligibilities')
                ->where('id', $id)
                ->update(['verification_code' => Str::uuid()->toString()]);
        }
    }

    public function down(): void
    {
        Schema::table('certificate_eligibilities', function (Blueprint $table) {
            $table->dropColumn('verification_code');
        });
    }
};
