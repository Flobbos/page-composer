# PageComposer

![Page Composer](img/page-composer.png)

**Handle your content a little differently**

This package aims to create a flexible CMS experience for the user as well as the developer. Content is divided into rows and columns which contain elements of your choosing, text, photo, video and elements you can create based on your needs. This is a different approach at handling website content. I hope you like it.

### Docs

-   [Installation](#installation)
-   [Configuration](#configuration)
-   [Assets](#assets)
-   [Laravel compatibility](#laravel-compatibility)

## Installation

### Install package

Add the package in your composer.json by executing the command.

```bash
composer require flobbos/page-composer
```

PageComposer features auto discover for Laravel. In case this fails, just add the
Service Provider to the app.php file.

```
Flobbos\PageComposer\PageComposerServiceProvider::class,
```

### Running the installation routine

Using the new install command you are guided through the process of publishing all necessary files as well
as set up all required directories and symlinks.

```bash
php artisan page-composer:install
```

Follow the step by step process or alternatively you can just run everything at once. There is a prompt
for that option.

### Publish configuration file

This step is very important because it publishes the NewsletterTemplate model
to the App folder so you can set your own fillable fields as well as
relationships you may need. The template generator needs to have this model
present otherwise you will receive an error.

This also publishes the inital base layout that will be used to generate
newsletter templates.

```bash
php artisan vendor:publish --tag=page-composer-config
```

Here you need to use the route previously defined for your controller. The default
is the same but you will also be asked during the generation process.

### Migrations

During the publishing process the migration for the newsletter_templates table
was also published. Add all fields you need and run the migration.

```bash
php artisan migrate
```

### Adding the package

### Routes

Routes that are used by LaravelCM need to be added to your routes file. Since version 3.x you need to
specify the namespace of the NewsletterTemplateController generated from LaravelCM.

```php
use App\Http\Controllers\NewsletterTemplateController;

CMRoutes::load(NewsletterTemplateController::class);
```

This is all you need to do for the routes to load.

If you want to add the routes to your NewsletterTemplateController manually you
can simply add the following routes:

```php
Route::put('newsletter-template/generate-template/{id}', [NewsletterTemplateController::class, 'generateTemplate'])->name('newsletter-templates.generate-template');
Route::put('newsletter-template/update-template/{id}', [NewsletterTemplateController::class, 'updateTemplate'])->name('newsletter-templates.update-template');
Route::get('templates/{id}/send-preview', [NewsletterTemplateController::class, 'sendPreview'])->name('newsletter-templates.send-preview');
Route::resource('newsletter-templates', NewsletterTemplateController::class)
```

### Tailwind responsive menu

Since the switch to Tailwind the default Laravel menu has a responsive menu. Just include
the provided menu where the rest of the responsive Laravel menu is located.

```php
@include('page-composer::menu-responsive')
```

That's it. You're ready to roll. Let's move on to the configuration

## Configuration

### Client API Key

Set your Campaign Monitor client API key here to have access to the API.

```php
'client_api_key' => 'your secret key'
```

### Client ID

Set your Campaign Monitor client ID.

```php
'client_id' => 'your client ID'
```

### Default list ID

If you have created a list at Campaign Monitor you can set a default list. If
not you can create a list using the API and insert it here later.

```php
'default_list_id' => 'your default list ID'
```

### Base URI

This is the base URI where the Campaign Monitor API is being called. This might
change in the future with new releases of their API. For now, don't touch it.

```php
'base_uri' => 'https://api.createsend.com/api/v3.2/'
```

### Storage path

If you plan on importing XLS files with email addresses this determines the
storage path used for it.

```php
'storage_path' => 'xls'
```

### URL Path

Determine the base route for the package.

```php
'url_path'=>'page-composer'
```

### Format

Here you can set the default format being used to communicate with the API. For
the moment only JSON is supported.

```php
'format' => 'json'
```

### Confirmation emails

By default you can have a list of up to 5 email addresses in a comma separated
list where confirmations will be sent when a campaign is sent.

```php
'confirmation_emails' => 'you@example.com,xyz@example.com',
```

## Assets

### Naming conventions

A default layout file is provided for you to work with. Additional layout files
can be generated depending on what you need. The folder structure is simple and
as follows:

```php
/resources
    /defaults
        /base
            base.blade.php
            base.scss
```

This folder will get copied into your resources folder and you should put your
default layout design into these files. You can also add an images folder
which will also get copied once a new template gets generated.

The default should only contain your base layout. Subsequent changes should be
made to the files that have been generated for a particular newsletter template.

## Generators

### Controller generator

With this command you can generate a boiler plate controller where you can grab
your content and generate the templates used for your campaigns.

First parameter is the controller name. The route parameter tells the generator
where the default routes for the views/controller should be and the views
parameter tells the controller where the view path should be.

```php
php artisan page-composer:controller NewsletterController --route=admin.newsletter-template --views=page-composer.templates
```

### Views generator

This command generates the views needed for making templates to be used in
your campaigns.

First parameter is the path where the views should be located at. Should match
the path you gave to the controller command.

```php
php artisan page-composer:views /view/path --route=page-composer.templates
```

### Layouts Generator

You can generate a new layout by simply using the following command. This will
generate a new blank layout file for you to edit.

```php
php artisan page-composer:layout name-of-layout
```

## Usage

### Dashboard

The dashboard contains an overview of your config settings and a mini
documentation on how to use the package.

### Campaigns

The campaigns overview shows your draft/scheduled/sent campaigns that were
retrieved from Campaign Monitor via API. Here you can create/schedule/preview
your campaigns as well as view basic statistical information.

### Lists

The lists section lets you create/edit different email lists that are synced
to Campaign Monitor. Here you can also view basic statistical information about
your list such as subscribes/unsubscribes/bounces.

### Subscribers

Here you can view all your subscriber information across different lists that
you can select. It also gives you the option to import large amounts of
subscribers from and XLS file. The format should be:

```xls
EmailAddress    Name
```

Be careful if the subscribers are already confirmed and are imported into a
double-opt-in list, they will all receive a confirmation email where they
basically have to resubscribe.

You can also manually unsubscribe users as well as resubscribe them and view
basic information about your subscribers.

## Exceptions

### ConfigKeyNotSetException

This exception will be thrown if a configuration key is missing from your
config file but is needed to perform a certain API call.

### TemplateNotFoundException

This exception happens when you try to use a template that doesn't physically
exist.

## Laravel compatibility

| Laravel | LaravelCM |
| :------ | :-------- |
| 10.x    | >0.0.1\*  |

Lower versions of Laravel are not supported.
