<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\MailSettings;
use App\Services\SettingsService;
use App\Support\MapEmbed;
use App\Support\RedirectLink;
use App\Support\UploadRules;
use App\Support\VerificationTags;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SettingController extends Controller
{
    use HandlesMedia;

    public function __construct(protected SettingsService $settings) {}

    public function edit(string $group): View
    {
        abort_unless(isset($this->settings->groups()[$group]), 404);

        return view('admin.settings.edit', [
            'group' => $group,
            'groups' => $this->settings->groups(),
            'definition' => $this->settings->groups()[$group],
            'fields' => $this->settings->fields($group),
            'values' => $this->settings->all(),
        ]);
    }

    public function update(Request $request, string $group, ActivityLogger $logger): RedirectResponse
    {
        abort_unless(isset($this->settings->groups()[$group]), 404);

        $fields = collect($this->settings->fields($group))
            ->reject(fn ($field) => ($field['super'] ?? false) && ! $request->user()->isSuperAdmin());

        $request->merge($this->extracted($request, $fields));
        $request->validate($fields->mapWithKeys(fn ($field, $key) => [$key => $this->rules($key, $field)])->all());

        $values = [];
        foreach ($fields as $key => $field) {
            $value = $request->input($key);

            switch ($field['type']) {
                case 'image':
                    $values[$key] = $this->media($request, $key, $this->settings->get($key), 'settings');
                    break;
                case 'boolean':
                    $values[$key] = $request->boolean($key) ? '1' : '0';
                    break;
                case 'password':
                    if (filled($value)) {
                        $values[$key] = Crypt::encryptString($value);
                    }
                    break;
                default:
                    $values[$key] = match (true) {
                        $key === 'google_maps_embed' => MapEmbed::extract($value) ?? '',
                        isset($field['meta']) => VerificationTags::code($value) ?? '',
                        (bool) ($field['meta_list'] ?? false) => VerificationTags::normalise($value),
                        default => (string) ($value ?? ''),
                    };
            }
        }

        $this->settings->update($group, $values);
        $logger->log('updated', 'Updated settings: '.$this->settings->groups()[$group]['label']);

        return redirect()->route('admin.settings.edit', $group)->with('success', $this->settings->groups()[$group]['label'].' settings saved. The website is updated.');
    }

    public function testEmail(Request $request, MailSettings $mail): RedirectResponse
    {
        $to = $this->settings->get('notification_email') ?: $request->user()->email;

        try {
            $mail->apply();
            Mail::raw('This is a test email from the '.setting('site_name').' admin panel. Your email settings are working.', function ($message) use ($to) {
                $message->to($to)->subject('Test email — '.setting('site_name'));
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'The test email could not be sent: '.str($e->getMessage())->limit(200));
        }

        return back()->with('success', "Test email sent to {$to}. (With MAIL_MAILER=log it is written to storage/logs instead.)");
    }

    /**
     * Cleaned-up input before validation: IDs pulled out of pasted code for fields with
     * an "extract" pattern (e.g. G-XXXXXXXXXX from the whole Google tag snippet), and
     * "slug" fields reduced to a bare path ("/Leads/" → "leads").
     *
     * @param  Collection<string, array<string, mixed>>  $fields
     * @return array<string, string>
     */
    protected function extracted(Request $request, Collection $fields): array
    {
        $values = [];

        foreach ($fields as $key => $field) {
            if (isset($field['extract']) && preg_match($field['extract'], (string) $request->input($key), $match)) {
                $values[$key] = strtoupper($match[1]);
            } elseif (($field['slug'] ?? false) && $request->has($key)) {
                $values[$key] = RedirectLink::normalise($request->input($key));
            }
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<int, mixed>
     */
    protected function rules(string $key, array $field): array
    {
        $extra = $field['rules'] ?? [];

        $rules = match ($field['type']) {
            'boolean' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:150'],
            'url' => ['nullable', 'url', 'max:1000'],
            'number' => ['nullable', 'numeric'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'select' => ['nullable', Rule::in(array_keys($field['options'] ?? []))],
            'image' => ($field['mimes'] ?? null) === 'png,ico' ? UploadRules::favicon() : UploadRules::image(),
            'code' => ['nullable', 'string', 'max:50000'],
            'textarea' => ['nullable', 'string', 'max:5000'],
            'password' => ['nullable', 'string', 'max:255'],
            default => ['nullable', 'string', 'max:255'],
        };

        if ($key === 'google_maps_embed') {
            $rules[] = function (string $attribute, mixed $value, Closure $fail) {
                if (filled($value) && ! MapEmbed::extract($value)) {
                    $fail('Paste the Google Maps “Embed a map” iframe code or its https://www.google.com/maps… link.');
                }
            };
        }

        if (isset($field['meta'])) {
            $rules[] = function (string $attribute, mixed $value, Closure $fail) use ($field) {
                if (filled($value) && ! VerificationTags::code($value)) {
                    $fail("Paste the {$field['label']} meta tag, or only the code inside content=\"…\".");
                }
            };
        }

        if ($field['slug'] ?? false) {
            $rules = ['required', 'string', 'max:60', 'regex:'.RedirectLink::PATTERN, function (string $attribute, mixed $value, Closure $fail) {
                if (is_string($value) && RedirectLink::isTaken($value)) {
                    $fail("“{$value}” is already a page of the website. Choose another name, e.g. join, channel or leads.");
                }
            }];
        }

        if ($field['meta_list'] ?? false) {
            $rules[] = function (string $attribute, mixed $value, Closure $fail) {
                if (filled($value) && VerificationTags::normalise($value) === '') {
                    $fail('No usable <meta name="…" content="…"> tag was found. Scripts, links and http-equiv tags are not accepted here.');
                }
            };
        }

        return array_merge($rules, $extra);
    }
}
