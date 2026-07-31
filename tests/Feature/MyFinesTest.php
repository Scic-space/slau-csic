<?php

use App\Models\Fine;
use App\Models\FineType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyFinesTest extends TestCase
{
    use RefreshDatabase;

    private ?FineType $fineType = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->fineType = FineType::factory()->create();
    }

    public function test_member_can_view_their_fines(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $fine = Fine::factory()->pending()->create([
            'fine_type_id' => $this->fineType->id,
            'user_id' => $user->id,
            'amount' => 25.00,
            'amount_paid' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyFines::class)
            ->assertSee('UGX '.number_format(25.00, 0))
            ->assertSee('Pending');
    }

    public function test_member_sees_correct_outstanding_total(): void
    {
        $user = User::factory()->create()->assignRole('member');
        Fine::factory()->pending()->create([
            'fine_type_id' => $this->fineType->id,
            'user_id' => $user->id,
            'amount' => 50.00,
        ]);
        Fine::factory()->paid()->create([
            'fine_type_id' => $this->fineType->id,
            'user_id' => $user->id,
            'amount' => 30.00,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyFines::class)
            ->assertSee('UGX '.number_format(50.00, 0));
    }

    public function test_member_can_open_appeal_form(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $fine = Fine::factory()->pending()->create([
            'fine_type_id' => $this->fineType->id,
            'user_id' => $user->id,
            'issue_date' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyFines::class)
            ->call('openAppeal', $fine->id)
            ->assertSet('selectedFineId', $fine->id)
            ->assertSet('showAppealForm', true);
    }

    public function test_member_can_submit_appeal(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $fine = Fine::factory()->pending()->create([
            'fine_type_id' => $this->fineType->id,
            'user_id' => $user->id,
            'issue_date' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyFines::class)
            ->call('openAppeal', $fine->id)
            ->set('appealReason', 'first_offense')
            ->set('appealExplanation', 'This is my first offense, please waive the fine.')
            ->call('submitAppeal')
            ->assertSet('showAppealForm', false);

        $this->assertDatabaseHas('fine_appeals', [
            'fine_id' => $fine->id,
            'appeal_reason' => 'first_offense',
            'status' => 'pending',
        ]);
    }

    public function test_appeal_requires_reason(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $fine = Fine::factory()->pending()->create([
            'fine_type_id' => $this->fineType->id,
            'user_id' => $user->id,
            'issue_date' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyFines::class)
            ->call('openAppeal', $fine->id)
            ->set('appealExplanation', 'Some explanation')
            ->call('submitAppeal')
            ->assertHasErrors('appealReason');
    }

    public function test_appeal_requires_explanation(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $fine = Fine::factory()->pending()->create([
            'fine_type_id' => $this->fineType->id,
            'user_id' => $user->id,
            'issue_date' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyFines::class)
            ->call('openAppeal', $fine->id)
            ->set('appealReason', 'first_offense')
            ->call('submitAppeal')
            ->assertHasErrors('appealExplanation');
    }

    public function test_cannot_appeal_others_fine(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $other = User::factory()->create();
        $fine = Fine::factory()->pending()->create([
            'fine_type_id' => $this->fineType->id,
            'user_id' => $other->id,
            'issue_date' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyFines::class)
            ->call('openAppeal', $fine->id)
            ->set('appealReason', 'first_offense')
            ->set('appealExplanation', 'Should not work.')
            ->call('submitAppeal')
            ->assertStatus(403);
    }

    public function test_shows_empty_state_when_no_fines(): void
    {
        $user = User::factory()->create()->assignRole('member');

        Livewire::actingAs($user)
            ->test(\App\Livewire\MyFines::class)
            ->assertSee('You have no fines');
    }
}
