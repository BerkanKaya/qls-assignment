@props([
'label' => null,
'name',
'id' => null,
])

@php
$computedId = $id ?? trim(preg_replace('/[^a-zA-Z0-9\-_]+/', '_', $name), '_');
$errorKey = preg_replace('/\[(.*?)\]/', '.$1', $name);
$errorKey = trim(preg_replace('/\.+/', '.', $errorKey), '.');

$base = 'block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20';
$error = 'border-red-300 focus:border-red-500 focus:ring-red-500/20';

$classes = ($label ? 'mt-1 ' : '') . $base . ($errors->has($errorKey) ? ' ' . $error : '');
@endphp

<div>
    @if($label)
    <label for="{{ $computedId }}" class="block text-sm font-medium">{{ $label }}</label>
    @endif

    <select
        id="{{ $computedId }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </select>

    @error($errorKey)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>