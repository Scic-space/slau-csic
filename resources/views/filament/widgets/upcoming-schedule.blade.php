<x-filament-widgets::widget>
    <div class="fi-wi-widget p-6">
        <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white mb-4">
            Upcoming Schedule
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($items as $item)
                        <tr>
                            <td class="px-3 py-3 whitespace-nowrap">
                                <x-filament::badge :color="$item['color']">{{ $item['type'] }}</x-filament::badge>
                            </td>
                            <td class="px-3 py-3 text-sm font-medium text-gray-950 dark:text-white whitespace-nowrap">{{ $item['title'] }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($item['date'])->format('M d, Y g:i A') }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $item['location'] }}</td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                @php
                                    $statusColor = match($item['status']) { 'published', 'open', 'ongoing' => 'success', 'upcoming', 'scheduled' => 'info', 'draft', 'past' => 'gray', 'cancelled' => 'danger', 'completed' => 'gray', default => 'primary' };
                                @endphp
                                <x-filament::badge :color="$statusColor">{{ ucfirst($item['status']) }}</x-filament::badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No upcoming events</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
