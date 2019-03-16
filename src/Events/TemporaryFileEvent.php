<?php

namespace Lukaswhite\TemporaryFiles\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Lukaswhite\TemporaryFiles\TemporaryFile;

/**
 * Class TemporaryFileEvent
 *
 * This is simply the base for temporary file events, which all share a common
 * characteristic; that they refer to a temporary file.
 * 
 * @package Lukaswhite\Events
 */
abstract class TemporaryFileEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The file in question
     *
     * @var TemporaryFile
     */
    protected $file;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct( TemporaryFile $file )
    {
        $this->file = $file;
    }

    /**
     * Get the temporary file that this event relates to.
     *
     * @return TemporaryFile
     */
    public function getFile( ) : TemporaryFile
    {
        return $this->file;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
