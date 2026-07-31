<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('event_recurrence');
    }

    public function down(): void
    {
        // Intentionally not recreating an orphaned table
    }
};
