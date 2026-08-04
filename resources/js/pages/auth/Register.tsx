import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { GlowyWavesBackground } from '@/components/ui/glowy-waves-hero-shadcnui';
import { RegisterForm, type Faculty } from '@/components/ui/register-form';
import { FormEventHandler } from 'react';

interface RegisterProps {
    faculties: Faculty[];
}

export default function Register({ faculties }: RegisterProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        registration_number: '',
        phone: '',
        program: '',
        faculty: '',
        year_of_study: '',
        intake: '',
        intake_year: '',
        password: '',
        password_confirmation: '',
        terms: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/auth/register', {
            onSuccess: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GlowyWavesBackground>
            <section className="relative flex min-h-screen w-full flex-col items-center justify-center px-6 py-12">
                <Link
                    href="/"
                    className="absolute left-6 top-6 z-20 inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/40 backdrop-blur transition-colors hover:border-white/20 hover:bg-white/10 hover:text-white/70"
                >
                    <ArrowLeft className="h-3.5 w-3.5" />
                    Home
                </Link>

                <RegisterForm
                    onSubmit={submit}
                    faculties={faculties}
                    name={data.name}
                    email={data.email}
                    registration_number={data.registration_number}
                    phone={data.phone}
                    program={data.program}
                    faculty={data.faculty}
                    year_of_study={data.year_of_study}
                    intake={data.intake}
                    intake_year={data.intake_year}
                    password={data.password}
                    password_confirmation={data.password_confirmation}
                    terms={data.terms}
                    onNameChange={(value) => setData('name', value)}
                    onEmailChange={(value) => setData('email', value)}
                    onRegistrationNumberChange={(value) => setData('registration_number', value)}
                    onPhoneChange={(value) => setData('phone', value)}
                    onProgramChange={(value) => setData('program', value)}
                    onFacultyChange={(value) => setData('faculty', value)}
                    onYearOfStudyChange={(value) => setData('year_of_study', value)}
                    onIntakeChange={(value) => setData('intake', value)}
                    onIntakeYearChange={(value) => setData('intake_year', value)}
                    onPasswordChange={(value) => setData('password', value)}
                    onPasswordConfirmationChange={(value) => setData('password_confirmation', value)}
                    onTermsChange={(value) => setData('terms', value)}
                    processing={processing}
                    errors={errors}
                />

                <p className="mt-8 text-xs text-white/20">
                    SLAU Cybersecurity &amp; Innovations Club
                </p>
            </section>
        </GlowyWavesBackground>
    );
}
