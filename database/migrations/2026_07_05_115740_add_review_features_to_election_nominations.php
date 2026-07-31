<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_nominations', function (Blueprint $table) {
            $table->json('scores')->nullable()->after('photo');
            $table->json('documents')->nullable()->after('scores');
            $table->timestamp('interview_scheduled_at')->nullable()->after('reviewed_at');
            $table->string('interview_location')->nullable()->after('interview_scheduled_at');
            $table->text('interview_notes')->nullable()->after('interview_location');
        });
    }

    public function down(): void
    {
        Schema::table('election_nominations', function (Blueprint $table) {
            $table->dropColumn(['scores', 'documents', 'interview_scheduled_at', 'interview_location', 'interview_notes']);
        });
    }
};
