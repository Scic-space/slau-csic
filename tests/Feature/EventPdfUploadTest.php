<?php

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Livewire\EventCreate;
use App\Livewire\EventEdit;
use App\Models\Event;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
    $this->admin->assignRole('super-admin');

    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $this->pdfContent = Pdf::loadHTML('<h1>Admin lesson notes</h1><p>Network security practice.</p>')->output();
    $this->eventFormData = [
        'title' => 'Network Security Lesson',
        'type' => 'workshop',
        'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'end_date' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
        'location' => 'Computer lab',
        'organizer_id' => $this->admin->id,
    ];
});

it('creates an event through the admin form using a PDF instead of description text', function () {
    Livewire::test(CreateEvent::class)
        ->fillForm($this->eventFormData)
        ->set('data.description_file_path', UploadedFile::fake()->createWithContent('lesson-notes.pdf', $this->pdfContent))
        ->call('create')
        ->assertHasNoFormErrors();

    $event = Event::query()->where('title', 'Network Security Lesson')->sole();

    expect($event->description)->toBe('');
    expect($event->description_file_path)->toStartWith('event-documents/')->toEndWith('.pdf');
    Storage::disk('local')->assertExists($event->description_file_path);
    expect(Storage::disk('local')->get($event->description_file_path))->toBe($this->pdfContent);

    $event->update(['status' => 'published', 'is_public' => true]);
    auth()->logout();

    $view = $this->get(route('events.description.show', $event))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($view->headers->get('Content-Disposition'))->toStartWith('inline;');
    expect($view->streamedContent())->toBe($this->pdfContent);
});

it('requires a PDF in the admin event creation form', function () {
    Livewire::test(CreateEvent::class)
        ->fillForm($this->eventFormData)
        ->call('create')
        ->assertHasFormErrors(['description_file_path' => 'required']);

    expect(Event::query()->count())->toBe(0);
});

it('rejects non-PDF and oversized uploads in the admin form', function (string $filename, bool $isOversized) {
    $content = $isOversized
        ? $this->pdfContent.str_repeat(' ', 10240 * 1024)
        : '<!DOCTYPE html><html><body>This is not a PDF.</body></html>';
    $upload = UploadedFile::fake()->createWithContent($filename, $content);
    $upload->mimeType((new UploadedFile($upload->getPathname(), $filename, null, null, true))->getMimeType());

    Livewire::test(CreateEvent::class)
        ->fillForm($this->eventFormData)
        ->set('data.description_file_path', $upload)
        ->call('create')
        ->assertHasFormErrors(['description_file_path']);

    expect(Event::query()->count())->toBe(0);
})->with([
    'HTML document' => ['notes.html', false],
    'HTML disguised as PDF' => ['notes.pdf', false],
    'PDF larger than 10 MB' => ['notes.pdf', true],
]);

it('replaces the event PDF through the admin edit form', function () {
    $event = Event::factory()->create([
        'description' => 'Existing legacy notes',
        'description_file_path' => 'event-documents/original.pdf',
    ]);
    Storage::disk('local')->put($event->description_file_path, $this->pdfContent);
    $replacementPdf = Pdf::loadHTML('<h1>Updated lesson notes</h1>')->output();
    $component = Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()]);
    $oldFileKey = array_key_first($component->get('data.description_file_path'));

    $component
        ->call('callSchemaComponentMethod', 'form.description_file_path', 'removeUploadedFile', ['fileKey' => $oldFileKey])
        ->set('data.description_file_path', UploadedFile::fake()->createWithContent('revised-notes.pdf', $replacementPdf))
        ->call('save')
        ->assertHasNoFormErrors();

    $event->refresh();

    expect($event->description_file_path)->not->toBe('event-documents/original.pdf');
    expect(Storage::disk('local')->get($event->description_file_path))->toBe($replacementPdf);
    expect($event->description)->toBe('Existing legacy notes');
});

it('preserves the existing PDF during unrelated admin event edits', function () {
    $event = Event::factory()->create([
        'description' => '',
        'description_file_path' => 'event-documents/original.pdf',
    ]);
    Storage::disk('local')->put($event->description_file_path, $this->pdfContent);

    Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
        ->fillForm(['title' => 'Revised event title'])
        ->call('save')
        ->assertHasNoFormErrors();

    $event->refresh();

    expect($event->title)->toBe('Revised event title');
    expect($event->description_file_path)->toBe('event-documents/original.pdf');
    expect(Storage::disk('local')->get($event->description_file_path))->toBe($this->pdfContent);
});

it('preserves legacy notes when an admin edits an event without uploading a PDF', function () {
    $event = Event::factory()->create(['description' => '<p>Existing lesson notes</p>']);

    Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
        ->fillForm(['title' => 'Revised legacy event'])
        ->call('save')
        ->assertHasNoFormErrors();

    $event->refresh();

    expect($event->description)->toBe('<p>Existing lesson notes</p>');
    expect($event->description_file_path)->toBeNull();
});

it('creates an event with a PDF through the member event form and previews its draft', function () {
    Livewire::test(EventCreate::class)
        ->set('title', 'Member lesson')
        ->set('startDate', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('descriptionFile', UploadedFile::fake()->createWithContent('notes.pdf', $this->pdfContent))
        ->call('create')
        ->assertHasNoErrors();

    $event = Event::query()->where('title', 'Member lesson')->sole();

    expect($event->description)->toBe('');
    expect($event->description_file_path)->toStartWith('event-documents/');
    expect(Storage::disk('local')->get($event->description_file_path))->toBe($this->pdfContent);

    $view = $this->get(route('events.description.show', $event))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($view->streamedContent())->toBe($this->pdfContent);
    $this->get(route('events.description.download', $event))->assertNotFound();

    Livewire::test(EventEdit::class, ['event' => $event])
        ->set('title', 'Revised draft lesson')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('events.edit', $event->slug);
});

it('requires a PDF through the member event creation form', function () {
    Livewire::test(EventCreate::class)
        ->set('title', 'Member lesson')
        ->set('startDate', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('create')
        ->assertHasErrors(['descriptionFile' => 'required']);

    expect(Event::query()->count())->toBe(0);
});

it('rejects non-PDF and oversized uploads through the member event form', function (string $filename, bool $isOversized) {
    $content = $isOversized
        ? $this->pdfContent.str_repeat(' ', 10240 * 1024)
        : '<!DOCTYPE html><html><body>This is not a PDF.</body></html>';
    $upload = UploadedFile::fake()->createWithContent($filename, $content);
    $upload->mimeType((new UploadedFile($upload->getPathname(), $filename, null, null, true))->getMimeType());

    Livewire::test(EventCreate::class)
        ->set('title', 'Member lesson')
        ->set('startDate', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('descriptionFile', $upload)
        ->call('create')
        ->assertHasErrors(['descriptionFile']);

    expect(Event::query()->count())->toBe(0);
})->with([
    'HTML document' => ['notes.html', false],
    'HTML disguised as PDF' => ['notes.pdf', false],
    'PDF larger than 10 MB' => ['notes.pdf', true],
]);

it('replaces the event PDF through the member edit form and keeps it on later edits', function () {
    $event = Event::factory()->create([
        'description' => 'Existing legacy notes',
        'description_file_path' => 'event-documents/original.pdf',
    ]);
    Storage::disk('local')->put($event->description_file_path, $this->pdfContent);
    $replacementPdf = Pdf::loadHTML('<h1>Revised member lesson notes</h1>')->output();

    Livewire::test(EventEdit::class, ['event' => $event])
        ->set('descriptionFile', UploadedFile::fake()->createWithContent('revised.pdf', $replacementPdf))
        ->call('save')
        ->assertHasNoErrors();

    $event->refresh();
    $replacementPath = $event->description_file_path;

    expect($replacementPath)->not->toBe('event-documents/original.pdf');
    expect(Storage::disk('local')->get($replacementPath))->toBe($replacementPdf);
    expect($event->description)->toBe('Existing legacy notes');

    Livewire::test(EventEdit::class, ['event' => $event])
        ->set('title', 'A revised lesson title')
        ->call('save')
        ->assertHasNoErrors();

    expect($event->fresh()->description_file_path)->toBe($replacementPath);
    expect(Storage::disk('local')->get($replacementPath))->toBe($replacementPdf);
});

it('views and downloads the uploaded PDF before during and after lessons', function (string $status, string $startDate, string $endDate, bool $isMember) {
    auth()->logout();

    $event = Event::factory()->create([
        'title' => 'Network Security Lesson',
        'description' => '<p>Older text that must not replace the uploaded PDF.</p>',
        'description_file_path' => 'event-documents/lesson.pdf',
        'status' => $status,
        'is_public' => ! $isMember,
        'start_date' => now()->modify($startDate),
        'end_date' => now()->modify($endDate),
    ]);
    Storage::disk('local')->put($event->description_file_path, $this->pdfContent);
    $downloadUrl = route('events.description.download', $event);
    $viewUrl = route('events.description.show', $event);

    if ($isMember) {
        $this->actingAs(User::factory()->create())
            ->get(route('events.member-show', $event))
            ->assertOk()
            ->assertSee($downloadUrl, false)
            ->assertSee($viewUrl, false)
            ->assertDontSee($event->description_file_path, false);
    } else {
        $this->get(route('events.show', $event))
            ->assertInertia(fn (Assert $page) => $page
                ->where('event.description_download_url', $downloadUrl)
                ->where('event.description_view_url', $viewUrl)
                ->missing('event.description_file_path')
                ->missing('event.description'));
    }

    $download = $this->get($downloadUrl)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('network-security-lesson-description.pdf');

    expect($download->streamedContent())->toBe($this->pdfContent);

    $view = $this->get($viewUrl)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($view->headers->get('Content-Disposition'))->toStartWith('inline;');
    expect($view->streamedContent())->toBe($this->pdfContent);
    expect($event->registrations()->exists())->toBeFalse();
})->with([
    'before published lesson' => ['published', '+1 day', '+1 day +2 hours'],
    'before scheduled lesson' => ['scheduled', '+1 day', '+1 day +2 hours'],
    'during lesson' => ['ongoing', '-1 hour', '+1 hour'],
    'after lesson' => ['completed', '-2 days', '-1 day'],
])->with([
    'public guest' => false,
    'member' => true,
]);

it('keeps uploaded PDFs private for guests and members awaiting approval', function (bool $isPendingMember) {
    auth()->logout();

    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => false,
        'description_file_path' => 'event-documents/private.pdf',
    ]);
    Storage::disk('local')->put($event->description_file_path, $this->pdfContent);

    if ($isPendingMember) {
        $this->actingAs(User::factory()->pending()->create());
    }

    $this->get(route('events.description.show', $event))->assertNotFound();
    $this->get(route('events.description.download', $event))->assertNotFound();
})->with([
    'guest' => false,
    'pending member' => true,
]);

it('does not expose uploaded PDFs for hidden or deleted events', function (string $status, bool $isDeleted) {
    auth()->logout();

    $event = Event::factory()->create([
        'status' => $status,
        'is_public' => true,
        'description_file_path' => 'event-documents/hidden.pdf',
    ]);
    Storage::disk('local')->put($event->description_file_path, $this->pdfContent);
    $viewUrl = route('events.description.show', $event);
    $downloadUrl = route('events.description.download', $event);

    if ($isDeleted) {
        $event->delete();
    }

    $this->get($viewUrl)->assertNotFound();
    $this->get($downloadUrl)->assertNotFound();
})->with([
    'draft' => ['draft', false],
    'cancelled' => ['cancelled', false],
    'deleted' => ['published', true],
]);

it('returns not found when the uploaded event PDF is missing', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'description_file_path' => 'event-documents/missing.pdf',
    ]);

    $this->get(route('events.description.show', $event))->assertNotFound();
    $this->get(route('events.description.download', $event))->assertNotFound();
});

it('previews legacy administrator notes as an inline PDF', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'description' => '<h2>Existing administrator work</h2><p>Complete the network exercise.</p>',
    ]);

    $response = $this->get(route('events.description.show', $event))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->headers->get('Content-Disposition'))->toStartWith('inline;');
    expect($response->getContent())->toStartWith('%PDF-');
});

it('keeps draft PDF previews unavailable to unrelated approved members', function () {
    $event = Event::factory()->create([
        'status' => 'draft',
        'is_public' => true,
        'description_file_path' => 'event-documents/draft.pdf',
    ]);
    Storage::disk('local')->put($event->description_file_path, $this->pdfContent);

    $this->actingAs(User::factory()->create())
        ->get(route('events.description.show', $event))
        ->assertNotFound();
});

it('never serves HTML stored under an event PDF path', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'description_file_path' => 'event-documents/disguised.pdf',
    ]);
    Storage::disk('local')->put($event->description_file_path, '<html><body>raw-html-marker</body></html>');

    $this->get(route('events.description.show', $event))
        ->assertUnsupportedMediaType()
        ->assertDontSee('raw-html-marker');

    $this->get(route('events.description.download', $event))
        ->assertUnsupportedMediaType()
        ->assertDontSee('raw-html-marker');
});
