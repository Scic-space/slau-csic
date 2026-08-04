<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $groups = [
            'general' => [
                'site_name' => ['value' => 'SLAU CSIC', 'type' => 'string', 'description' => 'Club/site name'],
                'club_description' => ['value' => 'Strathmore University Cybersecurity Club', 'type' => 'text', 'description' => 'Brief club description'],
                'academic_year' => ['value' => '2025/2026', 'type' => 'string', 'description' => 'Current academic year'],
                'contact_email' => ['value' => 'kevinssali23@gmail.com', 'type' => 'string', 'description' => 'Public contact email'],
            ],
            'membership' => [
                'membership_fee_amount' => ['value' => '500', 'type' => 'integer', 'description' => 'Membership fee in local currency'],
                'grace_period_days' => ['value' => '30', 'type' => 'integer', 'description' => 'Days after expiration before suspension'],
                'require_approval' => ['value' => '1', 'type' => 'boolean', 'description' => 'Require admin approval for new members'],
                'max_active_members' => ['value' => '200', 'type' => 'integer', 'description' => 'Maximum active members limit'],
            ],
            'features' => [
                'enable_ctf' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable CTF module'],
                'enable_exams' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable exams module'],
                'enable_elections' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable elections module'],
                'enable_badges' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable gamification badges'],
                'enable_finance' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable finance module'],
                'enable_teaching_sessions' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable teaching sessions module'],
            ],
            'notifications' => [
                'notify_admin_on_register' => ['value' => '1', 'type' => 'boolean', 'description' => 'Notify admins when new members register'],
                'default_reminder_hours' => ['value' => '24', 'type' => 'integer', 'description' => 'Default hours before event reminder'],
            ],
        ];

        foreach ($groups as $group => $settings) {
            foreach ($settings as $key => $data) {
                DB::table('settings')->insert([
                    'key' => $key,
                    'value' => $data['value'],
                    'type' => $data['type'],
                    'group' => $group,
                    'description' => $data['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
