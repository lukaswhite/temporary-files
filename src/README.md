# Temporary Files

**"Self-destructing" temporary files for Laravel.**

## The problem this package aims to solve

Often in a CRUD application, models will have files associated with them; for example, product images.

Modern front-end applications usually provide AJAX-based file upload widgets. That way, someone can select a file and have that uploading in the background while they complete the rest of the form.

If we're talking about a pre-existing model &mdash; think an edit form &mdash; then it's pretty straightfoward to attach the ID of the model to a file upload request, so that the file can be associated with the relevant model.

However, creating a new model poses a problem. If the model hasn't yet been created, then it's not possible to associate the file with the model until later.

To get around this, you'll probably want to store an uploaded file, return a reference to it and then make the association when the whole form has been submitted; in other words create a temporary or "orphaned" file.

This poses some questions:

* Where do those files go?
* How do we make the association retrospectively?
* What happens to the file if the form gets abandoned?

You can use the system's temporary directory, but there are some issues:

* You either need to accept the system's randomly-created, unique filename &mdash; in which case, you lose the original filename &mdash; or you have to handle the storage logic.
* There's no guarrantee that the temporary file will be available when you need it
* There's also no guarrantee that the file will get cleaned up

This package offers a more robust solution to all of these problems.

In essence, what this package provides is:

* An endpoint for uploading temporary files
* A wrapper that provides a unique ID for a file, so that it can be associated with a model retrospectively
* Logic for storing metadata such as the authenticated user against the file
* Automatic cleanup of files that are no longer required

Here's a typical workflow for implementing a form in a CRUD application for creating a new model which includes, say, an image.

* The application displays a form
* The user hits an upload button to upload the image in the background, so that they continue completing the form whilst the image uploads in the background via AJAX
* The upload endpoint returns a unique ID, which gets stored in the form
* The user finishes completing the form and hits submit
* The controller responsible for creating the new mode receives the model-specific data, along with the unique ID of the uploaded image
* The file gets moved into the appropriate place, gets associated with the new model now that it exists, and the temporary file gets deleted

Meantime, files that get uploaded but never used will automatically "self-destruct"; that is, they get cleaned up in the background.

## Installation

```php
composer require lukaswhite/temporary-files
```

The package provides auto-discovery, so there's no need to register the service provider.

## Usage

The package aims to provide as much flexibility as possible, so you have a number of options. Let's run through what it provides.

### Upload Controller

A **controller** is provided that handles the upload, stores it and returns a response that includes the unique ID of the file.

You have a couple of options;

The simplest, but most opiniated way:

```php
TemporaryFiles::routes( );
```

This creates a POST endpoint at `/files/temporary`, which returns a JSON response.

Or register your own route:

```php
Route::post(
	'/uploads',
	'Lukaswhite\TemporaryFiles\Http\Controllers\FilesController::class )
)->name( 'temporary-files.upload' );
```

If you'd prefer to implement your own controller, there's a **trait** you can use.

```php
use UploadsTemporaryFiles;
```

Refer to the source code for more information.

The **facade** provides the heavy-lifting for you, if you'd rather use that directly in your own controllers.


## Setting up the "Self-Destruct" Feature

The package provides two options for self-destructing files; i.e., cleaning up, and you're free to choose the one that suits your requirements best. You do, however, have to complete one small step whichever option you prefer.

### Cron

The package provides a console command that's designed to be run periodically - say, once a day - that deletes unused files. It's enabled by default, however **you need to set up the cron job**.

For example:

```php
$schedule->command('temporary-files:cleanup')->daily();
```

### Queued Jobs

Alternatively, the package also provides the option to delete a temporary file after a certain period of time by using a delayed queued job. In order to use this option, you simply need to ensure you have a queue set up for it.

## Configuration

You'll need to publish the configuration file:

```
artisan vendor:publish --provider="Lukaswhite\TemporaryFiles\TemporaryFilesServiceProvider"
```

This will create a new file - `config/temporary-files.php`.

The newly-created config file is self-documented, but let's run through some of the most important options.

### The mode

The mode indicates the approach you want to take for cleaning up unused files, or "self-destructing". It's described earlier in this README. 

So, either:

```php
'mode'  =>  Lukaswhite\TemporaryFiles\TemporaryFiles::MODE_CRON,
```

Or:

```php
'mode'  =>  Lukaswhite\TemporaryFiles\TemporaryFiles::MODE_QUEUE,
```

### The Lifetime

This refers to the length of time between when a file has been uploaded, and when it's scheduled for deletion. For example, if someone started filling out a form, uploaded a file but didn't complete the process - resulting in an "orpaned" file.

To make this three hours:

```php
'lifetime'  =>  60,
```

### Where to store the files

There are two configuration options that dictate where the files get stored.

You can specify the disk, which defaults to `local`:

```php
'disk' => 'local',
```

The files are placed in a directory on the configured disk; here's the default:

```php
'directory' => 'temp-files',
```

### Configuring the Queue

If you opt for the delayed, queued job for deleting unused files, then you can specify the queue you wish it to use:

```php
/**
 * If you're using the queue mode, this is the name of the queue that should be used.
 *
 * Leave the value blank to use the default queue.
 */
'queue' => '',
```

### Customizing the Response

When a temporary file gets created, the relevant metadata is transformed into a format suitable for sending back over HTTP. 

Here's what it looks like out-of-the-box:

```json
{
  'id' : '5c248a41e6435',
  'filename' : 'my-uploaded-file.ext',
  'expires_at' : 1234567890,
  'expires_in' : 60
}
```

Feel free to modify this:

1. Create a new transformation class that implements the interface `TransformsTemporaryFiles`. You may wish to refer to the default `Transformer` class.
2. Specify the fully-qualified class name of your custom transformer in the config:

```php
'transformer' => \Your\YourNamespace\YourCustomTransformer::class,
```

### Customizing the Unique IDs

An uploaded temporary file gets assigned a unique ID so that it can be retrieved at the appropriate time.

Out-of-the-box, this simply uses `uniqid()`.

You're welcome to implement your own class for creating these:

1. Create a new class that implements the interface `GeneratesUniqueIds`; it has one method, `generate`, that given a filename returns a unique ID.
2. Specify the fully-qualified class name of your custom class in the config:

```php
'id_generator' => \Your\YourNamespace\YourCustomGenerator::class,
```

### Using the Session

The package can store the unique ID of the last temporary file that gets uploaded, if you wish.

In the config, enable it like so:

```php
'store_in_session' => true,
```

The following line specifies the name of the session key, which you're free to override:

```php
'session_key' => 'last_uploaded_temporary_file_id',
```


