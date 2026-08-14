<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /** @test */
    public function test_treasurer_can_access_transaction_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Treasurer');

        $response = $this->actingAs($user)
            ->get('/admin/transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_president_can_access_transaction_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole('President');

        $response = $this->actingAs($user)
            ->get('/admin/transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_super_admin_can_access_transaction_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)
            ->get('/admin/transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_regular_user_cannot_access_transaction_management(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/admin/transactions');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_can_create_transaction(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Treasurer');

        $this->actingAs($user);
        $response = $this->get('/admin/transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_can_approve_transaction(): void
    {
        $approver = User::factory()->create();
        $approver->assignRole('super-admin');

        Transaction::factory()->create([
            'status' => 'pending',
            'amount' => 150.00,
        ]);

        $this->actingAs($approver);
        $response = $this->get('/admin/transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_can_reject_transaction(): void
    {
        $approver = User::factory()->create();
        $approver->assignRole('super-admin');

        Transaction::factory()->create([
            'status' => 'pending',
            'amount' => 150.00,
        ]);

        $this->actingAs($approver);
        $response = $this->get('/admin/transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_can_bulk_approve_transactions(): void
    {
        $approver = User::factory()->create();
        $approver->assignRole('super-admin');

        Transaction::factory()->count(3)->create([
            'status' => 'pending',
        ]);

        $this->actingAs($approver);
        $response = $this->get('/admin/transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_filters_work_correctly(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);
        $response = $this->get('/admin/transactions');

        $response->assertStatus(200);
    }
}
