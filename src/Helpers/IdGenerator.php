<?php


namespace Lukaswhite\TemporaryFiles\Helpers;
use Lukaswhite\TemporaryFiles\Contracts\GeneratesUniqueIds;

/**
 * Class IdGenerator
 *
 * This class is responsible for generating a unique ID for a temporary file.
 *
 * It's very simple; it simply uses uniqid(). However should you wish to define your own,
 * you simply need to:
 *
 *  - implement the GeneratesUniqueIds interface
 *  - set the name of your new class in the config (id_generator)
 *
 * @package Lukaswhite\TemporaryFiles\Helpers
 */
class IdGenerator implements GeneratesUniqueIds
{
    /**
     * Generate a new unique ID
     *
     * @param string $filename
     * @return string
     */
    public function generate( string $filename ) : string
    {
        return uniqid( );
    }
}