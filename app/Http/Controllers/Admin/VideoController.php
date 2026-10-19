<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VideoProvider;
use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\MediaUploader;
use App\Support\UploadRules;
use App\Support\VideoEmbed;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VideoController extends Controller
{
    use HandlesMedia;

    public function index(): View
    {
        return view('admin.videos.index', ['videos' => Video::query()->ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.videos.form', ['video' => new Video(['provider' => VideoProvider::YouTube, 'is_active' => true, 'sort_order' => Video::nextSortOrder()])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, new Video);

        return redirect()->route('admin.videos.index')->with('success', 'Video added.');
    }

    public function edit(Video $video): View
    {
        return view('admin.videos.form', ['video' => $video]);
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        $this->save($request, $video);

        return redirect()->route('admin.videos.index')->with('success', "“{$video->title}” saved.");
    }

    public function destroy(Video $video, MediaUploader $media): RedirectResponse
    {
        $media->delete($video->file_path);
        $media->delete($video->thumbnail);
        $video->delete();

        return redirect()->route('admin.videos.index')->with('success', 'Video deleted.');
    }

    protected function save(Request $request, Video $video): void
    {
        $provider = VideoProvider::tryFrom((string) $request->input('provider'));
        $needsFile = $provider === VideoProvider::SelfHosted && ! $video->file_path;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'provider' => ['required', Rule::enum(VideoProvider::class)],
            'url' => ['nullable', Rule::requiredIf($provider !== VideoProvider::SelfHosted), 'url', 'max:1000', function (string $attribute, mixed $value, Closure $fail) use ($provider) {
                if ($provider !== VideoProvider::SelfHosted && filled($value) && ! VideoEmbed::isSupported($value)) {
                    $fail('Paste a YouTube or Vimeo video link.');
                }
            }],
            'file' => UploadRules::video($needsFile),
            'thumbnail' => UploadRules::image(),
            'description' => ['nullable', 'string', 'max:2000'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], ['file.required' => 'Upload the video file (MP4 or WebM).']);

        unset($data['file']);
        $data['file_path'] = $provider === VideoProvider::SelfHosted
            ? $this->media($request, 'file', $video->file_path, 'videos')
            : tap(null, fn () => app(MediaUploader::class)->delete($video->file_path));
        $data['thumbnail'] = $this->media($request, 'thumbnail', $video->thumbnail, 'videos');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $video->fill($data)->save();

        if ($video->is_featured) {
            Video::query()->whereKeyNot($video->id)->update(['is_featured' => false]);
        }
    }
}
