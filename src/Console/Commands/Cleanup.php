<?php

namespace Lukaswhite\TemporaryFiles\Console\Commands;

use Illuminate\Console\Command;
use Lukaswhite\TemporaryFiles\Helpers\Reader;
use Lukaswhite\TemporaryFiles\TemporaryFile;

/**
 * Class Cleanup
 *
 * This console command cleans up temporary files that are no longer in use.
 *
 * @package Lukaswhite\Console\Commands
 */
class Cleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temporary-files:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up any temporary files that are no longer required.';

    /**
     * The temporary files reader
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct( Reader $reader )
    {
        parent::__construct( );
        $this->reader = $reader;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->reader->expired( )->map( function( TemporaryFile $file ) {
            $this->reader->getDisk( )->deleteDirectory( $file->getDirectory( ) );
        } );
    }
}
