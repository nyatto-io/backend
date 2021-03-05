<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __invoke(Request $request, File $file)
    {
        if (!Storage::exists($file->path)) {
            return response('', 404);
        }
        $headers = [
            'Content-Type' => $file->type,
            'Content-Length' => $file->size,
        ];

        $binary = Storage::get($file->path);

        if ($request->input('download', false)) {
            return response()->download($binary, $file->name, $headers);
        }

        return response($binary, 200, $headers);
    }
}
