<?php

use App\Models\BudgetCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasurerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /** @test */
    public function test_dashboard_displays_correctly_for_treasurer(): void
    {
        $user = User::factory()->create();
        $user->assignRole('treasurer');

        $response = $this->actingAs($user)
            ->get('/admin/treasurer-dashboard');

        $response->assertStatus(200);
        $response->assertSee('TreasurerDashboard');
    }

    /** @test */
    public function test_regular_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/admin/treasurer-dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_displays_financial_overview(): void
    {
        $user = User::factory()->create();
        $user->assignRole('treasurer');

        // Create test transactions
        Transaction::factory()->income()->create(['amount' => 1000.00]);
        Transaction::factory()->expense()->create(['amount' => 500.00]);

        $response = $this->actingAs($user)
            ->get('/admin/treasurer-dashboard');

        $response->assertSee('totalIncome');
        $response->assertSee('totalExpenses');
    }

    /** @test */
    public function test_displays_budget_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('treasurer');

        // Create test budget category
        $category = BudgetCategory::factory()->expense()->create([
            'allocated_amount' => 1000.00,
            'is_active' => true,
        ]);

        // Create transaction that uses 80% of budget
        Transaction::factory()->create([
            'category' => $category->name,
            'amount' => 800.00,
            'type' => 'expense',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/treasurer-dashboard');

        $response->assertSee('budgetData');
    }

    /** @test */
    public function test_displays_recent_transactions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('treasurer');

        // Create test transactions
        Transaction::factory()->count(5)->create();

        $response = $this->actingAs($user)
            ->get('/admin/treasurer-dashboard');

        $response->assertSee('recentTransactions');
    }

    /** @test */
    public function test_displays_pending_approvals_count(): void
    {
        $user = User::factory()->create();
        $user->assignRole('treasurer');

        // Create pending transactions
        Transaction::factory()->count(3)->create(['status' => 'pending']);

        $response = $this->actingAs($user)
            ->get('/admin/treasurer-dashboard');

        $response->assertSee('pendingApprovals');
    }

    /** @test */
    public function test_displays_spending_trend_chart(): void
    {
        $user = User::factory()->create();
        $user->assignRole('treasurer');

        $response = $this->actingAs($user)
            ->get('/admin/treasurer-dashboard');

        $response->assertSee('spendingTrend');
    }
}
