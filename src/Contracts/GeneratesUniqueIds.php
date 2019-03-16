<?php

namespace Lukaswhite\TemporaryFiles\Contracts;

/**
 * Interface GeneratesUniqueIds
 *
 * A class that implements this interface is responsible for generating
 * unique IDs. Feel free to implement your own.
 *
 * @package Lukaswhite\TemporaryFiles\Contracts
 */
interface GeneratesUniqueIds {

    /**
     * Generate a new unique ID
     *
     * @param string $filename
     * @return string
     */
    public function generate( string $filename ) : string;

}
