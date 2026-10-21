<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Database\Factories\LeadFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * One field of the "Add Lead" form (Sales & CRM → Lead Form Fields). Built-in fields are
 * columns on the leads table; fields the admin adds are stored in the lead's "extra" JSON.
 */
#[Fillable(['key', 'label', 'type', 'placeholder', 'help', 'options', 'is_system', 'is_required', 'show_in_list', 'is_active', 'sort_order'])]
class LeadField extends Model
{
    /** @use HasFactory<LeadFieldFactory> */
    use HasFactory, HasVisibilityAndOrder, LogsActivity;

    /**
     * Field types the admin can choose for a new field.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'text' => 'Short text',
        'textarea' => 'Long text (paragraph)',
        'phone' => 'Phone number',
        'number' => 'Number',
        'email' => 'Email',
        'date' => 'Date',
        'select' => 'Dropdown (choose one)',
    ];

    /**
     * Built-in fields that every lead needs; they cannot be hidden or made optional.
     *
     * @var array<int, string>
     */
    public const LOCKED = ['name', 'phone'];

    /**
     * Names a new field's key may not take: the columns of the leads table.
     *
     * @var array<int, string>
     */
    public const RESERVED = [
        'id', 'plot_id', 'name', 'phone', 'email', 'budget', 'payment_preference', 'preferred_contact_method', 'preferred_time',
        'message', 'extra', 'source', 'status', 'assigned_to', 'created_by', 'follow_up_date', 'last_contacted_at', 'page_url',
        'ip_address', 'user_agent', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected static function booted(): void
    {
        // Name and phone stay on the form and required, however a field is switched.
        static::saving(function (self $field) {
            if ($field->isLocked()) {
                $field->is_active = true;
                $field->is_required = true;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_system' => 'boolean',
            'is_required' => 'boolean',
            'show_in_list' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Active fields in form order.
     *
     * @return Collection<int, self>
     */
    public static function form(): Collection
    {
        return self::query()->where(fn ($query) => $query->where('is_active', true)->orWhereIn('key', self::LOCKED))->ordered()->get();
    }

    /**
     * A key for a new field, made from its label: "Came from?" → came_from.
     */
    public static function keyFor(string $label): string
    {
        $base = Str::limit(Str::slug($label, '_'), 50, '') ?: 'field';
        $base = in_array($base, self::RESERVED, true) ? $base.'_field' : $base;
        $key = $base;

        for ($i = 2; self::query()->where('key', $key)->exists(); $i++) {
            $key = "{$base}_{$i}";
        }

        return $key;
    }

    public function isLocked(): bool
    {
        return $this->is_system && in_array($this->key, self::LOCKED, true);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? Str::headline($this->type);
    }

    /**
     * Choices for a dropdown: the admin's options, or for built-in dropdowns their fixed lists.
     *
     * @return array<string, string>
     */
    public function choices(): array
    {
        return match ($this->key) {
            'plot_id' => Plot::query()->ordered()->pluck('size_label', 'id')->all(),
            'payment_preference' => Lead::PAYMENT_PREFERENCES,
            'preferred_contact_method' => Lead::CONTACT_METHODS,
            default => collect($this->options ?? [])->mapWithKeys(fn (string $option) => [$option => $option])->all(),
        };
    }

    /**
     * The form input name: the column for built-in fields, extra[key] for the admin's own.
     */
    public function inputName(): string
    {
        return $this->is_system ? $this->key : "extra[{$this->key}]";
    }

    /**
     * Validation key matching inputName().
     */
    public function ruleKey(): string
    {
        return $this->is_system ? $this->key : "extra.{$this->key}";
    }

    /**
     * @return array<int, mixed>
     */
    public function rules(): array
    {
        $type = match ($this->key) {
            'plot_id' => ['integer', Rule::exists('plots', 'id')],
            'name' => ['string', 'max:150'],
            'budget' => ['string', 'max:100'],
            'preferred_time' => ['string', 'max:60'],
            'payment_preference', 'preferred_contact_method' => [Rule::in(array_keys($this->choices()))],
            default => match ($this->type) {
                'textarea' => ['string', 'max:5000'],
                'number' => ['numeric'],
                'email' => ['email', 'max:150'],
                'date' => ['date'],
                'phone' => ['string', 'max:30'],
                'select' => ['string', Rule::in(array_keys($this->choices()))],
                default => ['string', 'max:255'],
            },
        };

        return [$this->is_required || $this->isLocked() ? 'required' : 'nullable', ...$type];
    }

    /**
     * The value shown for a lead (labels for dropdowns, formatted dates).
     */
    public function display(Lead $lead): ?string
    {
        $value = match ($this->key) {
            'plot_id' => $lead->plot?->size_label,
            'payment_preference' => $lead->paymentPreferenceLabel(),
            'preferred_contact_method' => $lead->contactMethodLabel(),
            default => $this->is_system ? $lead->getAttribute($this->key) : ($lead->extra[$this->key] ?? null),
        };

        if ($this->type === 'date' && ! $this->is_system && filled($value)) {
            return rescue(fn () => Carbon::parse($value)->format('d M Y'), (string) $value, false);
        }

        return filled($value) ? (string) $value : null;
    }

    /**
     * The value to put in the form for a lead.
     */
    public function value(Lead $lead): mixed
    {
        return $this->is_system ? $lead->getAttribute($this->key) : ($lead->extra[$this->key] ?? null);
    }
}
