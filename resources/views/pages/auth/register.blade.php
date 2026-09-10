@extends('layouts.fullscreen-layout')

@section('content')
    <div class="portal-shell relative min-h-screen overflow-hidden">
        <div class="portal-grid absolute inset-0 opacity-40"></div>

        <div class="relative z-10 flex min-h-screen flex-col lg:flex-row">
            <section class="flex w-full lg:w-[54%]">
                <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col justify-center px-6 py-12 sm:px-10 lg:px-14">
                    <div class="mb-8 flex items-center justify-between gap-4">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-md bg-white p-1.5 shadow-[0_16px_40px_rgba(2,8,23,0.22)]">
                                <img src="{{ asset('images/club/logo1.jpg') }}" alt="SLAU Cybersecurity Club logo" class="h-9 w-9 object-contain">
                            </span>
                            <span>
                                <span class="block text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--portal-primary);">SLAU</span>
                                <span class="portal-muted block text-xs">Cybersecurity &amp; Innovations Club</span>
                            </span>
                        </a>

                        <button type="button" class="portal-toggle text-xs font-medium uppercase tracking-[0.18em]" data-theme-toggle>
                            <span data-theme-icon>☾</span>
                            <span data-theme-label>Dark</span>
                        </button>
                    </div>

                    <div class="mb-8">
                        <p class="text-sm font-medium uppercase tracking-[0.28em]" style="color: var(--portal-secondary);">Member Registration</p>
                        <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl" style="color: var(--portal-text);">Join the club</h1>
                        <p class="portal-copy mt-4 max-w-2xl text-sm leading-7 sm:text-base">
                            Register with your basic academic and contact details to become a member.
                        </p>
                    </div>

                    <div class="portal-card rounded-md p-6 sm:p-8">
                        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <div class="grid gap-5 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label for="name" class="portal-copy mb-2 block text-sm font-medium">Full name <span style="color: var(--portal-primary);">*</span></label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Your full name" autofocus class="portal-field h-12 w-full rounded-sm px-4 text-sm placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('name') border-error-500 @enderror" />
                                    @error('name')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="portal-copy mb-2 block text-sm font-medium">Email address <span style="color: var(--portal-primary);">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@slau.ac.ug" class="portal-field h-12 w-full rounded-sm px-4 text-sm placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('email') border-error-500 @enderror" />
                                    @error('email')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="registration_number" class="portal-copy mb-2 block text-sm font-medium">Registration Number <span style="color: var(--portal-primary);">*</span></label>
                                    <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number') }}" placeholder="BACS/26D/U/A0000" class="portal-field h-12 w-full rounded-sm px-4 text-sm placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('registration_number') border-error-500 @enderror" />
                                    <p class="portal-muted mt-2 text-xs">Format: Course/Year+Mode/Country/Intake+Number (e.g. BACS/26D/U/A0000)</p>
                                    @error('registration_number')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="portal-copy mb-2 block text-sm font-medium">Phone number <span style="color: var(--portal-primary);">*</span></label>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="07xx xxx xxx" class="portal-field h-12 w-full rounded-sm px-4 text-sm placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('phone') border-error-500 @enderror" />
                                    @error('phone')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div x-data="{
                                    faculties: @js(array_values(config('academics.faculties'))),
                                    faculty: @js((string) old('faculty')),
                                    program: @js((string) old('program')),
                                    get facultyPrograms() {
                                        const match = this.faculties.find((f) => f.name === this.faculty);
                                        return match ? match.programs : [];
                                    },
                                    resetProgram() {
                                        this.program = '';
                                    },
                                }" class="md:col-span-2 grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="faculty" class="portal-copy mb-2 block text-sm font-medium">Faculty <span style="color: var(--portal-primary);">*</span></label>
                                        <select id="faculty" name="faculty" x-model="faculty" @change="resetProgram()" class="portal-field h-12 w-full rounded-sm px-4 text-sm focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('faculty') border-error-500 @enderror">
                                            <option value="">Select faculty</option>
                                            <template x-for="f in faculties" :key="f.name">
                                                <option :value="f.name" x-text="f.name"></option>
                                            </template>
                                        </select>
                                        @error('faculty')
                                            <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="program" class="portal-copy mb-2 block text-sm font-medium">Programme <span style="color: var(--portal-primary);">*</span></label>
                                        <select id="program" name="program" x-model="program" :disabled="facultyPrograms.length === 0" class="portal-field h-12 w-full rounded-sm px-4 text-sm disabled:opacity-50 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('program') border-error-500 @enderror">
                                            <option value="" x-text="facultyPrograms.length === 0 ? 'Select a faculty first' : 'Select programme'"></option>
                                            <template x-for="p in facultyPrograms" :key="p">
                                                <option :value="p" x-text="p"></option>
                                            </template>
                                        </select>
                                        @error('program')
                                            <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="year_of_study" class="portal-copy mb-2 block text-sm font-medium">Year of study <span style="color: var(--portal-primary);">*</span></label>
                                    <select id="year_of_study" name="year_of_study" class="portal-field h-12 w-full rounded-sm px-4 text-sm focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('year_of_study') border-error-500 @enderror">
                                        <option value="">Select year</option>
                                        @for ($year = 1; $year <= 6; $year++)
                                            <option value="{{ $year }}" @selected(old('year_of_study') == $year)>Year {{ $year }}</option>
                                        @endfor
                                    </select>
                                    @error('year_of_study')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="intake" class="portal-copy mb-2 block text-sm font-medium">Intake <span style="color: var(--portal-primary);">*</span></label>
                                    <select id="intake" name="intake" class="portal-field h-12 w-full rounded-sm px-4 text-sm focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('intake') border-error-500 @enderror">
                                        <option value="">Select intake</option>
                                        <option value="august" @selected(old('intake') == 'august')>August</option>
                                        <option value="january" @selected(old('intake') == 'january')>January</option>
                                        <option value="february" @selected(old('intake') == 'february')>February</option>
                                        <option value="may" @selected(old('intake') == 'may')>May</option>
                                    </select>
                                    @error('intake')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="intake_year" class="portal-copy mb-2 block text-sm font-medium">Intake year <span style="color: var(--portal-primary);">*</span></label>
                                    <select id="intake_year" name="intake_year" class="portal-field h-12 w-full rounded-sm px-4 text-sm focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('intake_year') border-error-500 @enderror">
                                        <option value="">Select year</option>
                                        @for ($year = now()->year - 5; $year <= now()->year; $year++)
                                            <option value="{{ $year }}" @selected(old('intake_year') == $year)>{{ $year }}</option>
                                        @endfor
                                    </select>
                                    <p class="portal-muted mt-2 text-xs">The year you were admitted. Used to calculate your membership card expiry.</p>
                                    @error('intake_year')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="profile_photo" class="portal-copy mb-2 block text-sm font-medium">Passport-style photo <span style="color: var(--portal-primary);">*</span></label>
                                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" class="portal-field flex h-12 w-full items-center rounded-sm px-3 py-2 text-sm file:mr-4 file:rounded-sm file:border-0 file:bg-emerald-400/15 file:px-3 file:py-2 file:font-medium file:text-emerald-200 @error('profile_photo') border-error-500 @enderror" />
                                    <p class="portal-muted mt-2 text-xs">Upload a clear headshot. This will be used in the member directory.</p>
                                    @error('profile_photo')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password" class="portal-copy mb-2 block text-sm font-medium">Password <span style="color: var(--portal-primary);">*</span></label>
                                    <div x-data="{ showPassword: false }" class="relative">
                                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" placeholder="Create a secure password" class="portal-field h-12 w-full rounded-sm px-4 pr-12 text-sm placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10 @error('password') border-error-500 @enderror" />
                                        <button type="button" @click="showPassword = !showPassword" class="portal-muted absolute inset-y-0 right-4 inline-flex items-center">Show</button>
                                    </div>
                                    @error('password')
                                        <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="portal-copy mb-2 block text-sm font-medium">Confirm password <span style="color: var(--portal-primary);">*</span></label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password" class="portal-field h-12 w-full rounded-sm px-4 text-sm placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10" />
                                </div>
                            </div>

                            <label class="portal-copy inline-flex items-start gap-3 text-sm">
                                <input type="checkbox" id="terms" name="terms" value="1" class="mt-1 h-4 w-4 rounded-sm border-white/20 bg-transparent text-emerald-400 focus:ring-emerald-400/20" @checked(old('terms'))>
                                <span>
                                    I confirm that the information provided is accurate and I agree to the club platform terms and membership review process.
                                </span>
                            </label>
                            @error('terms')
                                <p class="text-sm text-error-400">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="portal-button flex h-12 w-full items-center justify-center rounded-sm px-4 text-sm font-semibold transition">
                                Submit Membership Registration
                            </button>
                        </form>

                        <div class="portal-muted mt-6 text-sm">
                            Already have an account?
                            <a href="{{ route('login') }}" class="font-medium hover:text-sky-200" style="color: var(--portal-secondary);">Sign in here</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="portal-shell-panel relative hidden lg:flex lg:w-[46%]">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/club/certificate-team.jpg') }}');"></div>
                <div class="portal-grid absolute inset-0 opacity-35"></div>

                <div class="relative z-10 flex flex-1 items-end p-10 xl:p-14">
                    <div class="max-w-xl">
                        <div class="portal-badge">Member Intake</div>

                        <h2 class="mt-6 text-4xl font-semibold tracking-tight text-white xl:text-5xl">
                            Join a community of cybersecurity enthusiasts.
                        </h2>

                        <p class="portal-side-copy mt-5 max-w-lg text-base leading-8">
                            Complete your profile after registration to share more about yourself with fellow members.
                        </p>

                        <div class="portal-side-grid mt-8 sm:grid-cols-2">
                            <div class="portal-side-stat rounded-md">
                                <div class="portal-side-stat-value">CTF</div>
                                <div class="portal-side-stat-label">Competitions</div>
                            </div>
                            <div class="portal-side-stat rounded-md">
                                <div class="portal-side-stat-value">Labs</div>
                                <div class="portal-side-stat-label">Hands-on training</div>
                            </div>
                            <div class="portal-side-stat rounded-md">
                                <div class="portal-side-stat-value">Events</div>
                                <div class="portal-side-stat-label">Workshops & talks</div>
                            </div>
                            <div class="portal-side-stat rounded-md">
                                <div class="portal-side-stat-value">Mentors</div>
                                <div class="portal-side-stat-label">Guidance & support</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
