<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $adminId = auth('admin')->id();

        $totalDocs = \App\Models\Document::where('uploaded_by', $adminId)->count();
        $totalEmbeddings = \App\Models\Document::where('uploaded_by', $adminId)->withCount('embeddings')->get()->sum('embeddings_count');

        $lastUploadDoc = \App\Models\Document::where('uploaded_by', $adminId)
            ->orderBy('created_at', 'desc')
            ->first();

        $lastUpload = $lastUploadDoc ? $lastUploadDoc->created_at->diffForHumans() : 'None';

        // Analytics data
        $analytics = $this->getAnalyticsData();

        return view('admin.dashboard', compact('totalDocs', 'totalEmbeddings', 'lastUpload', 'analytics'));
    }

    /**
     * Get analytics data for dashboard charts
     */
    private function getAnalyticsData()
    {
        // Total API calls by provider
        $apiUsageByProvider = \App\Models\Chat::where('role', 'bot')
            ->selectRaw('api_provider, COUNT(*) as count, SUM(api_tokens_used) as total_tokens, SUM(api_cost) as total_cost')
            ->groupBy('api_provider')
            ->get();

        // API usage over the last 7 days
        $last7Days = \App\Models\Chat::where('role', 'bot')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, api_provider, COUNT(*) as count, SUM(api_tokens_used) as tokens')
            ->groupBy('date', 'api_provider')
            ->orderBy('date')
            ->get();

        // Top documents by chat interactions
        $topDocuments = \App\Models\Document::withCount('chats')
            ->orderBy('chats_count', 'desc')
            ->take(5)
            ->get(['id', 'file_name', 'chats_count']);

        // Average response time
        $avgResponseTime = \App\Models\Chat::where('role', 'bot')
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');

        // Total chats count
        $totalChats = \App\Models\Chat::where('role', 'user')->count();

        // Recent activity (last 30 days)
        $recentActivity = \App\Models\Chat::where('role', 'user')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'apiUsageByProvider' => $apiUsageByProvider,
            'last7Days' => $last7Days,
            'topDocuments' => $topDocuments,
            'avgResponseTime' => round($avgResponseTime ?? 0),
            'totalChats' => $totalChats,
            'recentActivity' => $recentActivity,
        ];
    }

    public function documents()
    {
        return view('admin.documents');
    }

    public function upload()
    {
        return view('admin.upload');
    }

    public function chatlogs()
    {
        $sessions = \App\Models\ChatSession::with(['chats' => function($q) {
            $q->orderBy('created_at');
        }, 'chats.document'])->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.chatlogs', compact('sessions'));
    }

        public function chatlogDetail($sessionId)
    {
        $session = \App\Models\ChatSession::with(['chats' => function($q) {
            $q->orderBy('created_at');
        }, 'chats.document'])->findOrFail($sessionId);
        return view('admin.chatlog-detail', compact('session'));
    }

        // AJAX endpoint for latest chat sessions (for notification dropdown)
    public function latestChatSessions()
    {
        $sessions = \App\Models\ChatSession::orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'session_id', 'created_at']);
        return response()->json($sessions);
    }
}