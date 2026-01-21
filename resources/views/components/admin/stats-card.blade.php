@props(['icon', 'color' => 'blue', 'title', 'value', 'subtitle', 'badge' => null])

<div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
        <div class="p-2 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/30 rounded-lg text-{{ $color }}-600 dark:text-{{ $color }}-400">
            <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
        </div>
        @if($badge)
            <span class="text-xs font-medium text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">{{ $badge }}</span>
        @endif
    </div>
    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $value }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
</div>