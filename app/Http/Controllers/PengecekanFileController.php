<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengecekanFileController extends Controller
{
    public function index()
    {
        return view('pengecekan-file.index');
    }

    public function search(Request $request)
    {
        return response()->json([]);
    }
}
