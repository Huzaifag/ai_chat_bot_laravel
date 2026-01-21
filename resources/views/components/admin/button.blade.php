@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'onclick' => null, 'type' => 'button', 'disabled' => false])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variantClasses = [
        'primary' => 'text-white bg-primary-600 hover:bg-primary-700 focus:ring-primary-500',
        'secondary' => 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-primary-500 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700',
        'danger' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
    ];

    $sizeClasses = [
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $classes = $baseClasses . ' ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size];
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classes }}" @if($onclick) onclick="{{ $onclick }}" @endif>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }}" @if($onclick) onclick="{{ $onclick }}" @endif @if($disabled) disabled @endif id="{{ $attributes->get('id') }}">
        {{ $slot }}
    </button>
@endif