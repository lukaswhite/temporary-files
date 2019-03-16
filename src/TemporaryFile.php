<?php


namespace Lukaswhite\TemporaryFiles;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

/**
 * Class TemporaryFilesServiceProvider
 *
 * @package Lukaswhite\TemporaryFiles
 */
class TemporaryFile
{
    /**
     * The unique ID of the file
     *
     * @var string
     */
    protected $id;

    /**
     * The name of the file
     *
     * @var string
     */
    protected $filename;

    /**
     * The date and time that the file is scheduled to be deleted,
     * if it's not being used.
     *
     * @var Carbon
     */
    protected $expiresAt;

    /**
     * Additional data, which gets stored in the manifest.
     *
     * @var array
     */
    protected $data;

    /**
     * Whether the file is locked. If a file is locked, it cannot be deleted.
     *
     * @var bool
     */
    protected $locked = false;

    /**
     * TemporaryFile constructor.
     *
     * @param string $id
     * @param string $filename
     * @param Carbon $expiresAt
     */
    public function __construct( string $id = null, string $filename = null, Carbon $expiresAt, array $data )
    {
        if ( $id ) {
            $this->setId( $id );
        } else {
            $this->id = uniqid( );
        }

        $this->filename = $filename;
        $this->expiresAt = $expiresAt;
        $this->data = $data;
    }

    /**
     * Get the ID of the file
     *
     * @return string
     */
    public function getId( ) : string
    {
        return $this->id;
    }

    /**
     * Sets the ID of the file
     *
     * @param string $id
     * @return self
     */
    public function setId( string $id ) : self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the path to the file
     *
     * @return string
     */
    public function getPath( ) : string
    {
        return sprintf(
            '%s%s%s',
            $this->getDirectory( ),
            DIRECTORY_SEPARATOR,
            $this->filename
        );
    }

    /**
     * Get the full path to the file
     *
     * @return string
     */
    public function getFullPath( ) : string
    {
        $disk = ( app( )->make( TemporaryFiles::class ) )->getDisk( );
        return $disk->path( $this->getPath( ) );
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename( ) : string
    {
        return $this->filename;
    }

    /**
     * Set the filename
     *
     * @param string $filename
     * @return self
     */
    public function setFilename( string $filename ) : self
    {
        $this->filename = $filename;
    }

    /**
     * Get the date and time that this file is scheduled to be deleted.
     *
     * @return Carbon
     */
    public function getExpiresAt( ) : Carbon
    {
        return $this->expiresAt;
    }

    /**
     * Sets the expiry date & time
     *
     * @var Carbon $expiresAt
     * @return self
     */
    public function setExpiresAt( Carbon $expiresAt ) : self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    /**
     * Get the additional data
     *
     * @return array
     */
    public function getData( ) : array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return TemporaryFile
     */
    public function setData( array $data ) : self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns the number of minutes before this file is scheduled for deletion.
     *
     * @return int
     */
    public function getExpiresIn( ) : int
    {
        return $this->expiresAt->diffInMinutes( Carbon::now( ) );
    }

    /**
     * Determine whether this file has expired.
     *
     * @return bool
     */
    public function hasExpired( ) : bool
    {
        return Carbon::now( )->gte( $this->getExpiresAt( ) );
    }

    /**
     * Lock the file. While a file is locked, it cannot be deleted.
     *
     * @return self
     */
    public function lock( )
    {
        $this->locked = true;
        return $this;
    }

    /**
     * Unlock the file. While a file is locked, it cannot be deleted.
     *
     * @return self
     */
    public function unlock( )
    {
        $this->locked = false;
        return $this;
    }

    /**
     * Whether the file is locked. While a file is locked, it cannot be deleted.
     *
     * @return bool
     */
    public function isLocked( )
    {
        return $this->locked;
    }

    /**
     * Get the directory for the temporary file
     *
     * @return string
     */
    public function getDirectory( )
    {
        return sprintf(
            '%s%s%s',
            config( 'temporary-files.directory' ),
            DIRECTORY_SEPARATOR,
            $this->getId( )
        );
    }

    /**
     * Get the path to this file's manifest
     *
     * @return string
     */
    public function getManifestPath( )
    {
        return sprintf(
            '%s%s%s',
            $this->getDirectory( ),
            DIRECTORY_SEPARATOR,
            'manifest.json'
        );
    }
}