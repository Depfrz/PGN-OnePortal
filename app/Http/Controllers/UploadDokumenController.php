<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadDokumenController extends Controller
{
    public function index()
    {
        return view('upload-dokumen.index');
    }

    public function store(Request $request)
    {
        return redirect()->back();
    }
}
