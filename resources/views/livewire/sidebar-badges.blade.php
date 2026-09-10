<div class="hidden" aria-hidden="true"
     wire:poll.30s="refreshCounts"
     x-data
     x-effect="$store.sidebarBadges.sync({
         unreadAnnouncements: $wire.unreadAnnouncements,
         unansweredPolls: $wire.unansweredPolls,
         unpaidFines: $wire.unpaidFines,
         upcomingMeetings: $wire.upcomingMeetings,
         eventsToRegister: $wire.eventsToRegister,
         upcomingMyEvents: $wire.upcomingMyEvents,
     })">
</div>