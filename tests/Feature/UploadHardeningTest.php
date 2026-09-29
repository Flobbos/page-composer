<?php

use App\Livewire\PageComposerElements\Photo;
use Flobbos\PageComposer\Livewire\ImageUploadComponent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    View::addLocation(__DIR__ . '/../Fixtures/views');
});

/**
 * The Photo element is a publish-only stub under the App namespace, so it
 * isn't autoloaded in the package. Load it and register it under its own
 * name: the base TestCase maps page-composer-elements.photo to StubElement.
 */
function photoElement(array $content = [])
{
    require_once __DIR__ . '/../../src/PageComposer/Livewire/Elements/Photo.php';
    Livewire::component('test-photo-element', Photo::class);

    return Livewire::test('test-photo-element', ['data' => ['content' => $content], 'itemKey' => 0]);
}

it('rejects a non-image upload on the photo element', function () {
    photoElement()
        ->set('photo', UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'))
        ->assertHasErrors('photo')
        ->assertSet('photo', null)
        ->call('savePhoto')
        ->assertHasErrors('photo');

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('stores a valid photo on the public disk with its real extension', function () {
    $component = photoElement()
        ->set('photo', UploadedFile::fake()->image('Holiday Pic.jpg'))
        ->call('savePhoto')
        ->assertHasNoErrors();

    $stored = $component->get('data.content.photo');

    expect($stored)->toMatch('/^holiday-pic_[0-9A-Z]{26}\.jpg$/')
        ->and(Storage::disk('public')->exists('photos/' . $stored))->toBeTrue();
});

it('removes the replaced photo from the public disk', function () {
    Storage::disk('public')->put('photos/old.jpg', 'x');

    photoElement(['photo' => 'old.jpg'])
        ->set('photo', UploadedFile::fake()->image('new.jpg'))
        ->call('savePhoto')
        ->assertHasNoErrors();

    expect(Storage::disk('public')->exists('photos/old.jpg'))->toBeFalse();
});

function imageUpload(?string $existing = null)
{
    return Livewire::test(ImageUploadComponent::class, [
        'existingImage' => $existing,
        'eventTarget' => 'pageComposer.mainPhoto',
        'fieldName' => 'photo',
        'imagePath' => 'photos/',
    ]);
}

it('does not let the client repoint the upload component at other files', function (string $property, string $value) {
    imageUpload('photos/mine.jpg')->set($property, $value);
})->with([
    ['existingImage', 'someone-else/important.jpg'],
    ['imagePath', 'somewhere-else/'],
    ['eventTarget', 'somethingElse'],
    ['fieldName', 'name'],
])->throws(CannotUpdateLockedPropertyException::class);

it('only deletes the image it was mounted with', function () {
    Storage::disk('public')->put('photos/mine.jpg', 'x');
    Storage::disk('public')->put('someone-else/important.jpg', 'x');

    imageUpload('photos/mine.jpg')
        ->call('deleteExistingImage')
        ->assertDispatched('eventImageUploadComponentDeleted.pageComposer.mainPhoto');

    expect(Storage::disk('public')->exists('photos/mine.jpg'))->toBeFalse()
        ->and(Storage::disk('public')->exists('someone-else/important.jpg'))->toBeTrue();
});

it('stores uploads with their real extension and remembers the stored path', function () {
    $component = imageUpload()
        ->set('image', UploadedFile::fake()->image('Cover.png'))
        ->call('saveImage')
        ->assertHasNoErrors();

    $path = $component->get('existingImage');

    expect($path)->toBeString()->toMatch('#^photos/cover_[0-9A-Z]{26}\.png$#')
        ->and($component->get('imageInput'))->toBe($path)
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('rejects a non-image upload on the upload component', function () {
    imageUpload()
        ->set('image', UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'))
        ->assertHasErrors('image')
        ->assertSet('image', null)
        ->call('saveImage')
        ->assertHasErrors('image');

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

/**
 * BugComponent eager-loads Bug::user(), which points at App\Models\User, and
 * the testbench skeleton ships no users table here.
 */
function bugReporter(): \Flobbos\PageComposer\Tests\Fixtures\User
{
    if (!class_exists('App\\Models\\User')) {
        class_alias(\Flobbos\PageComposer\Tests\Fixtures\User::class, 'App\\Models\\User');
    }

    \Illuminate\Support\Facades\Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    return \Flobbos\PageComposer\Tests\Fixtures\User::create(['name' => 'Reporter']);
}

it('stores bug report screenshots with their real extension', function () {
    $user = bugReporter();

    Livewire::actingAs($user)
        ->test(\Flobbos\PageComposer\Livewire\BugComponent::class)
        ->set('newPhotos', [UploadedFile::fake()->image('Screen Shot.png')])
        ->set('title', 'Broken')
        ->set('description', 'It broke')
        ->call('saveBug')
        ->assertHasNoErrors();

    $bug = \Flobbos\PageComposer\Models\Bug::firstOrFail();

    expect($bug->photos)->toHaveCount(1)
        ->and($bug->photos[0])->toEndWith('.png')
        ->and(Storage::disk('public')->exists('photos/' . $bug->photos[0]))->toBeTrue();
});

it('rejects non-image bug report attachments', function () {
    $user = bugReporter();

    Livewire::actingAs($user)
        ->test(\Flobbos\PageComposer\Livewire\BugComponent::class)
        ->set('newPhotos', [UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;')])
        ->assertHasErrors('newPhotos.0');

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});
