<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UploadFileController extends Controller
{
    /**
     * Display the CSV management page.
     */
    public function index()
    {
        $exists = Storage::disk('local')->exists('seed.csv');

        return Inertia::render('Dashboard/UploadFile/Page', [
            'fileExists' => $exists,
            'fileUrl' => $exists ? route('csv.download') : null,
        ]);
    }

    /**
     * Handle CSV upload.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        // Store the uploaded file as seed.csv
        $file = $request->file('file');
        $file->storeAs('/', 'seed.csv', 'local');

        return redirect()->route('csv.index')->with('success', 'CSV uploaded successfully.');
    }

    /**
     * Handle CSV download.
     */
    public function download()
    {
        if (! Storage::disk('local')->exists('seed.csv')) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download('seed.csv');
    }

    /**
     * Handle CSV deletion.
     */
    public function delete()
    {
        if (Storage::disk('local')->exists('seed.csv')) {
            Storage::disk('local')->delete('seed.csv');
        }

        return redirect()->route('csv.index')->with('success', 'CSV deleted successfully.');
    }
}
