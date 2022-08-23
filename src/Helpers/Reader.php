<?php


namespace Lukaswhite\TemporaryFiles\Helpers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Lukaswhite\TemporaryFiles\Exceptions\MissingManifestException;
use Lukaswhite\TemporaryFiles\TemporaryFile;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Class Reader
 *
 * This helper class is responsible for reading temporary files, and extracting the necessary
 * metadata.
 *
 * @package Lukaswhite\TemporaryFiles\Helpers
 */
class Reader
{
    /**
     * The disk
     *
     * @var Filesystem
     */
    protected $disk;

    /**
     * The name of the directory in which the temporary files should be stored.
     *
     * @var string
     */
    protected $directory;

    /**
     * Reader constructor.
     *
     * @param Filesystem $disk
     * @param string $directory
     */
    public function __construct( Filesystem $disk, string $directory )
    {
        $this->disk = $disk;
        $this->directory = $directory;
    }

    /**
     * Get the disk
     *
     * @return Filesystem
     */
    public function getDisk( ) : Filesystem
    {
        return $this->disk;
    }

    /**
     * Get all of the temporary files
     *
     * @return Collection
     */
    public function all( )
    {
        return $directories = collect( Storage::directories( $this->directory ) )
            ->filter( function( $directory ) {
                return ! in_array( $directory, config( 'temporary-files.ignore', [ ] ) );
            }
        )->map( function( $directory ) {

            $id = substr( $directory, strripos( $directory, '/' ) + 1 );
            return $this->get( $id );

        } );
    }

    /**
     * Get all of the temporary files that have expired
     *
     * @return Collection
     */
    public function expired( )
    {
        return $this->all( )->filter( function( TemporaryFile $file ) {
            return $file->hasExpired( );
        } );
    }

    /**
     * Get all of the temporary files that have expired, and aren't locked.
     *
     * @return Collection
     */
    public function expiredAndNotLocked( )
    {
        return $this->expired( )->filter( function( TemporaryFile $file ) {
            return ! $file->isLocked( );
        } );
    }

    /**
     * Get a temporary file by its ID
     *
     * @param string $id
     * @return TemporaryFile|null
     * @throws MissingManifestException
     */
    public function get( string $id )
    {
        // Build the path
        $path = sprintf( '%s%s%s', $this->directory, DIRECTORY_SEPARATOR, $id );

        // If the file does not exist, which isn't necessarily unexpected behavior,
        // simply return null
        if ( ! $this->disk->exists( $path ) ) {
            return null;
        }

        // Grab the manifest; this gives us, amongst other things, the expiry time
        try {
            $manifest = Manifest::read(
                $this->disk->get( sprintf('%s%s%s', $path, DIRECTORY_SEPARATOR, 'manifest.json' ) )
            );
        } catch ( FileNotFoundException $e ) {
            throw new MissingManifestException( sprintf( 'Manifest not found for file %s', $id ) );
        }

        // Get a record of this file
        $file = $manifest->getFile( );

        // If appropriate, lock the file
        /**
        if ( property_exists( $manifest, 'locked' ) && $manifest->locked ) {
            $file->lock( );
        }**/

        return $file;
    }

    /**
     * Get the contents of the specified temporary file, by ID.
     *
     * @return string|resource
     */
    public function getContents( string $id )
    {
        $file = $this->get( $id );

        if ( ! $file ) {
            return null;
        }

        return $this->disk->get( $file->getFilepath( ) );
    }

}