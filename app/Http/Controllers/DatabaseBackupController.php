<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DatabaseBackupController extends Controller
{
    public function index(): View
    {
        $files = collect(Storage::disk('local')->files('backups'))
            ->filter(fn (string $path) => $this->isBackupFilename(basename($path)))
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => Storage::disk('local')->size($path),
                'created_at' => Storage::disk('local')->lastModified($path),
            ])
            ->sortByDesc('created_at')
            ->values();

        return view('database-backup.index', compact('files'));
    }

    public function store(DatabaseBackupService $backups): RedirectResponse
    {
        $filename = 'abdullah-town-backup-'.now()->format('Y-m-d-His').'.sql.gz';
        Storage::disk('local')->makeDirectory('backups');

        try {
            $backups->create(Storage::disk('local')->path('backups/'.$filename));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete('backups/'.$filename);
            report($exception);

            throw ValidationException::withMessages(['backup_create' => $exception->getMessage()]);
        }

        return redirect()->route('database-backup.index')->with('success', 'Backup created successfully.');
    }

    public function download(string $filename): Response
    {
        abort_unless($this->isBackupFilename($filename) && Storage::disk('local')->exists('backups/'.$filename), 404);

        return Storage::disk('local')->download('backups/'.$filename, $filename, [
            'Content-Type' => 'application/gzip',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function destroy(string $filename): RedirectResponse
    {
        abort_unless($this->isBackupFilename($filename) && Storage::disk('local')->exists('backups/'.$filename), 404);
        Storage::disk('local')->delete('backups/'.$filename);

        return redirect()->route('database-backup.index')->with('success', 'Backup deleted.');
    }

    public function restore(Request $request, DatabaseBackupService $backups): RedirectResponse
    {
        $data = $request->validate([
            'backup' => ['required', 'file', 'max:512000', 'extensions:sql,gz'],
            'current_password' => ['required', 'string'],
            'confirm_restore' => ['accepted'],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'The password is incorrect.']);
        }

        try {
            $result = $backups->restore($data['backup']->getRealPath());
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages(['backup' => $exception->getMessage()]);
        }

        return redirect()->route('database-backup.index')->with('success', "Database {$result['database']} restored successfully.");
    }

    private function isBackupFilename(string $filename): bool
    {
        return preg_match('/\Aabdullah-town-backup-\d{4}-\d{2}-\d{2}-\d{6}\.sql\.gz\z/', $filename) === 1;
    }
}
