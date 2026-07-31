<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_settings_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/system/settings')
            ->assertStatus(200);
    }

    public function test_settings_create_page_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/system/settings/create')
            ->assertStatus(200);
    }

    public function test_settings_model_works(): void
    {
        $setting = Setting::create([
            'key' => 'test_uniq_key_'.uniqid(),
            'value' => 'My Club',
            'type' => 'string',
            'group' => 'general',
        ]);

        $this->assertEquals('My Club', $setting->getTypedValue());
        $this->assertEquals('My Club', Setting::getValue($setting->key));
        $this->assertNull(Setting::getValue('nonexistent'));

        $setting->update(['value' => '1', 'type' => 'boolean']);
        $this->assertTrue($setting->fresh()->getTypedValue());
    }

    public function test_content_pages_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/system/content-pages')
            ->assertStatus(200);
    }

    public function test_content_page_create_page_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/system/content-pages/create')
            ->assertStatus(200);
    }

    public function test_content_page_model_works(): void
    {
        $page = ContentPage::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => '<p>Test content</p>',
            'is_published' => true,
        ]);

        $this->assertTrue($page->is_published);
        $this->assertEquals('test-page', $page->slug);
        $this->assertGreaterThanOrEqual(1, ContentPage::published()->count());
    }

    public function test_audit_log_resource_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/system/audit-logs')
            ->assertStatus(200);
    }

    public function test_system_overview_page_loads(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/admin/system-overview')
            ->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_access_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/system/settings')
            ->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_access_system_overview(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/system-overview')
            ->assertStatus(403);
    }
}
