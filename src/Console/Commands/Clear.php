<?php

namespace Lukaswhite\TemporaryFiles\Console\Commands;

use Illuminate\Console\Command;
use Lukaswhite\TemporaryFiles\Helpers\Reader;
use Lukaswhite\TemporaryFiles\TemporaryFile;
use Lukaswhite\TemporaryFiles\Events\TemporaryFileDeleted;

/**
 * Class Clear
 *
 * This console command clears all temporary files.
 *
 * @package Lukaswhite\Console\Commands
 */
class Clear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temporary-files:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all temporary files.';

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
        $this->reader->all( )->map( function( TemporaryFile $file ) {
            $this->reader->getDisk( )->deleteDirectory( $file->getDirectory( ) );
            event( new TemporaryFileDeleted( $file ) );
        } );
    }
}
