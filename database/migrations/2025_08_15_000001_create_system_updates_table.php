<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('system_updates', function (Blueprint $t) {
            $t->id();
            $t->string('zip_name');
            $t->string('backup_code_path');
            $t->string('backup_db_path');
            $t->enum('status', ['pending','success','failed','rolled_back'])->default('pending');
            $t->text('log')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamps();
        });
        if (!Schema::hasColumn('users','is_admin')) {
            Schema::table('users', function (Blueprint $t) {
                $t->boolean('is_admin')->default(false)->after('password');
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('system_updates');
        if (Schema::hasColumn('users','is_admin')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropColumn('is_admin');
            });
        }
    }
};
