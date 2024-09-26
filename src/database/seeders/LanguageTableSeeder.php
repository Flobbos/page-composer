<?php

namespace Flobbos\PageComposer;

use Flobbos\PageComposer\Models\Language;
use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::create([
            'name' => 'Deutsch',
            'locale' => 'de'
        ]);
    }
}
