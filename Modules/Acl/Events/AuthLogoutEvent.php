<?php

namespace Modules\Acl\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuthLogoutEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * ID de l'utilisateur.
     *
     * @var string
     */
    public $userId;
    public $ipInfo;


    /**
     * Create a new event instance.
     *
     * @param  string  $userId
     * @param  string  $ipInfo
     * @return void
     */
    public function __construct($userId, $ipInfo)
    {
        $this->userId = $userId;
        $this->ipInfo = $ipInfo;
    }
}
