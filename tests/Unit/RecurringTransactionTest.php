<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_can_create_recurring_transaction(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::create([
            'type' => 'expense',
            'category' => 'Events',
            'amount' => 100.00,
            'date' => now()->toDateString(),
            'description' => 'Monthly venue rental',
            'payment_method' => 'transfer',
            'status' => 'approved',
            'created_by' => $user->id,
            'is_recurring' => true,
            'recurring_frequency' => 'monthly',
            'recurring_interval' => 1,
            'recurring_end_date' => now()->addYear(),
        ]);

        $this->assertTrue($transaction->is_recurring);
        $this->assertEquals('monthly', $transaction->recurring_frequency);
        $this->assertEquals(1, $transaction->recurring_interval);
        $this->assertNotNull($transaction->recurring_end_date);
    }

    public function test_can_scope_recurring_transactions(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->count(3)->create(['is_recurring' => true, 'created_by' => $user->id]);
        Transaction::factory()->count(2)->create(['is_recurring' => false, 'created_by' => $user->id]);

        $recurring = Transaction::recurring()->get();

        $this->assertCount(3, $recurring);
    }

    public function test_has_recurring_parent_relationship(): void
    {
        $user = User::factory()->create();
        $parent = Transaction::factory()->create(['is_recurring' => true, 'created_by' => $user->id]);
        $child = Transaction::factory()->create([
            'is_recurring' => true,
            'recurring_parent_id' => $parent->id,
            'created_by' => $user->id,
        ]);

        $this->assertEquals($parent->id, $child->recurringParent->id);
        $this->assertTrue($parent->recurringChildren->contains($child));
    }

    public function test_auto_sets_requires_approval_for_large_amounts(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'amount' => 150.00,
            'created_by' => $user->id,
        ]);

        $this->assertTrue($transaction->requires_approval);
    }

    public function test_does_not_require_approval_for_small_amounts(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'amount' => 50.00,
            'created_by' => $user->id,
        ]);

        $this->assertFalse($transaction->requires_approval);
    }

    public function test_recurring_transaction_has_default_interval(): void
    {
        $user = User::factory()->create()->assignRole('treasurer');
        $transaction = Transaction::factory()->create([
            'created_by' => $user->id,
            'is_recurring' => true,
            'recurring_frequency' => 'monthly',
            'recurring_next_date' => now()->addMonth(),
        ]);

        $this->assertTrue($transaction->is_recurring);
        $this->assertEquals('monthly', $transaction->recurring_frequency);
        $this->assertNotNull($transaction->recurring_next_date);
    }

    public function test_non_recurring_transaction_has_null_fields(): void
    {
        $user = User::factory()->create()->assignRole('treasurer');
        $transaction = Transaction::factory()->create([
            'created_by' => $user->id,
            'is_recurring' => false,
        ]);

        $this->assertFalse($transaction->is_recurring);
        $this->assertNull($transaction->recurring_frequency);
        $this->assertNull($transaction->recurring_next_date);
        $this->assertNull($transaction->recurring_interval);
    }

    public function test_due_recurring_scope_returns_only_due_transactions(): void
    {
        $user = User::factory()->create()->assignRole('treasurer');
        $due = Transaction::factory()->create([
            'created_by' => $user->id,
            'is_recurring' => true,
            'recurring_frequency' => 'monthly',
            'recurring_next_date' => now()->subDay(),
        ]);
        $notDue = Transaction::factory()->create([
            'created_by' => $user->id,
            'is_recurring' => true,
            'recurring_frequency' => 'yearly',
            'recurring_next_date' => now()->addMonth(),
        ]);
        $nonRecurring = Transaction::factory()->create([
            'created_by' => $user->id,
            'is_recurring' => false,
        ]);

        $dueTransactions = Transaction::dueRecurring()->get();

        $this->assertTrue($dueTransactions->contains($due));
        $this->assertFalse($dueTransactions->contains($notDue));
        $this->assertFalse($dueTransactions->contains($nonRecurring));
    }

    public function test_calculate_next_date_for_monthly(): void
    {
        $user = User::factory()->create()->assignRole('treasurer');
        $transaction = Transaction::factory()->create([
            'created_by' => $user->id,
            'is_recurring' => true,
            'recurring_frequency' => 'monthly',
            'date' => now(),
        ]);

        $nextDate = $transaction->calculateNextDate();

        $this->assertEquals(now()->addMonth()->format('Y-m-d'), $nextDate->format('Y-m-d'));
    }

    public function test_calculate_next_date_for_weekly(): void
    {
        $user = User::factory()->create()->assignRole('treasurer');
        $transaction = Transaction::factory()->create([
            'created_by' => $user->id,
            'is_recurring' => true,
            'recurring_frequency' => 'weekly',
            'date' => now(),
        ]);

        $nextDate = $transaction->calculateNextDate();

        $this->assertEquals(now()->addWeek()->format('Y-m-d'), $nextDate->format('Y-m-d'));
    }
}
