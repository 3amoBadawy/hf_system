<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UpdateController extends Controller
{
    public function page() { return view('admin.update'); }

    public function run(Request $request)
    {
        $request->validate([
            'package' => ['required','file','mimes:zip','max:51200'], // 50MB
        ]);

        $path = $request->file('package')->store('updates');
        $full = Storage::path($path);

        $tmpDir = storage_path('app/tmp_update_'.uniqid());
        @mkdir($tmpDir, 0775, true);

        $zip = new ZipArchive();
        if ($zip->open($full) !== true) {
            return back()->withErrors(['package' => 'فشل فتح ملف الـ ZIP']);
        }
        $zip->extractTo($tmpDir);
        $zip->close();

        // انسخ المحتوى (استثناء vendor/storage)
        $cmd = sprintf(
            "rsync -a --delete --exclude 'vendor' --exclude 'storage' %s/ %s/",
            escapeshellarg($tmpDir), escapeshellarg(base_path())
        );
        shell_exec($cmd);

        Artisan::call('optimize:clear');
        Artisan::call('migrate', ['--force'=>true]);
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:clear');

        return back()->with('ok', 'تم رفع وتطبيق التحديث بنجاح.');
    }
}
