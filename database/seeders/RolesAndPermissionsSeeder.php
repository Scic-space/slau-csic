<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        $permissions = [
            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'approve_members',
            'suspend_members',
            'revoke_membership',

            // Meeting Management
            'view_meetings',
            'create_meetings',
            'edit_meetings',
            'delete_meetings',
            'generate_qr_codes',
            'open_attendance',
            'close_attendance',

            // Attendance
            'view_attendance',
            'record_attendance_manual',
            'edit_attendance',
            'export_attendance',
            'view_own_attendance',

            // Event Management
            'view_events',
            'create_events',
            'edit_events',
            'delete_events',
            'publish_events',
            'cancel_events',
            'manage_registrations',
            'manage_attendance',
            'view_event_feedback',
            'view_event_analytics',
            'view_member_history',

            // Project Management
            'view_projects',
            'create_projects',
            'edit_projects',
            'delete_projects',
            'assign_project_members',

            // Training Management
            'view_trainings',
            'create_trainings',
            'edit_trainings',
            'delete_trainings',
            'manage_enrollments',
            'create_ctf_challenges',

            // Financial
            'view_budget',
            'manage_budget',
            'approve_expenditures',
            'create_financial_reports',

            // Communication
            'send_announcements',
            'send_club_wide_emails',
            'manage_notifications',

            // Media & Content
            'manage_social_media',
            'upload_media',
            'edit_website_content',
            'create_blog_posts',

            // Research & Innovation
            'propose_research',
            'manage_innovation_lab',
            'create_technical_content',

            // Discipline & Security
            'view_conduct_violations',
            'issue_warnings',
            'recommend_termination',
            'manage_security_incidents',

            // Reports & Analytics
            'view_reports',
            'export_reports',
            'view_analytics',

            // Competition
            'register_competitions',
            'manage_competition_teams',

            // Voting
            'vote_in_elections',

            // System
            'access_admin_panel',
            'manage_roles',
            'manage_permissions',
            'view_audit_logs',
            'manage_settings',

            // Assignments
            'view_assignments',
            'manage_assignments',

            // Content & Course Materials
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',

            // Teacher & Education
            'teacher.events.view',
            'teacher.events.rsvp',
            'teacher.reports.view',
            'teacher.dashboard.view',

            // Student Portfolios
            'portfolio.view',
            'portfolio.manage',

            // Teaching Sessions
            'mark attendance',
            'create teaching session',
            'edit teaching session',
            'delete teaching session',
            'start teaching session',
            'end teaching session',
            'view leaderboard',
            'checkin via qr',
            'take eligibility snapshot',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $allPermissionNames = Permission::query()->pluck('name')->all();

        $this->renameRoles([
            'president' => 'President',
            'vice_president' => 'Vice President',
            'secretary' => 'General Secretary',
            'treasurer' => 'Treasurer',
            'head_projects' => 'Head of Projects',
            'head_ctf' => 'CTF Lead',
            'head_media' => 'Public Relations',
            'head_innovations' => 'Lead Developer',
            'head_discipline' => 'Technical Lead',
        ]);

        $admin = $this->syncRole('admin', $allPermissionNames);
        $superAdmin = $this->syncRole('super-admin', $allPermissionNames);

        User::where('email', '=', 'kevinssali23@gmail.com')?->first()?->assignRole($admin, $superAdmin);

        $rolePermissions = [
            'President' => [
                'view_users', 'edit_users', 'approve_members', 'suspend_members',
                'view_meetings', 'create_meetings', 'edit_meetings', 'delete_meetings',
                'generate_qr_codes', 'open_attendance', 'close_attendance',
                'view_attendance', 'export_attendance',
                'view_events', 'create_events', 'edit_events', 'delete_events',
                'publish_events', 'manage_registrations',
                'view_projects', 'create_projects', 'edit_projects',
                'view_trainings', 'view_budget', 'approve_expenditures',
                'send_announcements', 'send_club_wide_emails',
                'view_reports', 'export_reports', 'view_analytics',
                'access_admin_panel', 'view_audit_logs',
                'view_assignments',
                'vote_in_elections',
                'content.view', 'content.create', 'content.edit', 'content.delete',
                'teacher.events.view', 'teacher.events.rsvp', 'teacher.reports.view', 'teacher.dashboard.view',
                'portfolio.view', 'portfolio.manage',
                'mark attendance', 'create teaching session', 'edit teaching session',
                'start teaching session', 'end teaching session', 'view leaderboard',
            ],
            'Vice President' => [
                'view_users', 'approve_members',
                'view_meetings', 'create_meetings', 'edit_meetings',
                'generate_qr_codes', 'open_attendance',
                'view_attendance', 'export_attendance',
                'view_events', 'create_events', 'edit_events', 'manage_registrations',
                'view_projects',
                'send_announcements',
                'view_reports', 'view_analytics',
                'access_admin_panel',
                'view_assignments',
                'vote_in_elections',
                'mark attendance', 'create teaching session', 'edit teaching session',
                'start teaching session', 'end teaching session', 'view leaderboard',
            ],
            'General Secretary' => [
                'view_users',
                'view_meetings', 'create_meetings', 'edit_meetings',
                'view_attendance', 'record_attendance_manual', 'export_attendance',
                'view_events',
                'send_announcements', 'manage_notifications',
                'view_reports',
                'access_admin_panel',
                'vote_in_elections',
                'mark attendance', 'view leaderboard',
            ],
            'Treasurer' => [
                'view_users',
                'view_meetings',
                'view_attendance',
                'view_events',
                'view_budget', 'manage_budget', 'approve_expenditures',
                'create_financial_reports',
                'view_reports', 'export_reports',
                'access_admin_panel',
                'vote_in_elections',
            ],
            'Head of Projects' => [
                'view_users',
                'view_meetings', 'view_attendance',
                'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
                'assign_project_members',
                'view_reports',
                'access_admin_panel',
                'vote_in_elections',
            ],
            'CTF Lead' => [
                'view_users',
                'view_meetings', 'view_attendance',
                'view_trainings', 'create_trainings', 'edit_trainings', 'delete_trainings',
                'manage_enrollments', 'create_ctf_challenges',
                'register_competitions', 'manage_competition_teams',
                'view_reports',
                'access_admin_panel',
                'vote_in_elections',
            ],
            'Public Relations' => [
                'view_meetings', 'view_attendance',
                'view_events',
                'manage_social_media', 'upload_media',
                'edit_website_content', 'create_blog_posts',
                'view_analytics',
                'access_admin_panel',
                'vote_in_elections',
            ],
            'Lead Developer' => [
                'view_users',
                'view_meetings', 'view_attendance',
                'view_projects', 'create_projects',
                'propose_research', 'manage_innovation_lab', 'create_technical_content',
                'create_blog_posts',
                'access_admin_panel',
                'vote_in_elections',
            ],
            'Technical Lead' => [
                'view_users',
                'view_meetings', 'view_attendance', 'record_attendance_manual',
                'view_conduct_violations', 'issue_warnings',
                'recommend_termination', 'manage_security_incidents',
                'view_audit_logs',
                'access_admin_panel',
                'vote_in_elections',
                'mark attendance', 'view leaderboard',
            ],
            'Club Mentor' => $allPermissionNames,
            'Workshop Coordinator' => [
                'view_meetings', 'create_meetings', 'edit_meetings',
                'view_events', 'create_events', 'edit_events', 'manage_registrations',
                'view_trainings', 'create_trainings', 'edit_trainings', 'manage_enrollments',
                'send_announcements',
                'access_admin_panel',
                'vote_in_elections',
            ],
            'Events Lead' => [
                'view_events', 'create_events', 'edit_events', 'delete_events', 'publish_events',
                'cancel_events', 'manage_registrations', 'manage_attendance', 'view_event_feedback',
                'view_event_analytics', 'send_announcements', 'access_admin_panel', 'vote_in_elections',
            ],
            'member' => [
                'view_meetings',
                'view_own_attendance',
                'view_events',
                'view_projects',
                'view_trainings',
                'vote_in_elections',
                'content.view',
                'teacher.events.view', 'teacher.events.rsvp',
                'portfolio.view',
                'checkin via qr', 'view leaderboard',
            ],
            'associate' => [
                'view_meetings',
                'view_events',
                'view_projects',
            ],
            'alumni' => [
                'view_meetings',
                'view_events',
                'view_projects',
                'view_trainings',
                'portfolio.view',
                'view leaderboard',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $this->syncRole($roleName, $permissions);
        }

        $this->command->info('Roles and permissions created successfully!');

        // Optionally create a default admin user
        $this->createDefaultAdmin();
    }

    /**
     * @param  array<string, string>  $roleRenames
     */
    private function renameRoles(array $roleRenames): void
    {
        foreach ($roleRenames as $from => $to) {
            $role = Role::where('name', $from)->first();

            if ($role) {
                $role->update(['name' => $to]);
            }
        }
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function syncRole(string $name, array $permissions): Role
    {
        $role = Role::firstOrCreate(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role;
    }

    private function createDefaultAdmin()
    {
        // $admin = User::firstOrCreate(
        //     ['email' => 'kevinssali23@gmail.com'],
        //     [
        //         'name' => 'System Administrator',
        //         'password' => bcrypt('password'), // Change this!
        //         'student_id' => 'ADMIN001',
        //         'membership_status' => 'active',
        //         'joined_at' => now(),
        //     ]
        // );

        // $admin->assignRole('admin');

        // $this->command->info('Default admin created: kevinssali23@gmail.com / password');
        // $this->command->warn('IMPORTANT: Change the admin password immediately!');
    }
}
