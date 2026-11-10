<?php

namespace App\Http\Controllers;

use App\Models\FileManagerItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FileManagerController extends Controller
{
    public function index(): View
    {
        return view('pages.apps-file-manager', [
            'files' => FileManagerItem::query()->whereNull('parent_id')->orderBy('type')->orderBy('name')->get(),
            'allFiles' => FileManagerItem::query()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['nullable', 'file', 'max:10240'],
            'name' => ['required_without:file', 'string', 'max:255'],
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('files', 'public');
            $name = $request->file('file')->getClientOriginalName();
            $size = $request->file('file')->getSize();
        } else {
            $path = null;
            $name = $request->string('name')->toString();
            $size = null;
        }

        FileManagerItem::create([
            'name' => $name,
            'type' => $request->boolean('is_folder') ? 'folder' : 'file',
            'size' => $size !== null ? round($size / 1024, 1).' KB' : null,
            'date_label' => now()->format('d M, Y'),
            'icon' => $request->boolean('is_folder') ? 'bx bx-folder' : 'bx bx-file',
            'icon_class' => $request->boolean('is_folder') ? 'text-warning' : 'text-primary',
            'user_id' => auth()->id(),
        ]);

        return back()->with('status', 'Uploaded.');
    }

    public function destroy(FileManagerItem $file): RedirectResponse
    {
        $file->delete();

        return back()->with('status', 'Deleted.');
    }
}
