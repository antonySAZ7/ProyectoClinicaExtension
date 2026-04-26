<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center px-4 py-2 bg-brand-primary text-brand-contrast rounded-md text-base font-semibold hover:bg-brand-primary-strong focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 transition'
]) }}>
    {{ $slot }}
</button>