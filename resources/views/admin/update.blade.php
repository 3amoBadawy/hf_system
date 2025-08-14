<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">تحديث النظام</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 border border-green-300 rounded">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-300 rounded">
                    <ul class="list-disc ps-6">
                        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.update.run') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1">ملف التحديث (ZIP)</label>
                    <input type="file" name="package" accept=".zip" required class="w-full border rounded p-2">
                    <p class="text-sm text-gray-500 mt-1">الحد الأقصى 50MB</p>
                </div>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">تشغيل التحديث</button>
            </form>
        </div>

        @if (session('log'))
            <div class="max-w-3xl mx-auto mt-6 bg-white p-6 rounded shadow">
                <h3 class="font-semibold mb-2">Logs</h3>
                <pre class="text-sm overflow-auto">{{ implode("\n", session('log')) }}</pre>
            </div>
        @endif
    </div>
</x-app-layout>
