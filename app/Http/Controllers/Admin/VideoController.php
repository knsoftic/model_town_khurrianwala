<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VideoProvider;
use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\MediaUploader;
use App\Support\UploadRules;
use App\Support\VideoEmbed;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VideoController extends Controller
{
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
        $file = $request->file('file');
        $thumbnail = $request->file('thumbnail');

        // A video chosen in the thumbnail box (easy to do by mistake) is used as the video itself.
        if (! $file && $thumbnail && str_starts_with((string) $thumbnail->getMimeType(), 'video/')) {
            [$file, $thumbnail] = [$thumbnail, null];
        }

        $provider = VideoProvider::tryFrom((string) $request->input('provider'));

        // An uploaded video with no link means "upload from computer", whichever source button is selected.
        if ($file && blank($request->input('url'))) {
            $provider = VideoProvider::SelfHosted;
        }

        $needsFile = $provider === VideoProvider::SelfHosted && ! $video->file_path;
        $input = array_merge($request->all(), ['provider' => $provider?->value, 'file' => $file, 'thumbnail' => $thumbnail]);

        $data = Validator::make($input, [
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
        ], [
            'file.required' => 'Choose the video file from your computer (MP4 or WebM).',
            'url.required' => 'Paste the YouTube or Vimeo link, or choose “Upload from computer” and select your video file.',
        ])->validate();

        $media = app(MediaUploader::class);

        unset($data['file']);
        $data['file_path'] = $provider === VideoProvider::SelfHosted
            ? $media->replace($file, $video->file_path, 'videos', $request->boolean('remove_file'))
            : tap(null, fn () => $media->delete($video->file_path));
        $data['url'] = $provider === VideoProvider::SelfHosted ? null : ($data['url'] ?? null);
        $data['thumbnail'] = $media->replace($thumbnail, $video->thumbnail, 'videos', $request->boolean('remove_thumbnail'));
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $video->fill($data)->save();

        if ($video->is_featured) {
            Video::query()->whereKeyNot($video->id)->update(['is_featured' => false]);
        }
    }
}
