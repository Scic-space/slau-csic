@extends('layouts.frontend')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-6">
            <div class="mx-auto h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Check In</h2>
            <p class="text-gray-600 mt-2">{{ $meeting->title }}</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Date</p>
                    <p class="font-medium">{{ $meeting->scheduled_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Time</p>
                    <p class="font-medium">{{ $meeting->scheduled_at->format('h:i A') }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-500">Location</p>
                    <p class="font-medium">{{ $meeting->location }}</p>
                </div>
            </div>
        </div>

        @if($meeting->hasMeetingLink())
        <a href="{{ $meeting->meeting_link }}" target="_blank" rel="noopener noreferrer"
           class="w-full flex items-center justify-center bg-teal-600 text-white py-3 px-4 rounded-lg hover:bg-teal-700 transition font-medium mb-3">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Join Google Meet
        </a>
        @endif

        <button
            id="checkInBtn"
            type="button"
            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-medium"
            onclick="submitCheckIn()"
        >
            Check In Now
        </button>

        <div id="loading" class="hidden mt-4 text-center">
            <svg class="animate-spin mx-auto h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm text-gray-500 mt-2">Checking in...</p>
        </div>

        <div id="successMessage" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span id="successText" class="text-green-800 font-medium">Checked in successfully!</span>
            </div>
            <p id="bonusText" class="text-sm text-green-700 mt-1 hidden"></p>
        </div>

        <div id="errorMessage" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span id="errorText" class="text-red-800 font-medium"></span>
            </div>
        </div>
    </div>
</div>

<script>
function submitCheckIn() {
    const btn = document.getElementById('checkInBtn');
    const loading = document.getElementById('loading');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    const successText = document.getElementById('successText');
    const bonusText = document.getElementById('bonusText');
    const errorText = document.getElementById('errorText');

    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    loading.classList.remove('hidden');
    successMessage.classList.add('hidden');
    errorMessage.classList.add('hidden');

    fetch('{{ route("attendance.verify", $meeting->meeting_code) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        loading.classList.add('hidden');

        if (data.success) {
            successMessage.classList.remove('hidden');
            successText.textContent = data.message;

            if (data.bonus_earned > 0) {
                bonusText.classList.remove('hidden');
                bonusText.textContent = `+${data.bonus_earned} bonus points earned!`;
            }

            btn.textContent = 'Checked In';
        } else {
            errorMessage.classList.remove('hidden');
            errorText.textContent = data.message;
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    })
    .catch(error => {
        loading.classList.add('hidden');
        errorMessage.classList.remove('hidden');
        errorText.textContent = 'An error occurred. Please try again.';
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    });
}
</script>
@endsection
