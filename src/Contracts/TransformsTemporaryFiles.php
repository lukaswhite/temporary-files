<?php

namespace Lukaswhite\TemporaryFiles\Contracts;

use Lukaswhite\TemporaryFiles\TemporaryFile;

/**
 * Interface TransformsTemporaryFiles
 *
 * When implementing an uploader for temporary files, it's going
 * to necessary to transform the file into a format suitable for returning via
 * HTTP. The key information is the unique ID of the uploaded file, which would typically be
 * stored in the front-end, then sent with the rest of the information when the form
 * gets submitted so that the newly-created entity can be retrospectively associated with the file.
 *
 * Because this package does not try to limit how you implement this from the front-end, you're
 * free to customize the way in which a temporary file is formatted for returning; simply implement
 * this interface and specify it in the package config.
 *
 * Of course, should you want even more control over the output, you're free to implement
 * your own controller.
 *
 * @package Lukaswhite\TemporaryFiles\Contracts
 */
interface TransformsTemporaryFiles {

    /**
     * Run the transformation process
     *
     * @param TemporaryFile $file
     * @return array
     */
    public function run( TemporaryFile $file ) : array;

}
