<?php

namespace App\View\Components\ui;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Drawer extends Component
{
    public string $show;

    public string $onClose;

    public ?string $title;

    public string $width;

    public bool $showCloseButton;

    public bool $closeOnOverlayClick;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $show,
        string $onClose,
        ?string $title = null,
        string $width = 'md',
        bool $showCloseButton = true,
        bool $closeOnOverlayClick = true,
    ) {
        $this->show = $show;
        $this->onClose = $onClose;
        $this->title = $title;
        $this->width = $width;
        $this->showCloseButton = $showCloseButton;
        $this->closeOnOverlayClick = $closeOnOverlayClick;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.drawer');
    }
}
