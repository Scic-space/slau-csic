<div wire:poll.{{ $pollInterval }}s>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="pb-3 text-left font-medium text-gray-500 w-16">Rank</th>
                    <th class="pb-3 text-left font-medium text-gray-500">{{ $competition->allow_teams && $viewMode !== 'individual' ? 'Team' : 'Player' }}</th>
                    <th class="pb-3 text-right font-medium text-gray-500">Score</th>
                    <th class="pb-3 text-right font-medium text-gray-500">Solves</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($scoreboard as $entry)
                @php
                    $isCurrentUser = isset($entry['user_id']) && $entry['user_id'] === auth()->id();
                @endphp
                <tr class="border-b border-gray-100 dark:border-gray-800 {{ $isCurrentUser ? 'bg-emerald-50 dark:bg-emerald-900/10' : '' }}">
                    <td class="py-3">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-md text-xs font-semibold text-white
                            @if($entry['rank'] === 1) bg-yellow-500
                            @elseif($entry['rank'] === 2) bg-gray-400
                            @elseif($entry['rank'] === 3) bg-amber-700
                            @else bg-slate-900 dark:bg-slate-700 @endif">
                            {{ $entry['rank'] }}
                        </span>
                    </td>
                    <td class="py-3 font-medium text-gray-900 dark:text-white">
                        {{ $entry['name'] ?? $entry['team_name'] ?? '' }}
                        @if (isset($entry['member_count']))
                        <span class="ml-2 text-xs text-gray-500">({{ $entry['member_count'] }} members)</span>
                        @endif
                    </td>
                    <td class="py-3 text-right font-semibold text-gray-900 dark:text-white">{{ $entry['total_score'] }} pts</td>
                    <td class="py-3 text-right text-gray-500 dark:text-gray-400">{{ $entry['solves_count'] ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-8 text-center text-gray-500">No scores yet. Be the first to solve!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if (isset($userRank))
    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
        <p class="text-sm text-emerald-700 dark:text-emerald-300">Your rank: <strong>#{{ $userRank }}</strong></p>
    </div>
    @endif
</div>
