@props(['type' => 'button'])

<button {{ $attributes->merge(['type' => $type, 'class' => 'btn btn-outline']) }}>
    {{ $slot }}
</button>