<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_member_can_view_their_transactions(): void
    {
        $user = User::factory()->create()->assignRole('member');
        Transaction::factory()->create([
            'created_by' => $user->id,
            'type' => 'income',
            'amount' => 100.00,
            'status' => 'approved',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyTransactions::class)
            ->assertSee('UGX '.number_format(100.00, 0))
            ->assertSee('Income');
    }

    public function test_member_only_sees_own_transactions(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $other = User::factory()->create();
        Transaction::factory()->create([
            'created_by' => $other->id,
            'amount' => 999.00,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyTransactions::class)
            ->assertDontSee('UGX '.number_format(999.00, 0));
    }

    public function test_member_can_filter_transactions_by_type(): void
    {
        $user = User::factory()->create()->assignRole('member');
        Transaction::factory()->create([
            'created_by' => $user->id,
            'type' => 'income',
            'amount' => 100.00,
            'status' => 'approved',
        ]);
        Transaction::factory()->create([
            'created_by' => $user->id,
            'type' => 'expense',
            'amount' => 50.00,
            'status' => 'approved',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyTransactions::class)
            ->set('typeFilter', 'income')
            ->assertSee('UGX '.number_format(100.00, 0))
            ->assertDontSee('UGX '.number_format(50.00, 0));
    }

    public function test_member_can_filter_transactions_by_status(): void
    {
        $user = User::factory()->create()->assignRole('member');
        Transaction::factory()->create([
            'created_by' => $user->id,
            'status' => 'pending',
            'amount' => 75.00,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyTransactions::class)
            ->set('statusFilter', 'pending')
            ->assertSee('75');
    }

    public function test_member_can_reset_filters(): void
    {
        $user = User::factory()->create()->assignRole('member');
        Transaction::factory()->create([
            'created_by' => $user->id,
            'type' => 'income',
            'amount' => 100.00,
            'status' => 'approved',
        ]);
        Transaction::factory()->create([
            'created_by' => $user->id,
            'type' => 'expense',
            'amount' => 50.00,
            'status' => 'approved',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyTransactions::class)
            ->set('typeFilter', 'expense')
            ->call('resetFilters')
            ->assertSet('typeFilter', '')
            ->assertSet('statusFilter', '');
    }

    public function test_shows_empty_state_when_no_transactions(): void
    {
        $user = User::factory()->create()->assignRole('member');

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyTransactions::class)
            ->assertSee('No transactions found');
    }
}
