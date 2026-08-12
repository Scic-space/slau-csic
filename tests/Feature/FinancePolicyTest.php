<?php

use App\Models\BudgetAllocation;
use App\Models\BudgetCategory;
use App\Models\Fine;
use App\Models\FineAppeal;
use App\Models\FinePayment;
use App\Models\FineType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    // ============================================
    // TransactionPolicy
    // ============================================

    public function test_super_admin_can_view_any_transactions(): void
    {
        $user = User::factory()->create()->assignRole('super-admin');

        $this->assertTrue($user->can('viewAny', Transaction::class));
    }

    public function test_treasurer_can_view_any_transactions(): void
    {
        $user = User::factory()->create()->assignRole('Treasurer');

        $this->assertTrue($user->can('viewAny', Transaction::class));
    }

    public function test_member_cannot_view_any_transactions(): void
    {
        $user = User::factory()->create()->assignRole('member');

        $this->assertFalse($user->can('viewAny', Transaction::class));
    }

    public function test_member_can_view_own_transaction(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $transaction = Transaction::factory()->create(['created_by' => $user->id]);

        $this->assertTrue($user->can('view', $transaction));
    }

    public function test_member_cannot_view_others_transaction(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $other = User::factory()->create();
        $transaction = Transaction::factory()->create(['created_by' => $other->id]);

        $this->assertFalse($user->can('view', $transaction));
    }

    // ============================================
    // FinePolicy
    // ============================================

    public function test_admin_can_create_fines(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $this->assertTrue($user->can('create', Fine::class));
    }

    public function test_member_cannot_create_fines(): void
    {
        $user = User::factory()->create()->assignRole('member');

        $this->assertFalse($user->can('create', Fine::class));
    }

    public function test_member_can_view_own_fine(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $fine = Fine::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('view', $fine));
    }

    public function test_member_cannot_view_others_fine(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $other = User::factory()->create();
        $fine = Fine::factory()->create(['user_id' => $other->id]);

        $this->assertFalse($user->can('view', $fine));
    }

    public function test_head_discipline_can_waive_fines(): void
    {
        $user = User::factory()->create()->assignRole('Technical Lead');
        $fine = Fine::factory()->create();

        $this->assertTrue($user->can('waive', $fine));
    }

    public function test_member_cannot_waive_fines(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $fine = Fine::factory()->create();

        $this->assertFalse($user->can('waive', $fine));
    }

    // ============================================
    // FineTypePolicy
    // ============================================

    public function test_admin_can_manage_fine_types(): void
    {
        $user = User::factory()->create()->assignRole('admin');
        $fineType = FineType::factory()->create();

        $this->assertTrue($user->can('viewAny', FineType::class));
        $this->assertTrue($user->can('create', FineType::class));
        $this->assertTrue($user->can('update', $fineType));
    }

    public function test_member_cannot_manage_fine_types(): void
    {
        $user = User::factory()->create()->assignRole('member');

        $this->assertFalse($user->can('viewAny', FineType::class));
    }

    // ============================================
    // FineAppealPolicy
    // ============================================

    public function test_any_member_can_create_appeal(): void
    {
        $user = User::factory()->create()->assignRole('member');

        $this->assertTrue($user->can('create', FineAppeal::class));
    }

    public function test_admin_can_review_appeals(): void
    {
        $user = User::factory()->create()->assignRole('admin');
        $appeal = FineAppeal::factory()->pending()->create();

        $this->assertTrue($user->can('review', $appeal));
    }

    public function test_reviewed_appeal_cannot_be_reviewed_again(): void
    {
        $user = User::factory()->create()->assignRole('admin');
        $appeal = FineAppeal::factory()->approved()->create();

        $this->assertFalse($user->can('review', $appeal));
    }

    // ============================================
    // BudgetCategoryPolicy
    // ============================================

    public function test_treasurer_can_manage_budget_categories(): void
    {
        $user = User::factory()->create()->assignRole('Treasurer');
        $category = BudgetCategory::factory()->create();

        $this->assertTrue($user->can('viewAny', BudgetCategory::class));
        $this->assertTrue($user->can('create', BudgetCategory::class));
        $this->assertTrue($user->can('update', $category));
    }

    public function test_member_cannot_manage_budget_categories(): void
    {
        $user = User::factory()->create()->assignRole('member');

        $this->assertFalse($user->can('viewAny', BudgetCategory::class));
    }

    // ============================================
    // BudgetAllocationPolicy
    // ============================================

    public function test_treasurer_can_manage_allocations(): void
    {
        $user = User::factory()->create()->assignRole('Treasurer');
        $allocation = BudgetAllocation::factory()->create();

        $this->assertTrue($user->can('viewAny', BudgetAllocation::class));
        $this->assertTrue($user->can('create', BudgetAllocation::class));
        $this->assertTrue($user->can('update', $allocation));
    }

    public function test_member_cannot_manage_allocations(): void
    {
        $user = User::factory()->create()->assignRole('member');

        $this->assertFalse($user->can('viewAny', BudgetAllocation::class));
    }

    // ============================================
    // FinePaymentPolicy
    // ============================================

    public function test_treasurer_can_record_payments(): void
    {
        $user = User::factory()->create()->assignRole('Treasurer');

        $this->assertTrue($user->can('create', FinePayment::class));
    }

    public function test_member_cannot_record_payments(): void
    {
        $user = User::factory()->create()->assignRole('member');

        $this->assertFalse($user->can('create', FinePayment::class));
    }
}
