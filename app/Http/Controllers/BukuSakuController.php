<?php

namespace App\Http\Controllers;

use App\Models\BukuSakuDocument;
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
                    'categories' => is_array($doc->categories) ? implode(' ', $doc->categories) : $doc->categories,
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
                ->orderBy('approved_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('buku-saku.index', compact('documents', 'hasSearch', 'query', 'resultsNotFound'));
    }

    public function upload()
    {
        return view('buku-saku.upload');
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
            'tags' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'string',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $path = $file->store('buku-saku', 'public');
        $size = $file->getSize();
        $type = $file->getClientOriginalExtension();

        // Convert size to human readable
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $sizeFormatted = number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];

        BukuSakuDocument::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'tags' => $request->tags,
            'categories' => $request->categories,
            'file_path' => $path,
            'file_type' => $type,
            'file_size' => $sizeFormatted,
            'status' => 'pending', // Default status
        ]);

        // Notify Admins, Supervisors, and users with 'Pengecekan File' access
        $approvers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Admin', 'Supervisor']);
        })->orWhereHas('moduleAccesses', function($q) {
            $q->whereHas('module', function($m) {
                $m->where('name', 'Pengecekan File');
            })->where('can_read', true);
        })->get();

        Notification::send($approvers, new SystemNotification(
            'upload',
            'Buku Saku',
            'Dokumen baru "' . $request->title . '" menunggu persetujuan.',
            Auth::user()->name
        ));

        return redirect()->route('buku-saku.approval')->with('success', 'Dokumen berhasil diunggah dan menunggu persetujuan.');
    }

    public function destroy(BukuSakuDocument $document)
    {
        /** @var User $user */
        $user = Auth::user();

        // Allow owner or Admin/Supervisor to delete
        if ($document->user_id !== $user->id && !$user->hasAnyRole(['Admin', 'Supervisor'])) {
             return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus dokumen ini.');
        }

        // Instead of hard delete, let's update status to 'deleted' to show in history?
        // But if I hard delete, it's gone.
        // The image shows "Terhapus".
        // Let's update status to 'deleted' for now to match the "History" requirement.
        
        $document->update(['status' => 'deleted']);

        // Notify Admins if user deleted their own document
        // Or Notify Owner if Admin deleted it
        if ($document->user_id !== Auth::id()) {
            $document->user->notify(new SystemNotification(
                'delete',
                'Buku Saku',
                'Dokumen "' . $document->title . '" telah dihapus oleh admin.',
                Auth::user()->name
            ));
        } else {
             // User deleted their own document, notify admins and checkers
            $admins = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['Admin', 'Supervisor']);
            })->orWhereHas('moduleAccesses', function($q) {
                $q->whereHas('module', function($m) {
                    $m->where('name', 'Pengecekan File');
                })->where('can_read', true);
            })->get();
            
            Notification::send($admins, new SystemNotification(
                'delete',
                'Buku Saku',
                'Dokumen "' . $document->title . '" telah dihapus oleh pemiliknya.',
                Auth::user()->name
            ));
        }

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function approvalIndex()
    {
        // Show all documents for approval/history
        // In a real app, this should be restricted to admins/approvers
        $documents = BukuSakuDocument::with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('buku-saku.approval', compact('documents'));
    }

    public function approve($id)
    {
        $document = BukuSakuDocument::findOrFail($id);
        $document->update([
            'status' => 'approved',
            'rejected_reason' => null,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Notify Uploader
        $document->user->notify(new SystemNotification(
            'approve',
            'Buku Saku',
            'Dokumen Anda "' . $document->title . '" telah disetujui.',
            Auth::user()->name
        ));

        // Notify All Users about new document
        // Exclude the uploader to avoid duplicate notification (one for approval, one for new doc)
        // Or maybe just notify everyone. Let's exclude uploader from the "New Document" blast if they got "Approved".
        $otherUsers = User::where('id', '!=', $document->user_id)->get();
        Notification::send($otherUsers, new SystemNotification(
            'new_document',
            'Buku Saku',
            'Dokumen baru tersedia: "' . $document->title . '"',
            $document->user->name // Actor is the uploader? or the approver? Usually "New document available" implies the content creator.
        ));
        
        return redirect()->back()->with('success', 'Dokumen disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $document = BukuSakuDocument::findOrFail($id);
        $document->update([
            'status' => 'rejected',
            'rejected_reason' => $request->input('reason', 'Ditolak oleh admin.')
        ]);

        // Notify Uploader
        $document->user->notify(new SystemNotification(
            'reject',
            'Buku Saku',
            'Dokumen Anda "' . $document->title . '" telah ditolak. Alasan: ' . $document->rejected_reason,
            Auth::user()->name
        ));
        
        return redirect()->back()->with('success', 'Dokumen ditolak.');
    }

    public function toggleFavorite($id)
    {
        $document = BukuSakuDocument::findOrFail($id);
        /** @var User $user */
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
        /** @var User $user */
        $user = Auth::user();
        $documents = $user->favoriteDocuments()->with('user')->orderBy('created_at', 'desc')->get();
        return view('buku-saku.favorites', compact('documents'));
    }

    public function history()
    {
        // For now, let's assume history means "My Uploads" or "All Activity"
        // User said "side barnya terdapat data history".
        // Let's show all documents uploaded by the current user as "History Upload"
        $documents = BukuSakuDocument::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('buku-saku.history', compact('documents'));
    }
    
    public function show(BukuSakuDocument $document)
    {
        // Ensure the file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
             // We can still show the page but maybe with a warning, or just handle it in the view
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
}
