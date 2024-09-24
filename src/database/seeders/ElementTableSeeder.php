<?php

namespace Flobbos\PageComposer;

use Flobbos\PageComposer\Models\Element;
use Illuminate\Database\Seeder;

class ElementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Element::create([
            'name' => 'Headline Text',
            'component' => 'headline-text',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>'
        ]);
        Element::create([
            'name' => 'Text',
            'component' => 'text',
            'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.5 3H3V6.5H4V4H6.5V3Z" fill="currentColor" /><path d="M8.5 4V3H11V4H8.5Z" fill="currentColor" /><path d="M13 4H15.5V3H13V4Z" fill="currentColor" /><path d="M17.5 3V4H20V6.5H21V3H17.5Z" fill="currentColor" /><path d="M21 8.5H20V11H21V8.5Z" fill="currentColor" /><path d="M21 13H20V15.5H21V13Z" fill="currentColor" /><path d="M21 17.5H20V20H17.5V21H21V17.5Z" fill="currentColor" /><path d="M15.5 21V20H13V21H15.5Z" fill="currentColor" /><path d="M11 21V20H8.5V21H11Z" fill="currentColor" /><path d="M6.5 21V20H4V17.5H3V21H6.5Z" fill="currentColor" /><path d="M3 15.5H4V13H3V15.5Z" fill="currentColor" /><path d="M3 11H4V8.5H3V11Z" fill="currentColor" /><path d="M11 9.5H7V7.5H17V9.5H13V16.5H11V9.5Z" fill="currentColor" /></svg>'
        ]);
        Element::create([
            'name' => 'Photo',
            'component' => 'photo',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>'
        ]);
        Element::create([
            'name' => 'YouTube',
            'component' => 'you-tube',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>'
        ]);
    }
}
