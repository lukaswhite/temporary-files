<?php

namespace Lukaswhite\TemporaryFiles\Transformers;

use Lukaswhite\TemporaryFiles\Contracts\TransformsTemporaryFiles;
use Lukaswhite\TemporaryFiles\TemporaryFile;

/**
 * Class Transformer
 *
 * This class is responsible for transforming a temporary file into
 * a format suitable for returning an upload request. You're free to
 * override this, if you want to alter the format or include additional
 * information.
 *
 * @package Lukaswhite\TemporaryFiles\Transformers
 */
class Transformer implements TransformsTemporaryFiles
{
    /**
     * Run the transformation process
     *
     * @param TemporaryFile $file
     * @return array
     */
    public function run( TemporaryFile $file ) : array
    {
        return [
            'id'                =>  $file->getId( ),
            'filename'          =>  $file->getFilename( ),
            'data'              =>  $file->getData( ),
            'expires_at'        =>  $file->getExpiresAt( )->getTimestamp( ),
            'expires_in'        =>  $file->getExpiresIn( ),
        ];
    }
}