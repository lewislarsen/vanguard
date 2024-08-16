<?php

declare(strict_types=1);

namespace App\Mail\User\TwoFactor;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupCodeConsumedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly User $user)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You have consumed a backup code')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.user.two-factor.backup-code-consumed-mail',
            with: [
                'user' => $this->user,
                'backupCodesConsumedCount' => $this->user->backupCodesUsedCount(),
                'backupCodesRemainingCount' => $this->user->backupCodesRemainingCount(),
            ],
        );
    }
}
