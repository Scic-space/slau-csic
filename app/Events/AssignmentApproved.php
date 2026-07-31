<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AssignmentApproved
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Assignment $assignment,
        public ?int $approvedBy,
    ) {}
}
