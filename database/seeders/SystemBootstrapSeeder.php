<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class SystemBootstrapSeeder extends Seeder {
  public function run(): void {
    $roles = ['Admin','Manager','Sales','Cashier','Inventory','HR','Accounting'];
    foreach ($roles as $r) { Role::firstOrCreate(['name'=>$r]); }

    $perms = [
      'settings.manage','branches.manage','users.manage','roles.manage','permissions.manage',
      'payment_methods.manage','invoices.create','invoices.approve','invoices.view','payments.collect',
      'products.manage','bundles.manage','inventory.adjust','inventory.transfer','customers.manage'
    ];
    foreach ($perms as $p) { Permission::firstOrCreate(['name'=>$p]); }
    Role::where('name','Admin')->first()?->givePermissionTo($perms);

    DB::table('branches')->updateOrInsert(['code'=>env('DEFAULT_BRANCH_CODE','BR1')],[
      'name'=>env('DEFAULT_BRANCH_NAME','Main Showroom'),
      'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
    ]);

    foreach ([
      ['name'=>'نقدي','code'=>'CASH'],
      ['name'=>'تحويل بنكي','code'=>'TRANSFER'],
      ['name'=>'بطاقة','code'=>'CARD'],
      ['name'=>'تقسيط','code'=>'INSTALLMENT'],
    ] as $pm) {
      DB::table('payment_methods')->updateOrInsert(['code'=>$pm['code']],[
        'name'=>$pm['name'],'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
      ]);
    }

    $admin = \App\Models\User::firstOrCreate(['username'=>env('ADMIN_USERNAME','admin')],[
      'name'=>env('ADMIN_NAME','Admin'),
      'email'=>env('ADMIN_EMAIL','admin@example.com'),
      'password'=>Hash::make(env('ADMIN_PASSWORD','ChangeMe!123')),
    ]);
    $admin->assignRole('Admin');
  }
}
