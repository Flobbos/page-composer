<?php

use Illuminate\Support\Facades\Validator;

it('validates slider and newsletter images as optional stored paths', function (?string $value, bool $passes) {
    $rules = (require __DIR__ . '/../../src/config/pagecomposer.php')['rules'];

    $data = ['pageData' => ['slider_image' => $value, 'newsletter_image' => $value]];
    $subset = array_intersect_key($rules, array_flip(['pageData.slider_image', 'pageData.newsletter_image']));

    expect(Validator::make($data, $subset)->passes())->toBe($passes);
})->with([
    'stored path' => ['photos/slider_01J.jpg', true],
    'empty' => [null, true],
]);

it('rejects a non-string value for the stored image paths', function () {
    $rules = (require __DIR__ . '/../../src/config/pagecomposer.php')['rules'];

    expect(Validator::make(
        ['pageData' => ['slider_image' => ['not', 'a', 'path']]],
        ['pageData.slider_image' => $rules['pageData.slider_image']],
    )->passes())->toBeFalse();
});
