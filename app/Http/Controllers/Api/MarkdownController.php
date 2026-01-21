<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class MarkdownController extends Controller
{
    public function render(Request $request)
    {
        $text = $request->input('text', '');
        $html = Str::markdown($text);
        return response()->json(['html' => $html]);
    }
}
