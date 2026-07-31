<?php

use App\Filament\Resources\Trainings\Pages\ListTrainings;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrainingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_can_access_training_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)
            ->get('/admin/trainings');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_training_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)
            ->get('/admin/trainings');

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_training_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole('member');

        $response = $this->actingAs($user)
            ->get('/admin/trainings');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/admin/trainings');

        $response->assertRedirect('/admin/login');
    }

    public function test_list_displays_trainings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Training::create([
            'title' => 'Test Course',
            'description' => 'Test description',
            'category' => 'web_security',
            'difficulty' => 'beginner',
            'duration_hours' => 10,
            'instructor_id' => $user->id,
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/trainings');

        $response->assertStatus(200);
        $response->assertSee('Test Course');
    }

    public function test_search_filters_trainings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Training::create([
            'title' => 'Unique Course Alpha',
            'description' => 'Description',
            'category' => 'ethical_hacking',
            'difficulty' => 'beginner',
            'instructor_id' => $user->id,
        ]);

        Training::create([
            'title' => 'Different Course Beta',
            'description' => 'Description',
            'category' => 'programming',
            'difficulty' => 'intermediate',
            'instructor_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/trainings?search=Alpha');

        $response->assertStatus(200);
        $response->assertSee('Unique Course Alpha');
        $response->assertDontSee('Different Course Beta');
    }

    public function test_category_filter_works(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $course1 = Training::create([
            'title' => 'Ethical Hacking 101',
            'description' => 'Description',
            'category' => 'ethical_hacking',
            'difficulty' => 'beginner',
            'instructor_id' => $user->id,
        ]);

        Training::create([
            'title' => 'Programming Basics',
            'description' => 'Description',
            'category' => 'programming',
            'difficulty' => 'beginner',
            'instructor_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(ListTrainings::class)
            ->assertSee('Ethical Hacking 101')
            ->assertSee('Programming Basics')
            ->filterTable('category', 'ethical_hacking')
            ->assertSee('Ethical Hacking 101')
            ->assertDontSee('Programming Basics');
    }

    public function test_published_status_displayed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Training::create([
            'title' => 'Published Course',
            'description' => 'Description',
            'category' => 'web_security',
            'difficulty' => 'beginner',
            'instructor_id' => $user->id,
            'is_published' => true,
        ]);

        Training::create([
            'title' => 'Draft Course',
            'description' => 'Description',
            'category' => 'web_security',
            'difficulty' => 'beginner',
            'instructor_id' => $user->id,
            'is_published' => false,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/trainings');

        $response->assertStatus(200);
    }

    public function test_filter_by_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Training::create([
            'title' => 'Published Course',
            'description' => 'Description',
            'category' => 'web_security',
            'difficulty' => 'beginner',
            'instructor_id' => $user->id,
            'is_published' => true,
        ]);

        Training::create([
            'title' => 'Hidden Draft',
            'description' => 'Description',
            'category' => 'programming',
            'difficulty' => 'advanced',
            'instructor_id' => $user->id,
            'is_published' => false,
        ]);

        Livewire::actingAs($user)
            ->test(ListTrainings::class)
            ->assertSee('Published Course')
            ->assertSee('Hidden Draft')
            ->filterTable('is_published', 1)
            ->assertSee('Published Course')
            ->assertDontSee('Hidden Draft');
    }
}
