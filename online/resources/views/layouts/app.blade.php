<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Bank</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('question-bank.index') }}" class="text-lg font-semibold text-gray-900">Question Bank</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('question-bank.export') }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700">
                        Export JSON
                    </a>
                    <a href="{{ route('question-bank.create') }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700">
                        + Create Question
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded">{{ session('error') }}</div>
        @endif
        @if(session('message'))
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded">{{ session('message') }}</div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>