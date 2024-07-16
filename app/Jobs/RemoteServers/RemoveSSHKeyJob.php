<?php

declare(strict_types=1);

namespace App\Jobs\RemoteServers;

use App\Actions\RemoteServer\RemoveSSHKey;
use App\Models\RemoteServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RemoveSSHKeyJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public RemoteServer $remoteServer)
    {
        //
    }

    public function handle(): void
    {
        Log::info('Removing SSH key from server.', ['server_id' => $this->remoteServer->getAttribute('id')]);

        $removeSSHKey = new RemoveSSHKey;
        $removeSSHKey->handle($this->remoteServer);
    }
}
