<?php

use App\Livewire\EventDetails;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\Process\Process;

uses(RefreshDatabase::class);

it('updates the open member page at the server end time and handles rescheduling', function () {
    $this->freezeSecond();
    $event = Event::factory()->create([
        'status' => 'ongoing',
        'start_date' => now()->subHour(),
        'end_date' => now()->addMinute(),
    ]);
    $this->actingAs(User::factory()->create());
    $html = Livewire::test(EventDetails::class, ['event' => $event])->html();
    $document = new DOMDocument;
    $previousErrorHandling = libxml_use_internal_errors(true);

    try {
        $document->loadHTML($html, LIBXML_NONET);
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorHandling);
    }

    $element = (new DOMXPath($document))->query('//*[@data-event-server-time]')->item(0);
    expect($element)->not->toBeNull();

    $script = <<<'JS'
const assert = require('node:assert/strict');
const vm = require('node:vm');
const input = JSON.parse(require('node:fs').readFileSync(0, 'utf8'));
let elapsed = 0;
let tick;
let refreshes = 0;
let stopped = false;
const state = vm.runInNewContext(`(${input.expression})`, {
    performance: { now: () => elapsed },
    Date: { now: () => Number.MAX_SAFE_INTEGER },
    setInterval: (callback) => { tick = callback; return 42; },
    clearInterval: (id) => { assert.equal(id, 42); stopped = true; },
});
state.$el = { dataset: input.dataset };
state.$wire = { $refresh: () => { refreshes++; } };
state.init();
assert.equal(state.eventEnded, false);
assert.equal(refreshes, 0);
elapsed = 59999;
tick();
assert.equal(state.eventEnded, false);
elapsed = 60000;
tick();
assert.equal(state.eventEnded, true);
assert.equal(refreshes, 1);
tick();
assert.equal(refreshes, 1);
input.dataset.eventEndTime = String(Number(input.dataset.eventServerTime) + 120000);
tick();
assert.equal(state.eventEnded, false);
elapsed = 120000;
tick();
assert.equal(state.eventEnded, true);
assert.equal(refreshes, 2);
input.dataset.eventEndTime = '';
tick();
assert.equal(state.eventEnded, false);
input.dataset.eventEnded = 'true';
tick();
assert.equal(state.eventEnded, true);
assert.equal(refreshes, 2);
input.dataset.eventEnded = 'false';
input.dataset.eventCancelled = 'true';
input.dataset.eventEndTime = input.dataset.eventServerTime;
tick();
assert.equal(state.eventEnded, false);
state.destroy();
assert.equal(stopped, true);
JS;

    $process = new Process(['node', '-e', $script], base_path());
    $process->setInput(json_encode([
        'expression' => $element->getAttribute('x-data'),
        'dataset' => [
            'eventServerTime' => $element->getAttribute('data-event-server-time'),
            'eventEndTime' => $element->getAttribute('data-event-end-time'),
            'eventEnded' => $element->getAttribute('data-event-ended'),
            'eventCancelled' => $element->getAttribute('data-event-cancelled'),
        ],
    ], JSON_THROW_ON_ERROR));
    $process->setTimeout(10)->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());
});
