<?php

namespace Lukaswhite\TemporaryFiles\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Lukaswhite\TemporaryFiles\Events\TemporaryFileDeleted;
use Lukaswhite\TemporaryFiles\Exceptions\MissingManifestException;
use Lukaswhite\TemporaryFiles\Helpers\Reader;
use Lukaswhite\TemporaryFiles\TemporaryFile;

/**
 * Class DeleteTemporaryFile
 *
 * This job deletes a temporary file that's no longer required.
 *
 * A couple of notes:
 *
 *  - it's designed to be fired with a delay; that delay represents the "window"
 *    within which the file must be used, before it gets deleted
 *  - the file may well not exist, if it has been used. In which case, the job
 *    does nothing, but this is the expected behaviour.
 *
 * @package Lukaswhite\TemporaryFiles\Jobs
 */
class DeleteTemporaryFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The file that's been scheduled for deletion
     *
     * @var TemporaryFile
     */
    protected $file;

    /**
     * The temporary files reader
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( TemporaryFile $file )
    {
        $this->file = $file;
        $this->reader = app( )->make( Reader::class );
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $file = $this->reader->get($this->file->getId());

            if ( $file ) {
                $this->reader->getDisk( )->deleteDirectory( $file->getDirectory( ) );
                event( new TemporaryFileDeleted( $file ) );
            }

        } catch ( MissingManifestException $e ) {
            // silently fail
        }
    }
}
