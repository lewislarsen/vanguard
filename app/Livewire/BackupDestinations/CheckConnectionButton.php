<?php

declare(strict_types=1);

namespace App\Livewire\BackupDestinations;

use App\Models\BackupDestination;
use Illuminate\View\View;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class CheckConnectionButton extends Component
{
    public BackupDestination $backupDestination;

    public function refreshSelf(): void
    {
        $this->dispatch('$refresh');
    }

    public function checkConnection(): void
    {
        $this->backupDestination->refresh();

        $this->backupDestination->markAsChecking();

        $this->backupDestination->run();

        Toaster::info(__('Performing a connectivity check.'));

        $this->dispatch('backup-destination-connection-check-initiated-' . $this->backupDestination->getAttribute('id'));
    }

    public function render(): View
    {
        return view('livewire.backup-destinations.check-connection-button');
    }

    /**
     * Get the listeners array.
     *
     * @return array<string, string>
     */
    protected function getListeners(): array
    {
        return [
            sprintf('echo-private:backup-destinations.%s,BackupDestinationConnectionCheck', $this->backupDestination->getAttribute('id')) => 'refreshSelf',
            'update-backup-destination-check-button-' . $this->backupDestination->getAttribute('id') => '$refresh',
        ];
    }
}
