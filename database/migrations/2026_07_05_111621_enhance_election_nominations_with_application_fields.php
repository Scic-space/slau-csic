<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_nominations', function (Blueprint $table) {
            $table->text('manifesto')->nullable()->after('statement');
            $table->text('agenda')->nullable()->after('manifesto');
            $table->string('photo')->nullable()->after('agenda');
            $table->foreignId('reviewer_id')->nullable()->after('admin_notes')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewer_id');
            $table->timestamp('submitted_at')->nullable()->after('reviewed_at');
        });

        DB::table('election_nominations')->whereNull('submitted_at')->update([
            'submitted_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('election_nominations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewer_id');
            $table->dropColumn(['manifesto', 'agenda', 'photo', 'reviewed_at', 'submitted_at']);
        });
    }
};
