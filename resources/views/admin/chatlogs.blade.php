@extends('admin.layouts.app')

@section('pageTitle', 'Chat Logs')

@section('content')
    <div class="max-w-5xl mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Chat Logs</h1>
        @if($sessions->count() === 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="message-square-dashed" class="w-10 h-10 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">No Chat Logs Found</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-2">No chat sessions have been started yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3">Session ID</th>
                            <th class="px-4 py-3">Document Name</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($sessions as $session)
                            @php
                                $firstDocChat = $session->chats->first(function($chat) { return $chat->document; });
                                $doc = $firstDocChat ? $firstDocChat->document : null;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-mono text-primary-700 dark:text-primary-300">{{ $session->session_id }}</td>
                                <td class="px-4 py-3">
                                    @if($doc)
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $doc->original_name }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    @if($doc)
                                        {{ $doc->created_at ? $doc->created_at->format('M d, Y H:i') : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.chatlogs.show', $session->id) }}" class="text-blue-600 hover:underline">View Details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-8">{{ $sessions->links() }}</div>
            <div class="mt-8">{{ $sessions->links() }}</div>
        @endif
    </div>
@endsection