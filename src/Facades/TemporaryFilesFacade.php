<?php


namespace Lukaswhite\TemporaryFiles\Facades;

use Lukaswhite\TemporaryFiles\TemporaryFiles;

/**
 * Class TemporaryFilesFacade
 *
 * Provides a Laravel facade to the temporary files package.
 *
 * @package Lukaswhite\TemporaryFiles
 */
class TemporaryFilesFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the facade accessor
     *
     * @return string
     */
    protected static function getFacadeAccessor( )
    {
        return TemporaryFiles::class;
    }
}