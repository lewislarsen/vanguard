<?php

declare(strict_types=1);

namespace App\Livewire\BackupTasks;

use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view('livewire.backup-tasks.index');
    }
}
