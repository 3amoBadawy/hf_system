<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UpdateController extends Controller
{
    public function page()
    {
        return view('admin.update');
    }

    public function run(Request $request)
    {
        $request->validate([
            'package' => 'required|file|mimes:zip|max:51200', // 50MB
        ]);

        $log = [];
        $pushSshCmd = 'ssh -i /var/www/.ssh/id_ed25519 -o IdentitiesOnly=yes';

        $ts = date('Ymd_His');
        $zipPath = $request->file('package')->storeAs("updates", "update_{$ts}.zip");
        $absZip = storage_path("app/{$zipPath}");
        $log[] = "✔️ Uploaded: $absZip";

        // 1) DB backup
        $db = env('DB_DATABASE'); $user=env('DB_USERNAME'); $pass=env('DB_PASSWORD'); $host=env('DB_HOST','127.0.0.1'); $port=env('DB_PORT','3306');
        $dbDump = storage_path("app/backups/db_{$db}_{$ts}.sql.gz");
        @mkdir(dirname($dbDump), 0775, true);
        $cmd = sprintf("mysqldump -h%s -P%s -u%s -p%s %s | gzip > %s",
            escapeshellarg($host), escapeshellarg($port), escapeshellarg($user), escapeshellarg($pass), escapeshellarg($db), escapeshellarg($dbDump)
        );
        $this->runCmd($cmd, $log, "DB backup");
        
        // 2) Code backup (قبل التحديث)
        $codeBackup = storage_path("app/backups/code_{$ts}.tar.gz");
        $this->runCmd("tar --exclude='vendor' --exclude='node_modules' --exclude='storage' -czf ".escapeshellarg($codeBackup)." -C /var/www/hf_system live", $log, "Code backup");

        // 3) فك الـ ZIP فوق المشروع
        $project = base_path(); // /var/www/hf_system/live
        $z = new ZipArchive();
        if ($z->open($absZip) !== true) {
            return back()->withErrors(['package'=>'فشل فتح ملف الـ ZIP'])->with('log',$log);
        }
        $z->extractTo($project);
        $z->close();
        $log[] = "✔️ Extracted into: $project";

        // 4) Composer (بدون سكربتات) + كاش
        $this->runCmd("cd ".escapeshellarg($project)." && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-scripts", $log, "composer install");
        // clear laravel compiled cache files first (just in case)
        @unlink($project.'/bootstrap/cache/config.php');
        @unlink($project.'/bootstrap/cache/packages.php');
        @unlink($project.'/bootstrap/cache/services.php');

        $this->runCmd("cd ".escapeshellarg($project)." && php artisan optimize:clear && php artisan config:cache && php artisan route:cache", $log, "artisan caches");

        // 5) Migrate
        $this->runCmd("cd ".escapeshellarg($project)." && php artisan migrate --force", $log, "artisan migrate");

        // 6) Git add/commit/push (كمستخدم www-data مع SSH)
        $gitMsg = "web-update: {$ts}";
        $this->runCmd("cd ".escapeshellarg($project)." && git add -A && git commit -m ".escapeshellarg($gitMsg)." || true", $log, "git commit");
        $this->runCmd("cd ".escapeshellarg($project)." && GIT_SSH_COMMAND=".escapeshellarg($pushSshCmd)." git push origin main", $log, "git push");

        return redirect()->route('admin.update')->with('status','✅ تم التحديث بنجاح')->with('log', $log);
    }

    private function runCmd(string $cmd, array &$log, string $title)
    {
        $log[] = "→ {$title}: $cmd";
        $out = [];
        $ret = 0;
        exec($cmd." 2>&1", $out, $ret);
        $log[] = implode("\n", $out);
        if ($ret !== 0) {
            $log[] = "⚠️ فشل: {$title} (code={$ret})";
        } else {
            $log[] = "✔️ OK: {$title}";
        }
    }
}
