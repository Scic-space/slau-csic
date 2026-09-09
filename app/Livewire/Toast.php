<?php

namespace App\Livewire;

use Livewire\Component;

class Toast extends Component
{
    public string $message = '';

    public string $type = 'success';

    public bool $show = false;

    public function hide(): void
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.toast');
    }
}
