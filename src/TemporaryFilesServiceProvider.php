<?php


namespace Lukaswhite\TemporaryFiles;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Lukaswhite\TemporaryFiles\Console\Commands\Cleanup;
use Lukaswhite\TemporaryFiles\Console\Commands\Clear;
use Lukaswhite\TemporaryFiles\Contracts\GeneratesUniqueIds;
use Lukaswhite\TemporaryFiles\Contracts\TransformsTemporaryFiles;
use Lukaswhite\TemporaryFiles\Helpers\IdGenerator;
use Lukaswhite\TemporaryFiles\Helpers\Reader;
use Lukaswhite\TemporaryFiles\Transformers\Transformer;

/**
 * Class TemporaryFilesServiceProvider
 *
 * @package Lukaswhite\TemporaryFiles
 */
class TemporaryFilesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Merge in the config settings
        $configPath = __DIR__ . '/config.php';
        $this->mergeConfigFrom( $configPath, 'temporary-files' );

        // Register the package's migrations
        //$this->loadMigrationsFrom( __DIR__ . '/../../../../migrations' );

        // Register the console commands
        if ( $this->app->runningInConsole( ) ) {
            $this->commands([
                Cleanup::class,
                Clear::class,
            ] );
        }

        $this->app->bind( GeneratesUniqueIds::class, function( ) {
            return app( )->make( config( 'temporary-files.id_generator', IdGenerator::class ) );
        } );

        $this->app->bind( TransformsTemporaryFiles::class, function( ) {
            return app( )->make( config( 'temporary-files.transformer', Transformer::class ) );
        } );

        $this->app->singleton( Reader::class, function( ) {
            return new Reader(
                Storage::disk( config( 'temporary-files.disk' ) ),
                config( 'temporary-files.directory' )
            );
        } );

        $this->app->singleton( TemporaryFiles::class, function( ) {
            return new TemporaryFiles(
                Storage::disk( config( 'temporary-files.disk' ) ),
                app( )->make( Reader::class ),
                app( )->make( GeneratesUniqueIds::class )
            );
        } );


    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

}