import { Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { RegisterForm, type Faculty } from '@/components/ui/register-form';
import { ThemeToggle } from '@/components/ui/theme-toggle';

interface RegisterProps { faculties: Faculty[]; }

export default function Register({ faculties }: RegisterProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '', email: '', registration_number: '', phone: '', program: '', faculty: '',
        year_of_study: '', intake: '', intake_year: '', password: '', password_confirmation: '', terms: false,
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post('/auth/register', { onSuccess: () => reset('password', 'password_confirmation') });
    };

    return (
        <main className="min-h-screen overflow-x-hidden bg-background font-sans text-foreground">
            <header className="bg-card/95 shadow-theme-xs backdrop-blur-xl">
                <div className="flex h-16 w-full items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                    <Link href="/" className="flex min-w-0 shrink-0 items-center gap-2" aria-label="SCIC Cyber home">
                        <img src="/images/club_logo.png" alt="SLAU-CSIC" className="h-10 w-auto dark:brightness-0 dark:invert" />
                        <span className="hidden text-sm font-bold leading-tight text-foreground sm:block">SCIC <span className="block text-xs font-medium text-muted-foreground">Cyber</span></span>
                    </Link>
                    <div className="flex shrink-0 items-center gap-2 sm:gap-3">
                        <Link href="/auth/login" className="inline-flex min-h-10 items-center rounded-sm px-3 py-2 text-sm font-semibold text-muted-foreground transition-colors duration-200 hover:bg-card-hover hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:px-4">Sign In</Link>
                        <ThemeToggle />
                    </div>
                </div>
            </header>

            <div className="mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6">
                <Link href="/" className="mb-3 inline-flex min-h-9 items-center gap-2 rounded-sm px-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-card-hover hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"><span className="material-symbols-outlined text-[19px]" aria-hidden="true">arrow_back</span>Back to home</Link>
                <RegisterForm
                    onSubmit={submit} faculties={faculties} name={data.name} email={data.email}
                    registration_number={data.registration_number} phone={data.phone} program={data.program}
                    faculty={data.faculty} year_of_study={data.year_of_study} intake={data.intake}
                    intake_year={data.intake_year} password={data.password} password_confirmation={data.password_confirmation}
                    terms={data.terms} onNameChange={(value) => setData('name', value)}
                    onEmailChange={(value) => setData('email', value)}
                    onRegistrationNumberChange={(value) => setData('registration_number', value)}
                    onPhoneChange={(value) => setData('phone', value)} onProgramChange={(value) => setData('program', value)}
                    onFacultyChange={(value) => setData('faculty', value)} onYearOfStudyChange={(value) => setData('year_of_study', value)}
                    onIntakeChange={(value) => setData('intake', value)} onIntakeYearChange={(value) => setData('intake_year', value)}
                    onPasswordChange={(value) => setData('password', value)}
                    onPasswordConfirmationChange={(value) => setData('password_confirmation', value)}
                    onTermsChange={(value) => setData('terms', value)} processing={processing} errors={errors}
                />
            </div>
        </main>
    );
}
