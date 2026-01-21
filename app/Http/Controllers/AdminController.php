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

        return view('admin.dashboard', compact('totalDocs', 'totalEmbeddings', 'lastUpload'));
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
}