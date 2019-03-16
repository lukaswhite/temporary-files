<?php

namespace Lukaswhite\TemporaryFiles\Http\Traits;

use Illuminate\Http\Response;
use Lukaswhite\TemporaryFiles\TemporaryFiles;
use Lukaswhite\TemporaryFiles\Contracts\TransformsTemporaryFiles;
use Lukaswhite\TemporaryFiles\Http\Requests\TemporaryFileUploadRequest;

/**
 * Class DeletesTemporaryFiles
 *
 * When applied to a trait, this provides the ability to delete temporary files.
 *
 * @package Lukaswhite\TemporaryFiles
 */
trait DeletesTemporaryFiles
{
    /**
     * Delete a temporary file
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function delete( string $id, Request $request )
    {
        /** @var TemporaryFiles $files */

        // Get the temporary files service
        $files = app( )->make( TemporaryFiles::class );

        // Build the response
        $response = [
            'id'        =>  $id,
            'deleted'   =>  true,
        ];

        // Return a JSON response, if that's what's been requested
        if ( $request->wantsJson( ) ) {
            return response( )->json( $response );
        }

        // Otherwise return an array; let the application handle the response.
        return response( )->make( $response );
    }
}