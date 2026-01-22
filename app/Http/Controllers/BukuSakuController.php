<?php

namespace App\Http\Controllers;

use App\Models\BukuSakuDocument;
use App\Models\BukuSakuTag;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class BukuSakuController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $hasSearch = !empty($query);
        $documents = collect();

        $resultsNotFound = false;

        if ($hasSearch) {
            // Fetch all approved documents for NLP processing
            $allDocs = BukuSakuDocument::approved()->with('user')->get()->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'description' => $doc->description,
                    'tags' => $doc->tags,
                    'valid_until' => $doc->valid_until,
                ];
            });

            // Prepare Python Execution
            $pythonPath = base_path('python_engine/search_engine.py');
            // Escape arguments to prevent command injection
            // On Windows, json_encode might produce double quotes which need proper escaping for shell
            // We'll write the JSON to a temporary file to be safe and avoid length limits
            
            $tempFile = tempnam(sys_get_temp_dir(), 'buku_saku_search_');
            file_put_contents($tempFile, json_encode($allDocs));
            
            $escapedQuery = escapeshellarg($query);
            
            // Assuming 'python' is in PATH. Use full path if necessary.
            $command = "python \"{$pythonPath}\" \"{$tempFile}\" {$escapedQuery}";
            
            $output = shell_exec($command);
            
            // Clean up temp file
            unlink($tempFile);
            
            $results = json_decode($output, true);
            
            if (is_array($results) && !empty($results)) {
                $ids = array_column($results, 'id');
                if (!empty($ids)) {
                    $documents = BukuSakuDocument::with('user')
                        ->whereIn('id', $ids)
                        ->get()
                        ->sortBy(function($model) use ($ids) {
                            return array_search($model->id, $ids);
                        });
                }
            }
            // If search yields no results, $documents remains empty collection
        } else {
            // Default view: Show latest approved documents
            $documents = BukuSakuDocument::approved()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('buku-saku.index', compact('documents', 'hasSearch', 'query', 'resultsNotFound'));
    }

    public function upload()
    {
        $availableTags = BukuSakuTag::all();
        return view('buku-saku.upload', compact('availableTags'));
    }

    public function hapusDokumenIndex(Request $request)
    {
        $query = $request->input('q');
        
        $documents = BukuSakuDocument::where('user_id', Auth::id())
            ->when($query, function ($q) use ($query) {
                return $q->where('title', 'like', "%{$query}%")
                         ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('buku-saku.hapus-dokumen', compact('documents', 'query'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|array', // Now an array from checklist
            'tags.*' => 'string',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:15360', // Max 15MB
            'valid_until' => 'nullable|date',
        ]);

        $file = $request->file('file');
        $path = $file->store('buku-saku', 'public');
        $size = $file->getSize();
        $type = $file->getClientOriginalExtension();

        // Convert size to human readable
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $sizeFormatted = number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];

        // Process tags array to string (comma separated)
        $tags = $request->tags ? implode(',', $request->tags) : null;

        BukuSakuDocument::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'tags' => $tags,
            'file_path' => $path,
            'file_type' => $type,
            'file_size' => $sizeFormatted,
            'status' => 'approved', // Auto-approved
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'valid_until' => $request->valid_until,
        ]);

        // Notify All Users about new document (since it's auto-approved)
        $otherUsers = User::where('id', '!=', Auth::id())->get();
        Notification::send($otherUsers, new SystemNotification(
            'new_document',
            'Buku Saku',
            'Dokumen baru tersedia: "' . $request->title . '"',
            Auth::user()->name
        ));

        return redirect()->route('buku-saku.index')->with('success', 'Dokumen berhasil diunggah.');
    }

    public function edit($id)
    {
        $document = BukuSakuDocument::findOrFail($id);
        
        // Check permission (Owner or Admin/Supervisor)
        if ($document->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['Admin', 'Supervisor'])) {
             return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        $availableTags = BukuSakuTag::all();
        return view('buku-saku.edit', compact('document', 'availableTags'));
    }

    public function update(Request $request, $id)
    {
        $document = BukuSakuDocument::findOrFail($id);

        // Check permission
        if ($document->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['Admin', 'Supervisor'])) {
             return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:15360',
            'valid_until' => 'nullable|date',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'valid_until' => $request->valid_until,
        ];

        // Update tags
        if ($request->has('tags')) {
             $data['tags'] = implode(',', $request->tags);
        } else {
             // If tags field is present but empty (user unchecked all), clear tags. 
             // Note: In HTML forms, unchecked checkboxes are not sent. 
             // We should handle this carefully. If 'tags' input is not in request but form was submitted, it implies empty.
             // But for now, let's assume if it's in request, we update it.
             // A common trick is to add a hidden input for tags to ensure it's sent.
             // We'll rely on the array validation.
             $data['tags'] = null;
        }

        // Update file if provided
        if ($request->hasFile('file')) {
            // Delete old file
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $path = $file->store('buku-saku', 'public');
            $size = $file->getSize();
            $type = $file->getClientOriginalExtension();

            $units = ['B', 'KB', 'MB', 'GB'];
            $power = $size > 0 ? floor(log($size, 1024)) : 0;
            $sizeFormatted = number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];

            $data['file_path'] = $path;
            $data['file_type'] = $type;
            $data['file_size'] = $sizeFormatted;
        }

        $document->update($data);

        return redirect()->route('buku-saku.index')->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(BukuSakuDocument $document)
    {
        // Allow owner or Admin/Supervisor to delete
        if ($document->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['Admin', 'Supervisor'])) {
             return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus dokumen ini.');
        }
        
        // Permanent delete as per "hapus saja" request usually implies removal, 
        // but let's stick to soft delete via status 'deleted' or actually delete?
        // "hapus sistem acc atau reject hanya ada hapus"
        // If I delete it, it's gone from History too unless I soft delete.
        // Let's use delete() if SoftDeletes trait is used, or update status.
        // Model doesn't have SoftDeletes.
        // I'll stick to updating status to 'deleted' to preserve history record if needed, 
        // OR actually delete if user wants it gone.
        // Let's actually delete the file and record.
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function approvalIndex()
    {
        // Now just a list of all documents (Management view)
        // Or "Kelola Dokumen"
        $documents = BukuSakuDocument::with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('buku-saku.approval', compact('documents'));
    }

    // approve/reject removed/deprecated

    public function toggleFavorite($id)
    {
        $document = BukuSakuDocument::findOrFail($id);
        $user = Auth::user();

        if ($user->favoriteDocuments()->where('buku_saku_document_id', $id)->exists()) {
            $user->favoriteDocuments()->detach($id);
            $message = 'Dokumen dihapus dari favorit.';
        } else {
            $user->favoriteDocuments()->attach($id);
            $message = 'Dokumen ditambahkan ke favorit.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function favorites()
    {
        $documents = Auth::user()->favoriteDocuments()->with('user')->orderBy('created_at', 'desc')->get();
        return view('buku-saku.favorites', compact('documents'));
    }

    public function history()
    {
        // "Riwayat Dokumen" - All documents (uploaded by everyone or just me?)
        // Usually history implies logs. But here it might mean "All Documents List".
        // Let's show all documents for now, or maybe just "My Uploads".
        // Context: "riwayat dokumen itu juga perlu bisa geser kiri kanan" -> Table view.
        // I'll show all documents here.
        $documents = BukuSakuDocument::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('buku-saku.history', compact('documents'));
    }
    
    public function show(BukuSakuDocument $document)
    {
        // Ensure the file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
             // We can still show the page but maybe with a warning
        }
        return view('buku-saku.show', compact('document'));
    }

    public function download(BukuSakuDocument $document)
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            $path = Storage::disk('public')->path($document->file_path);
            return response()->download($path, $document->title . '.' . $document->file_type);
        }
        return redirect()->back()->with('error', 'File tidak ditemukan.');
    }

    public function preview(BukuSakuDocument $document)
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            $path = Storage::disk('public')->path($document->file_path);
            return response()->file($path);
        }
        return redirect()->back()->with('error', 'File tidak ditemukan.');
    }

    public function storeTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:buku_saku_tags,name|max:50',
        ]);

        $tag = BukuSakuTag::create(['name' => $request->name]);

        if ($request->wantsJson()) {
            return response()->json($tag);
        }

        return redirect()->back()->with('success', 'Tag berhasil ditambahkan.');
    }

    public function destroyTag($id)
    {
        // Only Admin or Supervisor should probably delete tags, but let's allow any user for now based on request "bisa tambah dan hapus tag sendiri"
        // Or maybe restrict to creator? But tags are global. 
        // Let's assume open for now or restrict to Admin/Supervisor if "sendiri" means "I can do it myself" as an admin.
        // Usually global tags are managed by admins. But if user says "sendiri", they might mean "I want to be able to do it".
        
        $tag = BukuSakuTag::findOrFail($id);
        $tag->delete();

        return redirect()->back()->with('success', 'Tag berhasil dihapus.');
    }
}
