<x-filament-widgets::widget>
    <div class="fi-wi-widget p-6">
        <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white mb-4">
            Pending Approvals
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"></th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Details</th>
                        <th class="px-3 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($items as $item)
                        <tr>
                            <td class="px-3 py-3 whitespace-nowrap">
                                @php
                                    $urgencyColor = match($item['urgency']) { 'high' => 'danger', 'medium' => 'warning', 'low' => 'info', default => 'gray' };
                                @endphp
                                <x-filament::badge :color="$urgencyColor" class="!p-0 !w-2 !h-2 !min-w-0 !min-h-0 rounded-full" />
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                @php
                                    $typeColor = match($item['type']) { 'Pending Member' => 'warning', 'Transaction' => 'info', 'Fine Appeal' => 'danger', default => 'primary' };
                                @endphp
                                <x-filament::badge :color="$typeColor">{{ $item['type'] }}</x-filament::badge>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-950 dark:text-white">{{ $item['details'] }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($item['date'])->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nothing pending approval</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
