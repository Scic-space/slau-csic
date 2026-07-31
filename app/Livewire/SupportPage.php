<?php

namespace App\Livewire;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Help & Support')]
class SupportPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public bool $submitted = false;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'name.required' => 'Please provide your name.',
            'email.required' => 'An email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'subject.required' => 'Please select a topic.',
            'message.required' => 'Please write your message.',
        ]);

        ContactMessage::create($validated);

        $this->submitted = true;

        $this->dispatch('toast-show', message: 'Your message has been sent. We will get back to you soon.', type: 'success');
    }

    public function render()
    {
        return view('livewire.support-page');
    }
}
