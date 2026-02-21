<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|max:2048'
        ]);

        $path = $request->file('upload')->store('ckeditor', 'public');

        return response()->json([
            'url' => asset('storage/' . $path)
        ]);
    }
}