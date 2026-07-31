<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardLoadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_dashboard_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(200);
    }

    public function test_users_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertStatus(200);
    }

    public function test_transactions_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/transactions')
            ->assertStatus(200);
    }

    public function test_exams_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/exams')
            ->assertStatus(200);
    }

    public function test_budget_categories_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/budget-categories')
            ->assertStatus(200);
    }

    public function test_meetings_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/meetings')
            ->assertStatus(200);
    }

    public function test_roles_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/roles')
            ->assertStatus(200);
    }

    public function test_trainings_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/trainings')
            ->assertStatus(200);
    }

    public function test_financial_report_page_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/financial-report')
            ->assertStatus(200);
    }
}
