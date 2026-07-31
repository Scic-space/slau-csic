<x-filament-widgets::widget>
    <div class="fi-wi-widget p-6">
        <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white mb-4">
            Recent Activity
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Activity</th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($activities as $activity)
                        <tr>
                            <td class="px-3 py-3 whitespace-nowrap">
                                <x-filament::badge :color="$activity['color']">{{ $activity['type'] }}</x-filament::badge>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-950 dark:text-white">{{ $activity['description'] }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($activity['time'])->format('M d, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No recent activity</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
