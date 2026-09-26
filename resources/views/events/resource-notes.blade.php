<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resource->title }} | {{ $event->title }}</title>
    @vite('resources/css/app.css')
    <script>
        const savedTheme = localStorage.getItem('slau-theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const theme = savedTheme || systemTheme;
        document.documentElement.dataset.theme = theme;
        document.documentElement.classList.toggle('dark', theme === 'dark');
    </script>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-background">
    <main class="mx-auto flex max-w-4xl flex-col gap-6 px-4 py-8 sm:px-6">
        <a href="{{ route('events.show', $event) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            &larr; Back to {{ $event->title }}
        </a>

        <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
            <header class="flex flex-col gap-4 border-b border-gray-200 p-5 dark:border-border sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Lesson notes</p>
                    <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $resource->title }}</h1>
                </div>
                <a href="{{ route('events.resources.download', [$event, $resource]) }}" download
                   class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    Download PDF
                </a>
            </header>
            <div class="prose max-w-none overflow-x-auto p-5 text-gray-700 dark:prose-invert dark:text-gray-300 sm:p-8">
                {!! $notesHtml !!}
            </div>
        </article>
    </main>
</body>
</html>
