<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">تحديث النظام</h2></x-slot>
  <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
    @if ($errors->any())
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul class="list-disc ps-6">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif
    @if (session('ok'))
      <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('ok') }}</div>
    @endif
    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
      <form method="POST" action="{{ route('admin.update.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
          <label class="block mb-1">حزمة التحديث (ZIP)</label>
          <input type="file" name="package" class="w-full border rounded p-2 bg-white dark:bg-gray-900">
          <p class="text-sm text-gray-500 mt-1">الحد الأقصى 50MB</p>
        </div>
        <div class="flex gap-3">
          <x-primary-button>رفع الحزمة</x-primary-button>
          <a href="/" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded">رجوع</a>
        </div>
      </form>
    </div>
    <div class="text-xs text-gray-500 mt-6">للنشر استخدم سكربت السيرفر: <code>deploy_hf.sh</code></div>
  </div>
</x-app-layout>
