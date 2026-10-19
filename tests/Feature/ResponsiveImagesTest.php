<?php

namespace Tests\Feature;

use App\Models\HomepageSection;
use App\Services\MediaUploader;
use App\Services\ResponsiveImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResponsiveImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
    }

    public function test_uploads_are_served_as_resized_copies_and_removed_with_the_original(): void
    {
        $path = app(MediaUploader::class)->store(UploadedFile::fake()->image('hero.png', 2000, 1000), 'sections');
        $attributes = app(ResponsiveImages::class)->attributes($path, '100vw', [640, 1280, 2400]);

        $this->assertSame(2000, $attributes['width']);
        $this->assertSame(1000, $attributes['height']);
        $this->assertSame('100vw', $attributes['sizes']);
        $this->assertStringContainsString(' 640w', $attributes['srcset']);
        $this->assertStringContainsString(' 1280w', $attributes['srcset']);
        $this->assertStringContainsString(' 2000w', $attributes['srcset'], 'Requests above the original size are capped at the original width');
        $this->assertStringNotContainsString('2400w', $attributes['srcset']);

        $variant = 'variants/640/'.preg_replace('/\.png$/', '.webp', $path);
        Storage::disk('public')->assertExists($variant);
        $this->assertSame(640, getimagesize(Storage::disk('public')->path($variant))[0]);

        app(MediaUploader::class)->delete($path);

        Storage::disk('public')->assertMissing($variant);
    }

    public function test_svgs_and_external_images_are_passed_through(): void
    {
        Storage::disk('public')->put('uploads/demo/plan.svg', "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'></svg>");

        $svg = app(ResponsiveImages::class)->attributes('uploads/demo/plan.svg');
        $this->assertSame(['src', 'width', 'height'], array_keys($svg));
        $this->assertSame([800, 600], [$svg['width'], $svg['height']]);

        $this->assertSame(['src' => 'https://img.youtube.com/vi/x/hqdefault.jpg'], app(ResponsiveImages::class)->attributes('https://img.youtube.com/vi/x/hqdefault.jpg'));
        $this->assertSame([], app(ResponsiveImages::class)->attributes(null));
    }

    public function test_logo_is_sized_for_its_display_height(): void
    {
        $path = app(MediaUploader::class)->store(UploadedFile::fake()->image('logo.png', 1000, 250), 'settings');
        $attributes = app(ResponsiveImages::class)->attributes($path, height: 44);

        $this->assertSame(176, $attributes['width']);
        $this->assertSame(44, $attributes['height']);
        $this->assertSame('176px', $attributes['sizes']);
        $this->assertStringContainsString(' 352w', $attributes['srcset']);
    }

    public function test_homepage_hero_is_preloaded_and_media_optimize_prepares_copies(): void
    {
        $path = app(MediaUploader::class)->store(UploadedFile::fake()->image('hero.jpg', 1600, 900), 'sections');
        $hero = HomepageSection::query()->where('type', 'hero')->firstOrFail();
        $hero->update(['settings' => ['background_image' => $path] + (array) $hero->settings]);

        $this->get('/')->assertOk()
            ->assertSee('<link rel="preload" as="image"', false)
            ->assertSee('imagesizes="100vw"', false)
            ->assertSee('variants/960/', false);

        $this->artisan('media:optimize')->assertSuccessful();
        Storage::disk('public')->assertExists('variants/96/'.preg_replace('/\.jpg$/', '.webp', $path));
    }

    public function test_llms_txt_summarises_the_site(): void
    {
        $this->get('/llms.txt')->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# '.setting('site_name'))
            ->assertSee('## Plots')
            ->assertSee('('.route('plots.show', '5-marla').')', false)
            ->assertSee('## Pages');
    }
}
