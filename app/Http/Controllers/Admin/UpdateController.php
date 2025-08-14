<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class UpdateController extends Controller{
    public function index(){ return view('admin.update'); }
    public function store(Request $request){
        $request->validate(['package'=>['required','file','mimes:zip','max:51200']]);
        $f=$request->file('package');
        $name='update_'.date('Ymd_His').'.zip';
        $path=$f->storeAs('updates',$name,'local'); // storage/app/updates
        $zip=new ZipArchive;
        if($zip->open(storage_path('app/'.$path))!==true){
            return back()->withErrors(['package'=>'ZIP غير صالح.']);
        }
        $tmp=storage_path('app/tmp_'.uniqid());
        @mkdir($tmp,0775,true);
        $zip->extractTo($tmp); $zip->close();
        Artisan::call('optimize:clear');
        return back()->with('ok','تم رفع الحزمة، النشر يتم من سكربت السيرفر.');
    }
}
