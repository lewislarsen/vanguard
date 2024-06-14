<div>
    <x-modal name="backup-task-remove-historic-log-{{ $backupTaskLog->id }}">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Clear Backup Task Log - :label', ['label' => $backupTaskLog->backupTask->label]) }}
            </h2>
            <p class="text-gray-800 dark:text-gray-200 my-3">
                {{ __('Are you sure you want to clear this log?') }}
            </p>
            <p class="text-gray-800 dark:text-gray-200 my-3">
                {{ __('The backup data will still exist at your backup destination, however there will be no record of this within :app.', ['app' => config('app.name')]) }}
            </p>
            <div class="flex space-x-5">
                <div class="w-4/6">
                    <x-danger-button type="button" wire:click="delete" class="mt-4" centered
                                     wire:loading.attr="disabled"
                                     wire:loading.class="opacity-50 cursor-not-allowed">

                        <div wire:loading wire:target="delete">
                            <x-spinner class="mr-2 text-white h-4 w-4 inline"/>
                            {{ __('Clearing...') }}
                        </div>

                        <div wire:loading.remove wire:target="delete">
                            {{ __('Confirm') }}
                        </div>
                    </x-danger-button>
                </div>
                <div class="w-2/6 ml-2">
                    <x-secondary-button type="button" class="mt-4" centered x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                </div>
            </div>
        </div>
    </x-modal>
</div>