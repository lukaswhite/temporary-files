<?php


namespace Lukaswhite\TemporaryFiles\Helpers;

use Carbon\Carbon;
use Lukaswhite\TemporaryFiles\TemporaryFile;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class Manifest
 *
 * When storing a temporary file, the package creates a manifest, which indicates
 * when the file expires. This helper is used to read and write them.
 *
 * @package Lukaswhite\TemporaryFiles\Helpers
 */
class Manifest
{
    /**
     * The path to the manifest file.
     *
     * @var string
     */
    protected $contents;

    /**
     * The file that this manifest refers to
     *
     * @var TemporaryFile
     */
    protected $file;

    /**
     * The user who uploaded the file
     *
     * @var Authenticatable
     */
    protected $user;

    /**
     * Manifest constructor.
     *
     * @param string $path
     */
    public function __construct( TemporaryFile $file )
    {
        $this->file = $file;
    }

    /**
     * Get the file that the manifest refers to
     *
     * @return TemporaryFile
     */
    public function getFile( ) : TemporaryFile
    {
        return $this->file;
    }

    /**
     * Set the file that the manifest refers to
     *
     * @param TemporaryFile $file
     * @return self
     */
    public function setFile( TemporaryFile $file ) : self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Set the user who uploaded the file
     *
     * @param Authenticatable $user
     * @return self
     */
    public function setUser( Authenticatable $user ) :self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Read a manifest
     *
     * @param string $data
     * @return self
     */
    public static function read( string $data ) : self
    {
        $decoded = json_decode(
            $data
        );

        // Get the additional data, if there is any
        $additionalData = isset( $data[ 'data' ] ) && $data[ 'data' ] ? $data[ 'data' ] : [ ];

        $file = new TemporaryFile(
            $decoded->id,
            $decoded->filename,
            Carbon::createFromTimestamp( $decoded->expires_at ),
            $additionalData
        );

        if ( property_exists( $decoded, 'locked' ) && $decoded->locked ) {
            $file->lock( );
        }

        return new self( $file );
    }

    /**
     * Write the manifest for the specified temporary file
     *
     * @param TemporaryFile $file
     * @return self
     */
    public function write( Filesystem $disk ) : self
    {
        // Build the contents of the manifest...
        $data = [
            'id'            =>  $this->file->getId( ),
            'filename'      =>  $this->file->getFilename( ),
            'expires_at'    =>  $this->file->getExpiresAt( )->getTimestamp( ),
            'data'          =>  $this->file->getData( ),
        ];

        // Optionally insert the authenticated user identifier...
        if ( $this->user ) {
            $data[ 'user_id' ] = $this->user->getAuthIdentifier( );
        }

        // ...and place it in storage
        $disk->put(
            $this->file->getManifestPath( ),
            json_encode( $data )
        );

        return $this;
    }
}