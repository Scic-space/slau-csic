<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctf_hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_challenge_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('tier')->default(0);
            $table->text('content');
            $table->integer('cost')->default(0);
            $table->timestamps();

            $table->unique(['ctf_challenge_id', 'tier']);
        });

        // Add hint_tier to hint purchases
        Schema::table('ctf_hint_purchases', function (Blueprint $table) {
            $table->unsignedTinyInteger('hint_tier')->default(0)->after('user_id');
        });

        // Migrate existing hints from challenges table
        $challenges = DB::table('ctf_challenges')->whereNotNull('hint')->get(['id', 'hint', 'hint_cost']);
        foreach ($challenges as $c) {
            DB::table('ctf_hints')->insert([
                'ctf_challenge_id' => $c->id,
                'tier' => 0,
                'content' => $c->hint,
                'cost' => $c->hint_cost,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('ctf_challenges', function (Blueprint $table) {
            $table->dropColumn(['hint', 'hint_cost']);
        });

        Schema::table('ctf_challenges', function (Blueprint $table) {
            $table->boolean('flag_case_sensitive')->default(true)->after('flag_hash');
            $table->foreignId('depends_on_challenge_id')
                ->nullable()
                ->constrained('ctf_challenges')
                ->nullOnDelete()
                ->after('solve_count');
        });
    }

    public function down(): void
    {
        Schema::table('ctf_hint_purchases', function (Blueprint $table) {
            $table->dropColumn('hint_tier');
        });

        Schema::table('ctf_challenges', function (Blueprint $table) {
            $table->dropForeign(['depends_on_challenge_id']);
            $table->dropColumn(['flag_case_sensitive', 'depends_on_challenge_id']);
            $table->text('hint')->nullable()->after('is_active');
            $table->integer('hint_cost')->default(0)->after('hint');
        });

        // Restore hints from first tier
        DB::statement('UPDATE ctf_challenges c JOIN ctf_hints h ON h.ctf_challenge_id = c.id AND h.tier = 0 SET c.hint = h.content, c.hint_cost = h.cost');

        Schema::dropIfExists('ctf_hints');
    }
};
