@extends('admin.layouts.app')

@section('pageTitle', 'Chat Session Detail')

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('admin.chatlogs') }}" class="text-xs text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Chat Logs</a>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-primary-700 dark:text-primary-300 mb-1">Session: <span class="font-mono">{{ $session->session_id }}</span></h2>
            <div class="text-xs text-gray-500 mb-2">Started: {{ $session->created_at->format('M d, Y H:i') }}</div>
            <div class="flex flex-wrap gap-4 text-xs text-gray-400 mb-2">
                @if($session->user_id)
                    <span>User ID: {{ $session->user_id }}</span>
                @endif
                @if($session->current_document_id)
                    <span>Current Document ID: {{ $session->current_document_id }}</span>
                @endif
                <span>Total Messages: {{ $session->chats->count() }}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-3 py-2">Role</th>
                        <th class="px-3 py-2">Message</th>
                        <th class="px-3 py-2">Time</th>
                        <th class="px-3 py-2">Document</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($session->chats as $chat)
                        <tr>
                            <td class="px-3 py-2 font-semibold {{ $chat->role === 'user' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300' }}">
                                <i data-lucide="{{ $chat->role === 'user' ? 'user' : 'bot' }}" class="inline w-4 h-4 mr-1"></i>
                                {{ ucfirst($chat->role) }}
                            </td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $chat->message }}</td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ $chat->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-3 py-2 text-xs text-gray-400">
                                @if($chat->document)
                                    {{ $chat->document->original_name }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
