<textarea {{ $attributes->merge([
    'class' => 'block w-full rounded-md border-brand-border shadow-sm focus:border-brand-primary focus:ring-brand-primary min-h-[120px]'
]) }}>{{ $slot }}</textarea>