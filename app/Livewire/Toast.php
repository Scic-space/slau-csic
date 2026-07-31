<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Toast extends Component
{
    public string $message = '';

    public string $type = 'success';

    public bool $show = false;

    #[On('toast')]
    public function showToast(string $message, string $type = 'success'): void
    {
        $this->message = $message;
        $this->type = $type;
        $this->show = true;

        $this->dispatch('toast-auto-hide', timeout: 4000);
    }

    public function hide(): void
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.toast');
    }
}
