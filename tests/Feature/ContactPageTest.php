<?php

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the public contact page', function () {
    $this->get(route('contact'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('public/Contact'));
});

it('publishes the current email and WhatsApp contact destinations', function () {
    $source = file_get_contents(resource_path('js/pages/public/Contact.tsx'));

    expect($source)
        ->toContain('mailto:sciccyber8@gmail.com')
        ->toContain('sciccyber8@gmail.com')
        ->toContain('https://wa.me/254105883177')
        ->toContain('aria-label="Chat with SCIC Cyber on WhatsApp"')
        ->toContain('WhatsApp Us')
        ->not->toContain('+254 105 883 177')
        ->not->toContain('+254105883177');
});

it('continues to submit contact messages through the existing endpoint', function () {
    $this->post(route('contact'), [
        'name' => 'Public Visitor',
        'email' => 'visitor@example.com',
        'topic' => 'Collaboration or partnership',
        'message' => 'We would like to discuss a security workshop.',
    ])->assertRedirect(route('contact'));

    expect(ContactMessage::query()->where('email', 'visitor@example.com')->exists())->toBeTrue();
});

it('continues to validate contact submissions', function () {
    $this->post(route('contact'), [
        'name' => '',
        'email' => 'invalid-email',
        'topic' => '',
        'message' => '',
    ])->assertSessionHasErrors(['name', 'email', 'topic', 'message']);

    expect(ContactMessage::query()->count())->toBe(0);
});
