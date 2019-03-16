<?php

namespace Lukaswhite\TemporaryFiles\Http\Traits;

use Illuminate\Http\Response;
use Lukaswhite\TemporaryFiles\TemporaryFiles;
use Lukaswhite\TemporaryFiles\Contracts\TransformsTemporaryFiles;
use Lukaswhite\TemporaryFiles\Http\Requests\TemporaryFileUploadRequest;

/**
 * Class UploadsTemporaryFiles
 *
 * When applied to a trait, this provides the ability to upload temporary files.
 *
 * @package Lukaswhite\TemporaryFiles
 */
trait UploadsTemporaryFiles
{
    /**
     * Upload a temporary file
     *
     * @param TemporaryFileUploadRequest $request
     * @return Response
     */
    public function upload( TemporaryFileUploadRequest $request )
    {
        /** @var TemporaryFiles $files */

        // Get the temporary files service
        $files = app( )->make( TemporaryFiles::class );

        // Now create a new temporary file from the uploaded file, ensuring that
        // we keep the original filename
        $file = $files->create(
            $request->file( config( 'temporary-files.request_key', 'file' ) ),
            $request->file( config( 'temporary-files.request_key', 'file' ) )->getClientOriginalName( )
        );

        // Get the transformer, which can be overridden in config
        $transformer = app( )->make( TransformsTemporaryFiles::class );

        // Return a JSON response, if that's what's been requested
        if ( $request->wantsJson( ) ) {
            return response( )->json( $transformer->run( $file ) );
        }

        // Otherwise return an array; let the application handle the response.
        return response( )->make( $transformer->run( $file ) );
    }
}