<?php
// تحديث الكود من داخل لوحة التحكم
session_start();

// حماية بسيطة: لازم يكون اليوزر مسجل دخول كأدمن
// لو عايز تشيل الحماية دي، امسح الـ if
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    exit('Forbidden');
}

$cmd = <<<'BASH'
cd /var/www/hf_system/live &&
/usr/bin/git add -A &&
/usr/bin/git commit -m "deploy: site update via web panel $(date +%F_%T)" || true &&
GIT_SSH_COMMAND="/usr/bin/ssh -i /var/www/.ssh/id_ed25519 -o IdentitiesOnly=yes" \
/usr/bin/git push origin main
BASH;

$output = shell_exec($cmd . ' 2>&1');

// حفظ اللوج في ملف
file_put_contents(
    '/var/www/hf_system/live/storage/logs/update.log',
    "\n[".date('Y-m-d H:i:s')."]\n".$output."\n",
    FILE_APPEND
);

// عرض النتيجة في المتصفح
echo "<pre>$output</pre>";
