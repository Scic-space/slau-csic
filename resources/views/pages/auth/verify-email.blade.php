@extends('layouts.fullscreen-layout')

@section('content')
    <div class="portal-shell relative min-h-screen overflow-hidden">
        <div class="portal-grid absolute inset-0 opacity-40"></div>
        <div class="portal-orb portal-orb-secondary left-[10%] top-[10%] h-52 w-52"></div>
        <div class="portal-orb portal-orb-primary bottom-[10%] right-[12%] h-52 w-52"></div>

        <div class="relative z-10 flex min-h-screen flex-col lg:flex-row">
            <section class="flex w-full lg:w-[46%]">
                <div class="mx-auto flex w-full max-w-xl flex-1 flex-col justify-center px-6 py-12 sm:px-10 lg:px-14">
                    <div class="mb-8 flex items-center justify-between gap-4">
                        <a href="{{ route('dashboard') }}" class="portal-backlink">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back to dashboard
                        </a>

                        <button type="button" class="portal-toggle text-xs font-medium uppercase tracking-[0.18em]" data-theme-toggle>
                            <span data-theme-icon>☾</span>
                            <span data-theme-label>Dark</span>
                        </button>
                    </div>

                    <div class="mb-8">
                        <p class="text-sm font-medium uppercase tracking-[0.28em]" style="color: var(--portal-secondary);">Email Verification</p>
                        <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl" style="color: var(--portal-text);">Activate your member access</h1>
                        <p class="portal-copy mt-4 max-w-lg text-sm leading-7 sm:text-base">
                            Verify your email address to complete account activation and continue using the member portal securely.
                        </p>
                    </div>

                    <div class="portal-card rounded-md p-6 sm:p-8">
                        @if (session('status') == 'verification-link-sent')
                            <div class="mb-5 rounded-md border px-4 py-3 text-sm" style="border-color: rgba(25, 226, 143, 0.24); background: rgba(25, 226, 143, 0.08); color: var(--portal-text-soft);">
                                A new verification link has been sent to the email address you provided during registration.
                            </div>
                        @endif

                        <div class="space-y-5">
                            <p class="portal-copy text-sm leading-7">
                                Before getting started, please confirm your email using the link sent to your inbox. If the message did not arrive, you can request another verification email below.
                            </p>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <form method="POST" action="{{ route('verification.store.legacy') }}" class="w-full sm:w-auto">
                                    @csrf
                                    <button type="submit" class="portal-button flex h-12 w-full items-center justify-center rounded-sm px-6 text-sm font-semibold transition sm:w-auto">
                                        Resend Verification Email
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                                    @csrf
                                    <button type="submit" class="portal-field flex h-12 w-full items-center justify-center rounded-sm px-6 text-sm font-semibold transition sm:w-auto">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="portal-shell-panel relative hidden lg:flex lg:w-[54%]">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/club/with-gentlemen.jpg') }}');"></div>
                <div class="portal-grid absolute inset-0 opacity-40"></div>

                <div class="flex flex-1 items-end p-10 xl:p-14">
                    <div class="max-w-2xl">
                        <div class="portal-badge">Verification Layer</div>
                        <h2 class="mt-6 text-4xl font-semibold tracking-tight text-white xl:text-5xl">
                            Identity confirmation is part of a secure club platform.
                        </h2>
                        <p class="portal-side-copy mt-5 max-w-xl text-base leading-8">
                            Verification screens should feel like part of the product, not an afterthought. This version keeps the same premium portal mood while making the next step clear.
                        </p>

                        <div class="portal-side-grid mt-8 sm:grid-cols-3">
                            <div class="portal-side-stat">
                                <div class="portal-side-stat-value">Verified</div>
                                <div class="portal-side-stat-label">Email identity</div>
                            </div>
                            <div class="portal-side-stat">
                                <div class="portal-side-stat-value">Secure</div>
                                <div class="portal-side-stat-label">Portal access</div>
                            </div>
                            <div class="portal-side-stat">
                                <div class="portal-side-stat-value">Ready</div>
                                <div class="portal-side-stat-label">Member tools</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
