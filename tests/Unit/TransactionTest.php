<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_transaction(): void
    {
        $user = User::factory()->create();
        $transactionData = [
            'type' => 'income',
            'category' => 'Membership Dues',
            'amount' => 100.00,
            'date' => now()->toDateString(),
            'description' => 'Test transaction',
            'paid_to_from' => 'Test Member',
            'payment_method' => 'cash',
            'status' => 'approved',
            'created_by' => $user->id,
        ];

        $transaction = Transaction::create($transactionData);

        $this->assertDatabaseHas('transactions', $transactionData);
        $this->assertEquals('income', $transaction->type);
        $this->assertEquals(100.00, $transaction->amount);
    }

    /** @test */
    public function it_can_create_expense_transaction(): void
    {
        $user = User::factory()->create();
        $transactionData = [
            'type' => 'expense',
            'category' => 'Events',
            'amount' => 50.00,
            'date' => now()->toDateString(),
            'description' => 'Test expense',
            'paid_to_from' => 'Test Vendor',
            'payment_method' => 'card',
            'status' => 'pending',
            'created_by' => $user->id,
        ];

        $transaction = Transaction::create($transactionData);

        $this->assertDatabaseHas('transactions', $transactionData);
        $this->assertEquals('expense', $transaction->type);
        $this->assertEquals(50.00, $transaction->amount);
    }

    /** @test */
    public function it_requires_approval_for_amounts_over_100(): void
    {
        $transaction = Transaction::factory()->create([
            'amount' => 150.00,
        ]);

        $this->assertTrue($transaction->requires_approval);
    }

    /** @test */
    public function it_does_not_require_approval_for_amounts_under_100(): void
    {
        $transaction = Transaction::factory()->create([
            'amount' => 50.00,
        ]);

        $this->assertFalse($transaction->requires_approval);
    }

    /** @test */
    public function it_can_approve_transaction(): void
    {
        $approver = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'status' => 'pending',
        ]);

        $transaction->approve($approver, 'Approved for testing');

        $this->assertEquals('approved', $transaction->status);
        $this->assertEquals($approver->id, $transaction->approved_by);
        $this->assertNotNull($transaction->approved_at);
    }

    /** @test */
    public function it_can_reject_transaction(): void
    {
        $approver = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'status' => 'pending',
        ]);

        $transaction->reject($approver, 'Rejected for testing');

        $this->assertEquals('rejected', $transaction->status);
        $this->assertEquals($approver->id, $transaction->approved_by);
        $this->assertNotNull($transaction->approved_at);
    }

    /** @test */
    public function it_formats_amount_correctly(): void
    {
        $transaction = Transaction::factory()->create([
            'amount' => 123.45,
        ]);

        $this->assertEquals('$123.45', $transaction->formatted_amount);
    }

    /** @test */
    public function it_scopes_to_income_transactions(): void
    {
        Transaction::factory()->income()->count(3);
        Transaction::factory()->expense()->count(2);

        $incomeTransactions = Transaction::income()->get();

        $this->assertCount(3, $incomeTransactions);
        $incomeTransactions->each(function ($transaction) {
            $this->assertEquals('income', $transaction->type);
        });
    }

    /** @test */
    public function it_scopes_to_expense_transactions(): void
    {
        Transaction::factory()->income()->count(2);
        Transaction::factory()->expense()->count(3);

        $expenseTransactions = Transaction::expense()->get();

        $this->assertCount(3, $expenseTransactions);
        $expenseTransactions->each(function ($transaction) {
            $this->assertEquals('expense', $transaction->type);
        });
    }

    /** @test */
    public function it_scopes_to_pending_transactions(): void
    {
        Transaction::factory()->approved()->count(3);
        Transaction::factory()->pending()->count(2);

        $pendingTransactions = Transaction::pending()->get();

        $this->assertCount(2, $pendingTransactions);
        $pendingTransactions->each(function ($transaction) {
            $this->assertEquals('pending', $transaction->status);
        });
    }

    /** @test */
    public function it_belongs_to_creator(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $transaction->creator);
        $this->assertEquals($user->id, $transaction->creator->id);
    }

    /** @test */
    public function it_belongs_to_approver(): void
    {
        $approver = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'approved_by' => $approver->id,
        ]);

        $this->assertInstanceOf(User::class, $transaction->approver);
        $this->assertEquals($approver->id, $transaction->approver->id);
    }
}
