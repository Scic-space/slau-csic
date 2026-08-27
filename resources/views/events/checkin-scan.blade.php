<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="dashboard-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-border flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Event Check-In</h2>
                    <button x-show="!cameraActive" x-on:click="startCamera" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                        Open Scanner
                    </button>
                    <button x-show="cameraActive" x-on:click="stopCamera" class="rounded-lg bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 focus:ring-2 focus:ring-red-600 dark:focus:ring-red-400">
                        Close Scanner
                    </button>
                </div>
                <div class="px-5 py-6">
                    <div x-data="{
                        code: '',
                        scanning: false,
                        result: null,
                        error: null,
                        cameraActive: false,
                        videoStream: null,
                        scanInterval: null,

                        async startCamera() {
                            this.cameraActive = true;
                            this.error = null;
                            try {
                                this.videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: 320, height: 240 } });
                                await this.$nextTick();
                                const video = this.$refs.video;
                                if (video) {
                                    video.srcObject = this.videoStream;
                                    await video.play();
                                    this.startScanLoop();
                                }
                            } catch (e) {
                                this.cameraActive = false;
                                this.error = 'Camera access denied or not available. Use manual entry below.';
                            }
                        },

                        stopCamera() {
                            this.cameraActive = false;
                            if (this.scanInterval) {
                                clearInterval(this.scanInterval);
                                this.scanInterval = null;
                            }
                            if (this.videoStream) {
                                this.videoStream.getTracks().forEach(t => t.stop());
                                this.videoStream = null;
                            }
                        },

                        async startScanLoop() {
                            if (!('BarcodeDetector' in window)) {
                                this.error = 'QR scanning not supported in this browser. Use manual entry below.';
                                return;
                            }
                            const detector = new BarcodeDetector({ formats: ['qr_code'] });
                            this.scanInterval = setInterval(async () => {
                                if (!this.cameraActive || !this.$refs.video) return;
                                try {
                                    const barcodes = await detector.detect(this.$refs.video);
                                    if (barcodes.length > 0) {
                                        this.code = barcodes[0].rawValue;
                                        this.stopCamera();
                                        this.$nextTick(() => this.checkIn());
                                    }
                                } catch (e) {
                                    // Scanning frame failed, continue
                                }
                            }, 500);
                        },

                        async checkIn() {
                            if (!this.code.trim()) return;

                            this.scanning = true;
                            this.result = null;
                            this.error = null;

                            try {
                                const response = await fetch('{{ route('events.checkin.process') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ code: this.code.trim() }),
                                });

                                const data = await response.json();

                                if (!response.ok) {
                                    if (response.status === 409 && data.registration) {
                                        this.result = {
                                            success: false,
                                            message: data.message,
                                            name: data.registration.name,
                                            event: data.registration.event,
                                            alreadyCheckedIn: true,
                                            checkedInAt: data.registration.checked_in_at,
                                        };
                                    } else {
                                        this.error = data.message || 'Check-in failed.';
                                    }
                                    return;
                                }

                                this.result = {
                                    success: true,
                                    message: data.message,
                                    name: data.registration.name,
                                    event: data.registration.event,
                                };
                                this.code = '';
                            } catch (e) {
                                this.error = 'Network error. Please try again.';
                            } finally {
                                this.scanning = false;
                            }
                        }
                    }">
                        {{-- Camera Scanner --}}
                        <div x-show="cameraActive" x-cloak class="mb-6">
                            <div class="relative mx-auto max-w-xs overflow-hidden rounded-xl bg-black">
                                <video x-ref="video" class="w-full h-48 object-cover" autoplay playsinline muted></video>
                                <div class="absolute inset-0 border-2 border-dashed border-white/40 rounded-xl pointer-events-none"></div>
                                <p class="absolute bottom-2 left-0 right-0 text-center text-xs text-white/70">Align QR code within the frame</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="checkin-code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Enter Check-In Code
                                </label>
                                <input
                                    x-model="code"
                                    x-on:keydown.enter.prevent="checkIn"
                                    type="text"
                                    id="checkin-code"
                                    placeholder="e.g. ABC123XYZ789"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-center text-lg font-mono tracking-widest text-gray-900 placeholder-gray-400 focus:border-gray-500 focus:ring-2 focus:ring-gray-500/20 dark:border-border dark:bg-card dark:text-white dark:placeholder-gray-500 dark:focus:border-gray-400 dark:focus:ring-gray-400/20"
                                    maxlength="16"
                                    autofocus
                                >
                            </div>

                            <button
                                x-on:click="checkIn"
                                x-bind:disabled="!code.trim() || scanning"
                                class="w-full rounded-lg bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white"
                            >
                                <span x-show="!scanning">Check In</span>
                                <span x-show="scanning">Processing...</span>
                            </button>
                        </div>

                        {{-- Success Result --}}
                        <div x-show="result && result.success" x-cloak class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                            <div class="flex items-center gap-3">
                                <svg class="h-8 w-8 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <p class="font-semibold text-green-800 dark:text-green-300" x-text="result.message"></p>
                                    <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                        <span x-text="result.name"></span> &mdash; <span x-text="result.event"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Already Checked In --}}
                        <div x-show="result && result.alreadyCheckedIn" x-cloak class="mt-6 rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-700 dark:bg-yellow-900/20">
                            <div class="flex items-center gap-3">
                                <svg class="h-8 w-8 shrink-0 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                <div>
                                    <p class="font-semibold text-yellow-800 dark:text-yellow-300" x-text="result.message"></p>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                        <span x-text="result.name"></span> &mdash; <span x-text="result.event"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Error --}}
                        <div x-show="error" x-cloak class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-700 dark:bg-red-900/20">
                            <div class="flex items-center gap-3">
                                <svg class="h-8 w-8 shrink-0 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="font-semibold text-red-800 dark:text-red-300" x-text="error"></p>
                            </div>
                        </div>

                        {{-- Quick actions --}}
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-border">
                            <a href="{{ route('events.index') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                &larr; Back to Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
