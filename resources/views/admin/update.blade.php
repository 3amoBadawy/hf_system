@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4">
    <h2 class="text-2xl font-bold mb-6">تحديث النظام</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ $errors->first() }}</div>
    @endif
    @if (session('ok'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('ok') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.update.run') }}" enctype="multipart/form-data" class="bg-white p-4 rounded shadow">
        @csrf
        <label class="block mb-2 font-semibold">حزمة التحديث (ZIP)</label>
        <input type="file" name="package" class="border rounded p-2 w-full mb-4" required>
        <p class="text-sm text-gray-500 mb-4">الحد الأقصى 50MB</p>
        <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded">رفع الحزمة</button>
        <a href="{{ url()->previous() }}" class="ml-3 text-gray-600">رجوع</a>
    </form>

    <p class="text-xs text-gray-500 mt-6">للنشر على السيرفر استخدم السكربت: <code>deploy_hf.sh</code></p>
</div>
@endsection
