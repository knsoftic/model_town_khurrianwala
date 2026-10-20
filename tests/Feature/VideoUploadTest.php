<?php

namespace Tests\Feature;

use App\Enums\VideoProvider;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        $this->admin = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();
    }

    public function test_a_video_from_the_computer_needs_no_link_or_thumbnail(): void
    {
        $this->actingAs($this->admin)->get('/admin/videos/create')->assertOk()->assertSee('Upload from computer')->assertSee('Make from video');

        $this->actingAs($this->admin)->post('/admin/videos', [
            'title' => 'Site walkthrough', 'provider' => 'self_hosted', 'is_active' => '1',
            'file' => UploadedFile::fake()->create('walkthrough.mp4', 900, 'video/mp4'),
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/videos');

        $video = Video::query()->where('title', 'Site walkthrough')->firstOrFail();
        $this->assertSame(VideoProvider::SelfHosted, $video->provider);
        Storage::disk('public')->assertExists($video->file_path);
        $this->assertNull($video->url);
        $this->assertNull($video->thumbnail);
    }

    public function test_a_video_chosen_while_youtube_is_selected_is_saved_as_an_upload(): void
    {
        // The source buttons were left on YouTube and the video was picked in the thumbnail box.
        $this->actingAs($this->admin)->post('/admin/videos', [
            'title' => 'Picked in thumbnail box', 'provider' => 'youtube', 'url' => '', 'is_active' => '1',
            'thumbnail' => UploadedFile::fake()->create('clip.mp4', 900, 'video/mp4'),
        ])->assertSessionHasNoErrors();

        $video = Video::query()->where('title', 'Picked in thumbnail box')->firstOrFail();
        $this->assertSame(VideoProvider::SelfHosted, $video->provider);
        $this->assertStringEndsWith('.mp4', (string) $video->file_path);
        $this->assertNull($video->thumbnail);

        // Video in the video box plus the thumbnail made in the browser (or the admin's own image).
        $this->actingAs($this->admin)->post('/admin/videos', [
            'title' => 'With thumbnail', 'provider' => 'youtube', 'is_active' => '1',
            'file' => UploadedFile::fake()->create('clip.webm', 900, 'video/webm'),
            'thumbnail' => UploadedFile::fake()->image('clip-thumbnail.jpg', 1280, 720),
        ])->assertSessionHasNoErrors();

        $withThumb = Video::query()->where('title', 'With thumbnail')->firstOrFail();
        $this->assertSame(VideoProvider::SelfHosted, $withThumb->provider);
        Storage::disk('public')->assertExists($withThumb->thumbnail);
    }

    public function test_links_are_still_required_for_youtube_without_an_upload(): void
    {
        $this->actingAs($this->admin)->post('/admin/videos', ['title' => 'No link', 'provider' => 'youtube'])
            ->assertSessionHasErrors('url');

        $this->actingAs($this->admin)->post('/admin/videos', ['title' => 'Bad link', 'provider' => 'youtube', 'url' => 'https://example.org/video'])
            ->assertSessionHasErrors('url');

        $this->actingAs($this->admin)->post('/admin/videos', [
            'title' => 'Tour', 'provider' => 'youtube', 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame(VideoProvider::YouTube, Video::query()->where('title', 'Tour')->value('provider'));
    }
}
