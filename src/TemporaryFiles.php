<?php


namespace Lukaswhite\TemporaryFiles;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Lukaswhite\TemporaryFiles\Contracts\GeneratesUniqueIds;
use Lukaswhite\TemporaryFiles\Events\TemporaryFileDeleted;
use Lukaswhite\TemporaryFiles\Exceptions\MissingManifestException;
use Lukaswhite\TemporaryFiles\Helpers\IdGenerator;
use Lukaswhite\TemporaryFiles\Helpers\Reader;
use Lukaswhite\TemporaryFiles\Helpers\Manifest;
use Lukaswhite\TemporaryFiles\Events\TemporaryFileCreated;
use Lukaswhite\TemporaryFiles\Jobs\DeleteTemporaryFile;
use Session;

/**
 * Class TemporaryFiles
 *
 * The main temporary files service
 *
 * @package Lukaswhite\TemporaryFiles
 */
class TemporaryFiles
{
    /**
     * Class constants.
     *
     * The following constants represent the available modes.
     */
    const MODE_CRON     =   'cron';
    const MODE_QUEUE    =   'queue';
    const MODE_MANUAL   =   'manual';

    /**
     * The disk
     *
     * @var Filesystem
     */
    protected $disk;

    /**
     * The ID generator
     *
     * @var GeneratesUniqueIds
     */
    protected $idGenerator;

    /**
     * The reader
     *
     * @var Reader
     */
    protected $reader;

    /**
     * TemporaryFiles constructor.
     *
     * @param Filesystem $disk
     */
    public function __construct( Filesystem $disk, Reader $reader, IdGenerator $idGenerator )
    {
        $this->disk = $disk;
        $this->idGenerator = $idGenerator;
        $this->reader = $reader;
    }

    /**
     * Create a new temporary file
     *
     * @param string $filepath
     * @param string $filename
     * @param array $data
     * @param bool $deleteOriginal
     * @return TemporaryFile
     */
    public function create( string $filepath, $filename = null, $data = [ ], $deleteOriginal = false ) : TemporaryFile
    {
        // Calculate the expiry date, by adding the configured lifetime, which is in minutes,
        // to the current time.
        $expiresAt = Carbon::now( )->addMinutes( config( 'temporary-files.lifetime' ) );

        $filename = $filename ? $filename : basename( $filepath );

        // Create a new instance, which represents the temporary file being created.
        $file = new TemporaryFile(
            $this->idGenerator->generate( $filename ),
            $filename,
            $expiresAt,
            $data
        );

        // Create the directory
        $this->disk->makeDirectory( $file->getDirectory( ) );

        // Write the file
        $this->disk->put( $file->getPath( ), file_get_contents( $filepath ) );

        // Create and write the manifest
        $manifest = new Manifest( $file );

        // If there's an authenticated user and the package is configured to store it,
        // set it now
        if ( config( 'temporary-files.include_user', false ) && auth( )->user( ) ) {
            $manifest->setUser( auth( )->user( ) );
        }

        // Write the manifest
        $manifest->write( $this->disk );

        // Optionally delete the original file
        if ( $deleteOriginal ) {
            unlink( $filepath );
        }

        // Fire an event to indicate that a temporary file has been created
        event( new TemporaryFileCreated( $file ) );

        // The package provides the option to store a record of a newly-created temporary file
        // in the session, for easy access. If that's enabled, do so now.
        if ( config( 'temporary-files.store_in_session' ) ) {
            Session::put( config('temporary-files.session_key' ), $file->getId( ) );
        }

        // If we're using delayed jobs for the cleanup process, dispatch it with the appropriate
        // delay
        if (config('temporary-files.mode') === self::MODE_QUEUE) {
            DeleteTemporaryFile::dispatch( $file )
                ->delay(now()->addMinutes(config( 'temporary-files.lifetime')));
        }

        return $file;
    }

    /**
     * Get a temporary file by its ID
     *
     * @param string $id
     * @return TemporaryFile
     * @throws MissingManifestException
     */
    public function get(string $id) : ?TemporaryFile
    {
        return $this->reader->get($id);
    }

    /**
     * Delete a file by its ID.
     *
     * @param string $id
     * @throws MissingManifestException
     */
    public function delete(string $id)
    {
        $file = $this->get($id);
        // If it can't be found, there's not really any need to throw an error. They're
        // designed to be cleaned up automatically.
        if(!$file) {
            return;
        }
        // Don't delete it if it's locked
        if($file->isLocked()) {
            return;
        }
        $this->getDisk()->deleteDirectory($file->getDirectory());
        event(new TemporaryFileDeleted($file));
    }

    /**
     * Get the disk
     *
     * @return Filesystem
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * Register the routes for uploading temporary files.
     *
     * Note that this is optional; you're free to implement your own
     * controller for this purpose.
     *
     * @return void
     */
    public function routes()
    {
        Route::post(
            config( 'temporary-files.upload_uri', '/files/temporary' ),
            '\Lukaswhite\TemporaryFiles\Http\Controllers\FilesController@upload'
        )->name( 'temporary-files.upload' );
    }
}