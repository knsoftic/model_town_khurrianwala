{{-- Fields for adding or editing one menu link. Expects $item (NavigationItem), $routes, $parents, $location. --}}
<div class="grid gap-3 sm:grid-cols-2" x-data="{ type: @js($item->route_name || ! $item->exists ? 'page' : 'url') }">
    <div>
        <label class="a-label">Label</label>
        <input name="label" value="{{ $item->label }}" required class="a-input">
    </div>
    <div>
        <label class="a-label">Link to</label>
        <div class="flex gap-1 rounded-lg bg-slate-100 p-1 text-xs font-semibold">
            <label class="flex-1 cursor-pointer rounded-md px-2 py-1.5 text-center" :class="type === 'page' ? 'bg-white shadow-sm' : 'text-slate-500'"><input type="radio" name="link_type" value="page" x-model="type" class="sr-only"> Website page</label>
            <label class="flex-1 cursor-pointer rounded-md px-2 py-1.5 text-center" :class="type === 'url' ? 'bg-white shadow-sm' : 'text-slate-500'"><input type="radio" name="link_type" value="url" x-model="type" class="sr-only"> Custom link</label>
        </div>
    </div>
    <div x-show="type === 'page'">
        <label class="a-label">Page</label>
        <select name="route_name" class="a-input">
            @foreach ($routes as $route => $label)
                <option value="{{ $route }}" @selected($item->route_name === $route)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div x-show="type === 'url'" x-cloak>
        <label class="a-label">URL</label>
        <input name="url" value="{{ $item->url }}" placeholder="/plots/5-marla or https://…" class="a-input">
    </div>
    <div>
        <label class="a-label">Open in</label>
        <select name="target" class="a-input">
            <option value="_self" @selected($item->target !== '_blank')>Same tab</option>
            <option value="_blank" @selected($item->target === '_blank')>New tab</option>
        </select>
    </div>
    @if ($location === 'header')
        <div>
            <label class="a-label">Show under (dropdown)</label>
            <select name="parent_id" class="a-input">
                <option value="">— Top level —</option>
                @foreach ($parents as $parent)
                    @continue($parent->id === $item->id)
                    <option value="{{ $parent->id }}" @selected($item->parent_id === $parent->id)>{{ $parent->label }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="flex items-end pb-2">
        <x-admin.toggle name="is_active" label="Visible" :checked="$item->exists ? $item->is_active : true" />
    </div>
</div>
