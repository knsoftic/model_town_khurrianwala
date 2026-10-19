@props(['edit' => null, 'view' => null, 'delete' => null, 'deleteLabel' => 'this item'])

<div {{ $attributes->class(['flex items-center justify-end gap-1']) }}>
    {{ $slot }}
    @if ($view)
        <a href="{{ $view }}" class="a-icon-btn" title="View"><x-icon name="eye" class="size-4" /></a>
    @endif
    @if ($edit)
        <a href="{{ $edit }}" class="a-icon-btn" title="Edit"><x-icon name="pencil" class="size-4" /></a>
    @endif
    @if ($delete)
        <button type="button" class="a-icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="Delete"
                @click="$store.confirm.ask({ action: @js($delete), title: @js('Delete '.$deleteLabel.'?') })">
            <x-icon name="trash" class="size-4" />
        </button>
    @endif
</div>
