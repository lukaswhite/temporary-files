<?php
/**
 * Temporary files configuration.
 */
return [

    /**
     * The package offers two modes:
     *
     * cron means that files that are no longer required will be cleaned up
     * periodically using a Cron job. Note that in order to use this mode, you must
     * ensure that the Cron job is configured.
     *
     * e.g.
     *
     * $schedule->command('temporary-files:cleanup')->daily();
     *
     * queue means that unused files get deleted via a delayed job. In order to use this
     * mode, you'll need to ensure you have a queue configured.
     */
    'mode'  =>  Lukaswhite\TemporaryFiles\TemporaryFiles::MODE_CRON,

    /**
     * The lifetime value indicates how long a temporary file should be stored before,
     * if it's no longer required, it gets deleted.
     *
     * This value is in minutes.
     */
    'lifetime'  =>  60,

    /**
     * The name of the disk, on which the temporary files should be stored.
     */
    'disk' => 'local',

    /**
     * The name of the directory in which the temporary files should be stored.
     */
    'directory' => 'temp-files',

    'include_user' => true,

    /**
     * If you're using the queue mode, this is the name of the queue that should be used.
     *
     * Leave the value blank to use the default queue.
     */
    'queue' => '',

    /**
     * The package comes with a controller / trait for implementing file uploads.
     */

    /**
     * Files and directories that should be ignored when reading temporary files.
     */
    'ignore' => [
        '.',
        '..',
        '.DS_STORE'
    ],

    /**
     * The URI for the upload endpoint, if using the in-built controller and registering
     * the routes using the Facade's routes() method.
     */
    'upload_uri' => '/files/temporary',

    /**
     * If you're using the provided upload controller, you can define the middleware here
     */
    'middleware' => [

    ],

    /**
     * If you're using the provided controller or trait, this specifies the key that
     * indicates where the file can be found.
     */
    'request_key' => 'file',

    /**
     * The package provides the option to store a record of a newly-created temporary file
     * in the session, for easy access. This enables or disables that option.
     */
    'store_in_session' => true,

    /**
     * The package provides the option to store a record of a newly-created temporary file
     * in the session, for easy access. This option specifies the name of the item that stores
     * the ID of the file.
     */
    'session_key' => 'last_uploaded_temporary_file_id',

    /**
     * When the controller / trait handles an uploaded file, it's necessary to transform
     * the metadata before sending it back to the client over HTTP.
     *
     * If you want to customize this transformation process, simply implement the
     * TransformsTemporaryFiles interface, and specify the fully-qualified name of your custom
     * transformer class here.
     */
    'transformer' => \Lukaswhite\TemporaryFiles\Transformers\Transformer::class,

    /**
     * When creating new temporary files, a unique ID is required. This defines the class
     * used to generate them; if you want to create your own implementation, specify
     * it here; you simply need to implement the GeneratesUniqueIds interface.
     */
    'id_generator' => \Lukaswhite\TemporaryFiles\Helpers\IdGenerator::class,
];