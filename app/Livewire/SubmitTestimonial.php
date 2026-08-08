<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Testimonial;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Share Your Experience')]
class SubmitTestimonial extends Component
{
    use GuardsPendingMembers;

    public string $quote = '';

    public function rules(): array
    {
        return [
            'quote' => ['required', 'string', 'min:20', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'quote.required' => 'Please share your experience.',
            'quote.min' => 'Your testimonial should be at least 20 characters.',
            'quote.max' => 'Your testimonial cannot exceed 500 characters.',
        ];
    }

    public function submit(): void
    {
        if (! auth()->check()) {
            $this->dispatch('show-login');

            return;
        }

        $this->validate();

        $existingPending = Testimonial::where('user_id', auth()->id())
            ->where('is_approved', false)
            ->exists();

        if ($existingPending) {
            session()->flash('error', 'You already have a pending testimonial under review.');

            return;
        }

        Testimonial::create([
            'user_id' => auth()->id(),
            'quote' => $this->quote,
        ]);

        $this->quote = '';
        session()->flash('success', 'Thank you! Your testimonial has been submitted for review.');
    }

    public function render()
    {
        return view('livewire.submit-testimonial');
    }
}
