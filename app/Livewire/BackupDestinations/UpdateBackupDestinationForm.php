<?php

declare(strict_types=1);

namespace App\Livewire\BackupDestinations;

use App\Models\BackupDestination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Toaster;

class UpdateBackupDestinationForm extends Component
{
    public string $label;

    public string $type = 'custom_s3';

    public ?string $s3AccessKey = null;

    public ?string $s3SecretKey = null;

    public ?string $s3BucketName = null;

    public bool $usePathStyleEndpoint = false;

    public ?string $customS3Region = null;

    public ?string $customS3Endpoint = null;

    public BackupDestination $backupDestination;

    public function submit(): RedirectResponse|Redirector
    {
        $this->authorize('update', $this->backupDestination);

        $this->validate([
            'label' => ['required', 'string'],
            'type' => ['required', 'string', 'in:custom_s3,s3,local'],
            's3AccessKey' => ['nullable', 'required_if:type,custom_s3,s3'],
            's3SecretKey' => ['nullable', 'required_if:type,custom_s3,s3'],
            's3BucketName' => ['nullable', 'required_if:type,custom_s3,s3'],
            'customS3Region' => ['nullable', 'required_if:type,s3'], // Not required for custom S3 connections
            'customS3Endpoint' => ['nullable', 'required_if:type,custom_s3'],
            'usePathStyleEndpoint' => ['boolean', 'required_if:type,s3,custom_s3'],
        ], [
            'label.required' => __('Please enter a label.'),
            'type.required' => __('Please select a type.'),
            'type.in' => __('Please select a valid type.'),
            's3AccessKey.required_if' => __('Please enter a valid S3 access key.'),
            's3SecretKey.required_if' => __('Please enter a valid S3 secret key.'),
            's3BucketName.required_if' => __('Please enter a valid S3 bucket name.'),
            'customS3Region.required_if' => __('Please enter a valid S3 region.'),
            'customS3Endpoint.required_if' => __('Please enter a valid S3 endpoint URL.'),
        ]);

        $this->backupDestination->update([
            'label' => $this->label,
            'type' => $this->type,
            's3_access_key' => $this->s3AccessKey ?? null,
            's3_secret_key' => $this->s3SecretKey ?? null,
            's3_bucket_name' => $this->s3BucketName ?? null,
            'custom_s3_region' => $this->customS3Region ?? null,
            'custom_s3_endpoint' => $this->customS3Endpoint ?? null,
            'path_style_endpoint' => $this->usePathStyleEndpoint ?? false,
        ]);

        $this->backupDestination->save();

        Toaster::success(__('Backup destination has been updated.'));

        return Redirect::route('backup-destinations.index');
    }

    public function mount(BackupDestination $backupDestination): void
    {
        $this->backupDestination = $backupDestination;
        $this->label = $backupDestination->getAttribute('label');
        $this->type = $backupDestination->getAttribute('type');
        $this->s3AccessKey = $backupDestination->getAttribute('s3_access_key') ?? null;
        $this->s3SecretKey = $backupDestination->getAttribute('s3_secret_key') ?? null;
        $this->s3BucketName = $backupDestination->getAttribute('s3_bucket_name') ?? null;
        $this->customS3Region = $backupDestination->getAttribute('custom_s3_region') ?? null;
        $this->customS3Endpoint = $backupDestination->getAttribute('custom_s3_endpoint') ?? null;
        $this->usePathStyleEndpoint = $backupDestination->getAttribute('path_style_endpoint') ?? false;
    }

    public function render(): View
    {
        return view('livewire.backup-destinations.update-backup-destination-form');
    }
}
