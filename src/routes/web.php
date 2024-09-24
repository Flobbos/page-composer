<?php

use Flobbos\PageComposer\Livewire\Frontend\PageDisplay;

//Protected routes
Route::group(['middleware' => config('pagecomposer.middleware'), 'namespace' => 'Flobbos\PageComposer\Livewire', 'prefix' => 'page-composer', 'as' => 'page-composer::'], function () {
    Route::get('/', BugComponent::class)->name('dashboard');
    Route::get('pages', PageIndex::class)->name('pages.index');
    Route::get('pages/create', PageComposer::class)->name('pages.create');
    Route::get('pages/{page}/edit', PageComposer::class)->name('pages.edit');
});

//Public preview route
Route::get('{slug}', PageDisplay::class)->name('pages.detail');
