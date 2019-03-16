<?php


namespace Lukaswhite\TemporaryFiles\Http\Controllers;

use Illuminate\Routing\Controller;
use Lukaswhite\TemporaryFiles\Http\Traits\UploadsTemporaryFiles;

/**
 * Class FilesController
 *
 * @package Lukaswhite\TemporaryFiles
 */
class FilesController extends Controller
{
    use UploadsTemporaryFiles;
}