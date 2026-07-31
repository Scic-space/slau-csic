<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared("
                CREATE TABLE event_registrations_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    event_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    status VARCHAR(255) NOT NULL DEFAULT 'registered',
                    registered_at DATETIME NOT NULL,
                    attended_at DATETIME NULL,
                    notes TEXT NULL,
                    custom_fields TEXT NULL,
                    payment_completed TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    rsvp_status VARCHAR(255) NULL,
                    waitlisted_at DATETIME NULL,
                    check_in_code VARCHAR(255) NULL,
                    CHECK (rsvp_status IN ('attending', 'not_attending', 'maybe')),
                    FOREIGN KEY (event_id) REFERENCES events(id) ON UPDATE NO ACTION ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE NO ACTION ON DELETE CASCADE
                );

                INSERT INTO event_registrations_new (id, event_id, user_id, status, registered_at, attended_at, notes, custom_fields, payment_completed, created_at, updated_at, rsvp_status, waitlisted_at, check_in_code)
                SELECT id, event_id, user_id, status, registered_at, attended_at, notes, custom_fields, payment_completed, created_at, updated_at, rsvp_status, waitlisted_at, check_in_code
                FROM event_registrations;

                DROP TABLE event_registrations;
                ALTER TABLE event_registrations_new RENAME TO event_registrations;
            ");

            DB::unprepared('CREATE UNIQUE INDEX event_registrations_event_id_user_id_unique ON event_registrations(event_id, user_id)');
            DB::unprepared('CREATE UNIQUE INDEX event_registrations_check_in_code_unique ON event_registrations(check_in_code)');
            DB::unprepared('CREATE INDEX event_registrations_status_index ON event_registrations(status)');
            DB::unprepared('CREATE INDEX event_registrations_user_id_index ON event_registrations(user_id)');
        } else {
            DB::unprepared("ALTER TABLE event_registrations MODIFY COLUMN rsvp_status ENUM('attending', 'not_attending', 'maybe') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared("
                CREATE TABLE event_registrations_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    event_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    status VARCHAR(255) NOT NULL DEFAULT 'registered',
                    registered_at DATETIME NOT NULL,
                    attended_at DATETIME NULL,
                    notes TEXT NULL,
                    custom_fields TEXT NULL,
                    payment_completed TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    rsvp_status VARCHAR(255) NULL,
                    waitlisted_at DATETIME NULL,
                    check_in_code VARCHAR(255) NULL,
                    CHECK (rsvp_status IN ('attending', 'not_attending')),
                    FOREIGN KEY (event_id) REFERENCES events(id) ON UPDATE NO ACTION ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE NO ACTION ON DELETE CASCADE
                );

                INSERT INTO event_registrations_old (id, event_id, user_id, status, registered_at, attended_at, notes, custom_fields, payment_completed, created_at, updated_at, rsvp_status, waitlisted_at, check_in_code)
                SELECT id, event_id, user_id, status, registered_at, attended_at, notes, custom_fields, payment_completed, created_at, updated_at, rsvp_status, waitlisted_at, check_in_code
                FROM event_registrations;

                DROP TABLE event_registrations;
                ALTER TABLE event_registrations_old RENAME TO event_registrations;
            ");

            DB::unprepared('CREATE UNIQUE INDEX event_registrations_event_id_user_id_unique ON event_registrations(event_id, user_id)');
            DB::unprepared('CREATE UNIQUE INDEX event_registrations_check_in_code_unique ON event_registrations(check_in_code)');
            DB::unprepared('CREATE INDEX event_registrations_status_index ON event_registrations(status)');
            DB::unprepared('CREATE INDEX event_registrations_user_id_index ON event_registrations(user_id)');
        } else {
            DB::unprepared("ALTER TABLE event_registrations MODIFY COLUMN rsvp_status ENUM('attending', 'not_attending') NULL");
        }
    }
};
