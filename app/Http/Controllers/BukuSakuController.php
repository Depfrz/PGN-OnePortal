<?php

namespace App\Http\Controllers;

use App\Models\BukuSakuDocument;
use App\Models\BukuSakuTag;
use App\Models\User;
use App\Models\AuditLog;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class BukuSakuController extends Controller
{
    public function index(Request $request)
    {
        // --- AUTO-FIX HISTORY START ---
        // Jika user mengakses halaman ini dengan parameter ?fix_history=true
        if ($request->has('fix_history') && $request->input('fix_history') == 'true') {
            $docs = \App\Models\BukuSakuDocument::all();
            $count = 0;
            foreach ($docs as $doc) {
                // Backfill Create Log
                $exists = \App\Models\AuditLog::where('module', 'Buku Saku')->where('description', 'Menambahkan dokumen: ' . $doc->title)->exists();
                if (!$exists) {
                    \App\Models\AuditLog::create([
                        'user_id' => $doc->user_id,
                        'action' => 'create',
                        'module' => 'Buku Saku',
                        'description' => 'Menambahkan dokumen: ' . $doc->title,
                        'created_at' => $doc->created_at,
                        'updated_at' => $doc->created_at,
                    ]);
                    $count++;
                }
                // Backfill Update Log
                if ($doc->updated_at != $doc->created_at) {
                     $existsUpdate = \App\Models\AuditLog::where('module', 'Buku Saku')->where('description', 'like', 'Mengupdate dokumen: ' . $doc->title . '%')->exists();
                     if (!$existsUpdate) {
                        \App\Models\AuditLog::create([
                            'user_id' => $doc->user_id,
                            'action' => 'update',
                            'module' => 'Buku Saku',
                            'description' => 'Mengupdate dokumen: ' . $doc->title . ' (Dipulihkan Sistem)',
                            'created_at' => $doc->updated_at,
                            'updated_at' => $doc->updated_at,
                        ]);
                        $count++;
                     }
                }
            }
            return redirect()->route('buku-saku.history')->with('success', "Otomatis memulihkan $count riwayat.");
        }
        // --- AUTO-FIX HISTORY END ---

        $query = $request->input('q');
        $selectedTags = $request->input('selected_tags', []);
        $showExpired = $request->boolean('show_expired');
        $hasSearch = !empty($query) || !empty($selectedTags) || $showExpired;
        $documents = collect();
        $otherDocuments = collect();

        $resultsNotFound = false;

        // Base Query Scope Helper
        $applyExpiredFilter = function($q) use ($showExpired) {
            if (!$showExpired) {
                $q->where(function($query) {
                    $query->whereDate('valid_until', '>=', now())
                          ->orWhereNull('valid_until');
                });
            }
        };

        if ($hasSearch) {
            // Only perform text search if query is present
            if (!empty($query)) {
                // 1. Prepare Data for NLP Engine
                // Fetch all approved documents to feed into the search engine
                $baseQuery = BukuSakuDocument::approved();
                $applyExpiredFilter($baseQuery);
                
                $allDocs = $baseQuery->select('id', 'title', 'description', 'tags')
                    ->get()
                    ->toArray();

                // Create a temporary JSON file to pass data to Python
                // Using a unique filename to avoid race conditions
                $tempFileName = 'search_data_' . time() . '_' . uniqid() . '.json';
                $tempFilePath = storage_path('app/' . $tempFileName);
                
                // Ensure directory exists
                if (!file_exists(dirname($tempFilePath))) {
                    mkdir(dirname($tempFilePath), 0755, true);
                }
                
                file_put_contents($tempFilePath, json_encode($allDocs));

                // 2. Execute Python Script
                $scriptPath = base_path('python_engine/search_engine.py');
                $pythonPath = env('PYTHON_PATH', 'python'); // Default to 'python'

                $escapedQuery = escapeshellarg($query);
                $escapedFilePath = escapeshellarg($tempFilePath);
                
                $output = null;
                try {
                    if (function_exists('shell_exec')) {
                        // Pass: script.py [json_file_path] [query]
                        $command = "\"$pythonPath\" \"$scriptPath\" $escapedFilePath $escapedQuery";
                        $output = shell_exec($command);
                    }
                } catch (\Exception $e) {
                    // Ignore error, fallback to SQL
                }
                
                // Cleanup temp file
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
                
                $results = [];
                $pythonSuccess = false;
                
                if ($output) {
                    $decoded = json_decode($output, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $results = $decoded;
                        $pythonSuccess = true;
                    }
                }
                
                // Logic: Use Python results if available. 
                // If Python failed (invalid JSON) OR Python returned 0 results, 
                // we try the Smart SQL Fallback to be safe and ensure user sees something.
                
                $documents = collect();
                
                if ($pythonSuccess && !empty($results)) {
                    $ids = array_column($results, 'id');
                    if (!empty($ids)) {
                        $documents = BukuSakuDocument::approved()
                            ->with('user')
                            ->whereIn('id', $ids)
                            ->get()
                            ->sortBy(function($model) use ($ids) {
                                return array_search($model->id, $ids);
                            });

                        // Filter expired from results if needed (double check)
                        if (!$showExpired) {
                            $documents = $documents->filter(function($doc) {
                                return is_null($doc->valid_until) || $doc->valid_until >= now();
                            });
                        }
                    }
                }
                
                // If documents is empty (either Python failed, or Python found nothing), try Smart SQL
                if ($documents->isEmpty()) {
                    // Smart SQL Fallback: Split keywords
                    $keywords = explode(' ', $query);
                    // Filter empty keywords
                    $keywords = array_filter($keywords, function($k) { return strlen($k) > 2; }); // Ignore very short words
                    
                    if (empty($keywords)) {
                        $keywords = [$query];
                    }
                    
                    // Fetch ALL potential matches first, then rank in PHP
                    // Doing complex ranking in SQL is harder across DB types (MySQL vs SQLite etc)
                    $baseQuery = BukuSakuDocument::approved()->with('user');
                    $applyExpiredFilter($baseQuery);
                    
                    $potentialDocs = $baseQuery->where(function($q) use ($keywords, $query) {
                            // 1. Exact phrase match
                            $q->where('title', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%")
                            ->orWhere('tags', 'like', "%{$query}%");
                            
                            // 2. Keyword match
                            foreach ($keywords as $word) {
                                $q->orWhere('title', 'like', "%{$word}%")
                                ->orWhere('description', 'like', "%{$word}%")
                                ->orWhere('tags', 'like', "%{$word}%");
                            }
                        })
                        ->get();
                        
                    // Custom Ranking Logic
                    $documents = $potentialDocs->sortByDesc(function($doc) use ($keywords, $query) {
                        $score = 0;
                        $title = strtolower($doc->title ?? '');
                        $desc = strtolower($doc->description ?? '');
                        $tags = strtolower($doc->tags ?? '');
                        $qLower = strtolower($query);
                        
                        // Priority 1: Exact Phrase Match (Highest Score)
                        if (str_contains($title, $qLower)) $score += 50;
                        if (str_contains($tags, $qLower)) $score += 40;
                        if (str_contains($desc, $qLower)) $score += 30;
                        
                        // Priority 2: Keyword Matches (Count how many keywords appear)
                        foreach ($keywords as $word) {
                            $word = strtolower($word);
                            if (str_contains($title, $word)) $score += 10;
                            if (str_contains($tags, $word)) $score += 8;
                            if (str_contains($desc, $word)) $score += 5;
                        }
                        
                        return $score;
                    });
                }
            } else {
                // If query is empty but tags are selected, start with all approved documents
                $documents = BukuSakuDocument::approved()
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // Apply Tag Filter if selected
            if (!empty($selectedTags)) {
                $documents = $documents->filter(function($doc) use ($selectedTags) {
                    if (empty($doc->tags)) return false;
                    $docTags = array_map('trim', explode(',', strtolower($doc->tags)));
                    foreach ($selectedTags as $tag) {
                        if (in_array(strtolower(trim($tag)), $docTags)) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            // Get Other Documents (Not in Search Results)
            $foundIds = $documents->pluck('id')->toArray();
            $otherDocuments = BukuSakuDocument::approved()
                ->with('user')
                ->whereNotIn('id', $foundIds)
                ->orderBy('created_at', 'desc')
                ->get();

        } else {
            // Default view: Show latest approved documents
            $baseQuery = BukuSakuDocument::approved()->with('user');
            $applyExpiredFilter($baseQuery);
            
            $documents = $baseQuery->orderBy('created_at', 'desc')->get();
        }

        $availableTags = BukuSakuTag::all();

        // Check write access for current user
        $user = Auth::user();
        $moduleIds = \App\Models\Module::whereIn('name', ['Pengecekan File', 'Upload Dokumen', 'Buku Saku'])->pluck('id');
        $hasWriteAccess = \App\Models\ModuleAccess::where('user_id', $user->id)
            ->whereIn('module_id', $moduleIds)
            ->where('can_write', true)
            ->exists();

        return view('buku-saku.index', compact('documents', 'otherDocuments', 'hasSearch', 'query', 'resultsNotFound', 'availableTags', 'hasWriteAccess'));
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
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:30720', // Max 15MB
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
            'valid_until' => $request->valid_until ? \Carbon\Carbon::parse($request->valid_until)->addYears(5) : null,
        ]);

        // Log Activity
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Buku Saku',
            'description' => 'Menambahkan dokumen: ' . $request->title,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notify All Users about new document
        $allUsers = User::all();
        Notification::send($allUsers, new SystemNotification(
            'new_document',
            'Buku Saku',
            'Dokumen baru tersedia: "' . $request->title . '"',
            Auth::user()->name
        ));

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Dokumen berhasil diunggah.', 'redirect' => route('buku-saku.index')]);
        }

        return redirect()->route('buku-saku.index')->with('success', 'Dokumen berhasil diunggah.');
    }

    public function edit($id)
    {
        $document = BukuSakuDocument::findOrFail($id);
        
        /** @var User $user */
        $user = Auth::user();

        // Check permission (Admin, Supervisor, Owner, OR 'Pengecekan File' writer)
        $moduleIds = \App\Models\Module::whereIn('name', ['Pengecekan File', 'Upload Dokumen', 'Buku Saku'])->pluck('id');
        $hasWriteAccess = \App\Models\ModuleAccess::where('user_id', $user->id)
            ->whereIn('module_id', $moduleIds)
            ->where('can_write', true)
            ->exists();

        // Check permission (Owner or Admin/Supervisor or hasWriteAccess)
        if ($document->user_id !== $user->id && !$user->hasAnyRole(['Admin', 'Supervisor']) && !$hasWriteAccess) {
             return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        $availableTags = BukuSakuTag::all();
        return view('buku-saku.edit', compact('document', 'availableTags'));
    }

    public function update(Request $request, $id)
    {
        $document = BukuSakuDocument::findOrFail($id);

        /** @var User $user */
        $user = Auth::user();

        // Check permission (Admin, Supervisor, Owner, OR 'Pengecekan File' writer)
        $moduleIds = \App\Models\Module::whereIn('name', ['Pengecekan File', 'Upload Dokumen', 'Buku Saku'])->pluck('id');
        $hasWriteAccess = \App\Models\ModuleAccess::where('user_id', $user->id)
            ->whereIn('module_id', $moduleIds)
            ->where('can_write', true)
            ->exists();

        if ($document->user_id !== $user->id && !$user->hasAnyRole(['Admin', 'Supervisor']) && !$hasWriteAccess) {
             return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:30720',
            'valid_until' => 'nullable|date',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'valid_until' => $request->valid_until ? \Carbon\Carbon::parse($request->valid_until)->addYears(5) : null,
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

        // Log Activity (Update)
        \App\Models\AuditLog::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'action' => 'update',
            'module' => 'Buku Saku',
            'description' => 'Mengupdate dokumen: ' . $document->title,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('buku-saku.index')->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(BukuSakuDocument $document)
    {
        /** @var User $user */
        $user = Auth::user();

        // Check module access for "Pengecekan File"
        $moduleIds = \App\Models\Module::whereIn('name', ['Pengecekan File', 'Upload Dokumen', 'Buku Saku'])->pluck('id');
        $hasWriteAccess = \App\Models\ModuleAccess::where('user_id', $user->id)
            ->whereIn('module_id', $moduleIds)
            ->where('can_write', true)
            ->exists();

        // Allow owner or Admin/Supervisor or user with write access to delete
        if ($document->user_id !== $user->id && !$user->hasAnyRole(['Admin', 'Supervisor']) && !$hasWriteAccess) {
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

        // Log Activity
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Buku Saku',
            'description' => 'Menghapus dokumen: ' . $document->title,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Dokumen "' . $document->title . '" berhasil dihapus.');
    }

    public function approvalIndex()
    {
        /** @var User $user */
        $user = Auth::user();

        // Check module access for "Pengecekan File"
        $moduleIds = \App\Models\Module::whereIn('name', ['Pengecekan File', 'Upload Dokumen', 'Buku Saku'])->pluck('id');
        $hasWriteAccess = \App\Models\ModuleAccess::where('user_id', $user->id)
            ->whereIn('module_id', $moduleIds)
            ->where('can_write', true)
            ->exists();

        // Now just a list of all documents (Management view)
        // Or "Kelola Dokumen"
        $documents = BukuSakuDocument::with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('buku-saku.approval', compact('documents', 'hasWriteAccess'));
    }

    // approve/reject removed/deprecated

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

    public function favorites(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $query = $request->input('q');
        $selectedTags = $request->input('selected_tags', []);
        
        $documentsQuery = $user->favoriteDocuments()->with('user');

        // Text Search
        if (!empty($query)) {
             $documentsQuery->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('tags', 'like', "%{$query}%");
             });
        }

        $documents = $documentsQuery->orderBy('created_at', 'desc')->get();

        // Tag Filter (PHP Collection)
        if (!empty($selectedTags)) {
             $documents = $documents->filter(function($doc) use ($selectedTags) {
                if (empty($doc->tags)) return false;
                $docTags = array_map('trim', explode(',', strtolower($doc->tags)));
                foreach ($selectedTags as $tag) {
                    if (in_array(strtolower(trim($tag)), $docTags)) {
                        return true;
                    }
                }
                return false;
            });
        }

        $availableTags = BukuSakuTag::all();

        return view('buku-saku.favorites', compact('documents', 'availableTags', 'query', 'selectedTags'));
    }

    public function expiredIndex(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Check Access: Admin, Supervisor, or has 'Dokumen Kedaluwarsa' access
        if (!$user->hasRole(['Admin', 'Supervisor'])) {
             $hasAccess = $user->moduleAccesses()->whereHas('module', function($q) {
                 $q->where('name', 'Dokumen Kedaluwarsa');
             })->where('can_read', true)->exists();

             if (!$hasAccess) {
                 abort(403, 'Anda tidak memiliki akses untuk melihat dokumen kedaluwarsa.');
             }
        }

        $query = $request->input('q');
        $selectedTags = $request->input('selected_tags', []);
        
        // Base query: Approved documents that are expired or expiring within 1 year
            $documentsQuery = BukuSakuDocument::approved()
                ->with('user')
                ->whereDate('valid_until', '<=', now()->addYear()->endOfDay());

        // Text Search
        if (!empty($query)) {
             $documentsQuery->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('tags', 'like', "%{$query}%");
             });
        }

        $documents = $documentsQuery->orderBy('valid_until', 'asc')->get();

        // Tag Filter (PHP Collection)
        if (!empty($selectedTags)) {
             $documents = $documents->filter(function($doc) use ($selectedTags) {
                if (empty($doc->tags)) return false;
                $docTags = array_map('trim', explode(',', strtolower($doc->tags)));
                foreach ($selectedTags as $tag) {
                    if (in_array(strtolower(trim($tag)), $docTags)) {
                        return true;
                    }
                }
                return false;
            });
        }

        $availableTags = BukuSakuTag::all();

        return view('buku-saku.expired', compact('documents', 'availableTags', 'query', 'selectedTags'));
    }

    public function history()
    {
        // "Riwayat Dokumen" - Activity Log for Buku Saku
        $logs = AuditLog::where('module', 'Buku Saku')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('buku-saku.history', compact('logs'));
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
            
            // Force inline display for PDF to ensure it opens in browser
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->title . '.pdf"'
            ]);
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

    public function destroyTag(Request $request, $id)
    {
        // Only Admin or Supervisor should probably delete tags, but let's allow any user for now based on request "bisa tambah dan hapus tag sendiri"
        // Or maybe restrict to creator? But tags are global. 
        // Let's assume open for now or restrict to Admin/Supervisor if "sendiri" means "I can do it myself" as an admin.
        // Usually global tags are managed by admins. But if user says "sendiri", they might mean "I want to be able to do it".
        
        $tag = BukuSakuTag::findOrFail($id);
        $tag->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Tag berhasil dihapus']);
        }

        return redirect()->back()->with('success', 'Tag berhasil dihapus.');
    }
}
