<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Admin-editable website settings, backed by the website_settings table
 * with defaults from config/cms.php. Values are cached until the next save.
 */
class SettingsService
{
    public const CACHE_KEY = 'cms.website_settings';

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $values = null;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        try {
            $stored = Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            $stored = [];
        }

        return $this->values = array_merge($this->defaults(), array_filter($stored, fn ($value) => $value !== null));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Decrypted value of a "password" type setting.
     */
    public function secret(string $key): ?string
    {
        $value = $this->get($key);

        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function groups(): array
    {
        return config('cms.settings', []);
    }

    /**
     * Fields shown on a group's screen: its own fields plus any it "includes"
     * from other groups (e.g. the footer screen also edits the footer logo and colours).
     *
     * @return array<string, array<string, mixed>>
     */
    public function fields(string $group): array
    {
        $definition = $this->groups()[$group] ?? [];
        $fields = $definition['fields'] ?? [];

        foreach ($definition['include'] ?? [] as $key) {
            if ($owner = $this->ownerOf($key)) {
                $fields[$key] = $this->groups()[$owner]['fields'][$key];
            }
        }

        return $fields;
    }

    /**
     * The group that defines a setting key.
     */
    public function ownerOf(string $key): ?string
    {
        foreach ($this->groups() as $group => $definition) {
            if (isset($definition['fields'][$key])) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Save values submitted from one settings screen.
     *
     * @param  array<string, mixed>  $values
     */
    public function update(string $group, array $values): void
    {
        $fields = $this->fields($group);

        foreach ($values as $key => $value) {
            if (! isset($fields[$key])) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => $this->ownerOf($key) ?? $group, 'type' => $fields[$key]['type'], 'value' => is_bool($value) ? ($value ? '1' : '0') : $value],
            );
        }

        $this->flush();
    }

    /**
     * Write config defaults for any setting that has never been saved.
     */
    public function seedDefaults(): void
    {
        foreach ($this->groups() as $group => $definition) {
            foreach ($definition['fields'] as $key => $field) {
                Setting::query()->firstOrCreate(
                    ['key' => $key],
                    ['group' => $group, 'type' => $field['type'], 'value' => $field['default'] ?? null],
                );
            }
        }

        $this->flush();
    }

    public function flush(): void
    {
        $this->values = null;
        Cache::forget(self::CACHE_KEY);
        Cache::forget(ThemeService::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        $defaults = [];

        foreach ($this->groups() as $definition) {
            foreach ($definition['fields'] as $key => $field) {
                $defaults[$key] = $field['default'] ?? null;
            }
        }

        return $defaults;
    }
}
