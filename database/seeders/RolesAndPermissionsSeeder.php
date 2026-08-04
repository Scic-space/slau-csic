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

        // Create roles and assign permissions

        // 1. ADMIN/ADVISOR - All permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        User::where('email', '=', 'kevinssali23@gmail.com')?->first()?->assignRole($admin, $superAdmin);

        // 2. PRESIDENT - High-level management
        $president = Role::firstOrCreate(['name' => 'president']);
        $president->givePermissionTo([
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
            'vote_in_elections', // President can vote as an active member
            // Content & Course Materials
            'content.view', 'content.create', 'content.edit', 'content.delete',
            // Teacher & Education
            'teacher.events.view', 'teacher.events.rsvp', 'teacher.reports.view', 'teacher.dashboard.view',
            // Student Portfolios
            'portfolio.view', 'portfolio.manage',
            // Teaching Sessions
            'mark attendance', 'create teaching session', 'edit teaching session',
            'start teaching session', 'end teaching session', 'view leaderboard',
        ]);

        // 3. VICE PRESIDENT - Assists president
        $vicePresident = Role::firstOrCreate(['name' => 'vice_president']);
        $vicePresident->givePermissionTo([
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
            // Teaching Sessions
            'mark attendance', 'create teaching session', 'edit teaching session',
            'start teaching session', 'end teaching session', 'view leaderboard',
        ]);

        // 4. SECRETARY - Records & communication
        $secretary = Role::firstOrCreate(['name' => 'secretary']);
        $secretary->givePermissionTo([
            'view_users',
            'view_meetings', 'create_meetings', 'edit_meetings',
            'view_attendance', 'record_attendance_manual', 'export_attendance',
            'view_events',
            'send_announcements', 'manage_notifications',
            'view_reports',
            'access_admin_panel',
            'vote_in_elections',
            // Teaching Sessions
            'mark attendance', 'view leaderboard',
        ]);

        // 5. TREASURER - Financial management
        $treasurer = Role::firstOrCreate(['name' => 'treasurer']);
        $treasurer->givePermissionTo([
            'view_users',
            'view_meetings',
            'view_attendance',
            'view_events',
            'view_budget', 'manage_budget', 'approve_expenditures',
            'create_financial_reports',
            'view_reports', 'export_reports',
            'access_admin_panel',
            'vote_in_elections',
        ]);

        // 6. HEAD OF PROJECTS
        $headProjects = Role::firstOrCreate(['name' => 'head_projects']);
        $headProjects->givePermissionTo([
            'view_users',
            'view_meetings', 'view_attendance',
            'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
            'assign_project_members',
            'view_reports',
            'access_admin_panel',
            'vote_in_elections',
        ]);

        // 7. HEAD OF CTF & TRAINING
        $headCtf = Role::firstOrCreate(['name' => 'head_ctf']);
        $headCtf->givePermissionTo([
            'view_users',
            'view_meetings', 'view_attendance',
            'view_trainings', 'create_trainings', 'edit_trainings', 'delete_trainings',
            'manage_enrollments', 'create_ctf_challenges',
            'register_competitions', 'manage_competition_teams',
            'view_reports',
            'access_admin_panel',
            'vote_in_elections',
        ]);

        // 8. HEAD OF MEDIA & DESIGN
        $headMedia = Role::firstOrCreate(['name' => 'head_media']);
        $headMedia->givePermissionTo([
            'view_meetings', 'view_attendance',
            'view_events',
            'manage_social_media', 'upload_media',
            'edit_website_content', 'create_blog_posts',
            'view_analytics',
            'access_admin_panel',
            'vote_in_elections',
        ]);

        // 9. HEAD OF INNOVATIONS & RESEARCH
        $headInnovations = Role::firstOrCreate(['name' => 'head_innovations']);
        $headInnovations->givePermissionTo([
            'view_users',
            'view_meetings', 'view_attendance',
            'view_projects', 'create_projects',
            'propose_research', 'manage_innovation_lab', 'create_technical_content',
            'create_blog_posts',
            'access_admin_panel',
            'vote_in_elections',
        ]);

        // 10. HEAD OF DISCIPLINE & SECURITY
        $headDiscipline = Role::firstOrCreate(['name' => 'head_discipline']);
        $headDiscipline->givePermissionTo([
            'view_users',
            'view_meetings', 'view_attendance', 'record_attendance_manual',
            'view_conduct_violations', 'issue_warnings',
            'recommend_termination', 'manage_security_incidents',
            'view_audit_logs',
            'access_admin_panel',
            'vote_in_elections',
            // Teaching Sessions
            'mark attendance', 'view leaderboard',
        ]);

        // 11. ACTIVE MEMBER - Regular members
        $member = Role::firstOrCreate(['name' => 'member']);
        $member->givePermissionTo([
            'view_meetings',
            'view_own_attendance',
            'view_events',
            'view_projects',
            'view_trainings',
            'vote_in_elections',
            // Content & Course Materials
            'content.view',
            // Teacher & Education
            'teacher.events.view', 'teacher.events.rsvp',
            // Student Portfolios
            'portfolio.view',
            // Teaching Sessions
            'checkin via qr', 'view leaderboard',
        ]);

        // 12. ASSOCIATE MEMBER - Limited access
        $associate = Role::firstOrCreate(['name' => 'associate']);
        $associate->givePermissionTo([
            'view_meetings',
            'view_events',
            'view_projects',
        ]);

        $this->command->info('Roles and permissions created successfully!');

        // Optionally create a default admin user
        $this->createDefaultAdmin();
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
