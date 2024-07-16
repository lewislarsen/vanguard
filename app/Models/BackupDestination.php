<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\CheckBackupDestinationsS3ConnectionJob;
use Aws\S3\S3Client;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BackupDestination extends Model
{
    use HasFactory;

    public const string TYPE_CUSTOM_S3 = 'custom_s3';

    public const string TYPE_S3 = 's3';

    public const string TYPE_LOCAL = 'local';

    public const string STATUS_REACHABLE = 'reachable';

    public const string STATUS_UNREACHABLE = 'unreachable';

    public const string STATUS_UNKNOWN = 'unknown';

    public const string STATUS_CHECKING = 'checking';

    protected $guarded = [];

    /**
     * Get the user that owns the backup destination.
     *
     * @return BelongsTo<User, BackupDestination>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the backup tasks for the backup destination.
     *
     * @return HasMany<BackupTask>
     */
    public function backupTasks(): HasMany
    {
        return $this->hasMany(BackupTask::class);
    }

    public function isS3Connection(): bool
    {
        return $this->type === self::TYPE_S3 || $this->type === self::TYPE_CUSTOM_S3;
    }

    public function isLocalConnection(): bool
    {
        return $this->type === self::TYPE_LOCAL;
    }

    public function determineS3Region(): string
    {
        if ($this->type === BackupDestination::TYPE_CUSTOM_S3 && $this->custom_s3_region === null) {
            return 'us-east-1'; // Dummy region to satisfy the S3Client constructor
        }

        /** @var string $customS3Region */
        $customS3Region = $this->custom_s3_region;

        return $customS3Region;
    }

    public function getS3Client(): S3Client
    {
        if (! $this->isS3Connection()) {
            throw new RuntimeException('Backup destination is not an S3 connection.');
        }

        $config = [
            'version' => 'latest',
            'use_path_style_endpoint' => $this->getAttributeValue('use_path_style_endpoint'),
            'region' => $this->determineS3Region(),
            'credentials' => [
                'key' => $this->s3_access_key,
                'secret' => $this->s3_secret_key,
            ],
        ];

        if ($this->type === self::TYPE_CUSTOM_S3) {
            $config['endpoint'] = $this->custom_s3_endpoint;
        }

        try {
            return new S3Client($config);
        } catch (Exception $exception) {
            Log::error('Failed to create S3 client: ' . $exception->getMessage());
            throw new RuntimeException('Failed to create S3 client: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Get the type of the backup destination.
     */
    public function type(): ?string
    {
        if ($this->type === self::TYPE_S3) {
            return 'S3';
        }

        if ($this->type === self::TYPE_CUSTOM_S3) {
            return __('Custom S3');
        }

        if ($this->type === self::TYPE_LOCAL) {
            return __('Local');
        }

        return null;
    }

    public function run(): void
    {
        if ($this->isS3Connection()) {
            CheckBackupDestinationsS3ConnectionJob::dispatch($this)
                ->onQueue('connectivity-checks');
        }
    }

    public function isReachable(): bool
    {
        return $this->status === self::STATUS_REACHABLE;
    }

    public function isUnreachable(): bool
    {
        return $this->status === self::STATUS_UNREACHABLE;
    }

    public function isUnknown(): bool
    {
        return $this->status === self::STATUS_UNKNOWN;
    }

    public function isChecking(): bool
    {
        return $this->status === self::STATUS_CHECKING;
    }

    public function markAsChecking(): void
    {
        $this->update(['status' => self::STATUS_CHECKING]);
    }

    public function markAsReachable(): void
    {
        $this->update(['status' => self::STATUS_REACHABLE]);
    }

    public function markAsUnreachable(): void
    {
        $this->update(['status' => self::STATUS_UNREACHABLE]);
    }

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            's3_access_key' => 'encrypted',
            's3_secret_key' => 'encrypted',
            's3_bucket_name' => 'encrypted',
            'custom_s3_endpoint' => 'encrypted',
        ];
    }
}
