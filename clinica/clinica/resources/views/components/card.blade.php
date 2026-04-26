<div {{ $attributes->merge([
    'class' => 'bg-brand-contrast border border-brand-border rounded-xl shadow-sm p-6'
]) }}>
    {{ $slot }}
</div>