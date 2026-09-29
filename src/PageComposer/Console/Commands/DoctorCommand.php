<?php

namespace Flobbos\PageComposer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

/**
 * Checks files that were published into the app and therefore don't get
 * fixed by `composer update`.
 */
class DoctorCommand extends Command
{
    protected $signature = 'page-composer:doctor';

    protected $description = 'Check published Page Composer files for known security issues';

    public function handle(): int
    {
        $problems = [];

        $photo = app_path('Livewire/PageComposerElements/Photo.php');
        if (File::exists($photo)) {
            $source = File::get($photo);

            if (!str_contains($source, '$this->validate(')) {
                $problems[] = "{$photo} saves uploads without validating them. Copy savePhoto() from the package's src/PageComposer/Livewire/Elements/Photo.php.";
            }

            if (str_contains($source, 'getClientOriginalExtension()')) {
                $problems[] = "{$photo} names stored files with the client-supplied extension. Use ->extension() instead.";
            }
        }

        $middleware = Arr::wrap(config('pagecomposer.middleware'));
        if (!in_array('web', $middleware, true)) {
            $problems[] = "pagecomposer.middleware doesn't include 'web', so sessions and CSRF protection don't run on the Page Composer routes.";
        }

        foreach ((array) config('pagecomposer.rules', []) as $field => $rule) {
            if (is_string($rule) && str_starts_with($rule, 'sometimes:')) {
                $problems[] = "The pagecomposer.rules entry for {$field} ('{$rule}') validates nothing. Use 'nullable|string'.";
            }
        }

        if (empty($problems)) {
            $this->info('No known problems found.');

            return self::SUCCESS;
        }

        foreach ($problems as $problem) {
            $this->warn($problem);
        }

        return self::FAILURE;
    }
}
