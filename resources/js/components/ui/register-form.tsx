import { Link } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import { Loader2 } from 'lucide-react';
import { AuthInputError, AuthInputIcon, AuthPasswordToggle, authControlClass, authLabelClass } from '@/components/ui/auth-input';

export interface Faculty { name: string; programs: string[]; }

interface RegisterFormProps {
  onSubmit: (event: React.FormEvent) => void; faculties: Faculty[]; name: string; email: string;
  registration_number: string; phone: string; program: string; faculty: string; year_of_study: string;
  intake: string; intake_year: string; password: string; password_confirmation: string; terms: boolean;
  onNameChange: (value: string) => void; onEmailChange: (value: string) => void;
  onRegistrationNumberChange: (value: string) => void; onPhoneChange: (value: string) => void;
  onProgramChange: (value: string) => void; onFacultyChange: (value: string) => void;
  onYearOfStudyChange: (value: string) => void; onIntakeChange: (value: string) => void;
  onIntakeYearChange: (value: string) => void; onPasswordChange: (value: string) => void;
  onPasswordConfirmationChange: (value: string) => void; onTermsChange: (value: boolean) => void;
  processing: boolean; errors: Record<string, string>;
}

const controlClass = authControlClass;
const labelClass = authLabelClass;
const Icon = AuthInputIcon;
const ErrorMessage = AuthInputError;

function Section({ icon, title, description, children }: { icon: string; title: string; description: string; children: ReactNode }) {
  return <section><div className="mb-3 flex items-center gap-3"><span className="material-symbols-outlined flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-brand-50 text-[20px] text-brand-600 dark:bg-brand-500/10 dark:text-brand-300" aria-hidden="true">{icon}</span><div><h2 className="font-semibold leading-5 text-foreground">{title}</h2><p className="text-xs leading-5 text-muted-foreground sm:text-sm">{description}</p></div></div>{children}</section>;
}

export function RegisterForm(props: RegisterFormProps) {
  const {
    onSubmit, faculties, name, email, registration_number, phone, program, faculty, year_of_study,
    intake, intake_year, password, password_confirmation, terms, onNameChange, onEmailChange,
    onRegistrationNumberChange, onPhoneChange, onProgramChange, onFacultyChange, onYearOfStudyChange,
    onIntakeChange, onIntakeYearChange, onPasswordChange, onPasswordConfirmationChange, onTermsChange,
    processing, errors,
  } = props;
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmation, setShowConfirmation] = useState(false);
  const programs = faculties.find((item) => item.name === faculty)?.programs ?? [];
  const currentYear = new Date().getFullYear();
  const passwordRules = [
    ['At least 8 characters', password.length >= 8], ['One uppercase letter', /[A-Z]/.test(password)],
    ['One lowercase letter', /[a-z]/.test(password)], ['One number', /[0-9]/.test(password)],
    ['One special character', /[^A-Za-z0-9]/.test(password)],
  ] as const;

  return (
    <div className="min-w-0 rounded-sm bg-card/80 shadow-theme-sm backdrop-blur-sm dark:bg-card/70">
      <div className="px-5 pb-3 pt-5 sm:px-6"><h1 className="text-xl font-bold text-foreground sm:text-2xl">Membership registration</h1><p className="mt-1 text-sm text-muted-foreground"><span className="text-error-500">*</span> indicates a required field. All fields are shown below.</p></div>
      <form onSubmit={onSubmit} className="space-y-5 px-5 pb-5 pt-2 sm:px-6 sm:pb-6" noValidate={false}>
        <Section icon="person" title="Personal information" description="Tell us how to identify and contact you.">
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div><label htmlFor="name" className={labelClass}>Full name <span className="text-error-500">*</span></label><div className="relative"><Icon>person</Icon><input id="name" name="name" type="text" autoComplete="name" autoFocus required value={name} onChange={(event) => onNameChange(event.target.value)} placeholder="Your full name" className={controlClass(!!errors.name)} aria-invalid={!!errors.name} aria-describedby={errors.name ? 'name-error' : undefined} /></div><ErrorMessage id="name-error" message={errors.name} /></div>
            <div><label htmlFor="email" className={labelClass}>Email address <span className="text-error-500">*</span></label><div className="relative"><Icon>mail</Icon><input id="email" name="email" type="email" autoComplete="email" required value={email} onChange={(event) => onEmailChange(event.target.value)} placeholder="you@example.com" className={controlClass(!!errors.email)} aria-invalid={!!errors.email} aria-describedby={errors.email ? 'email-error' : undefined} /></div><ErrorMessage id="email-error" message={errors.email} /></div>
            <div><label htmlFor="phone" className={labelClass}>Phone number <span className="text-error-500">*</span></label><div className="relative"><Icon>phone</Icon><input id="phone" name="phone" type="tel" autoComplete="tel" required value={phone} onChange={(event) => onPhoneChange(event.target.value)} placeholder="Your phone number" className={controlClass(!!errors.phone)} aria-invalid={!!errors.phone} aria-describedby={errors.phone ? 'phone-error' : undefined} /></div><ErrorMessage id="phone-error" message={errors.phone} /></div>
            <div><label htmlFor="registration_number" className={labelClass}>Registration number <span className="text-error-500">*</span></label><div className="relative"><Icon>badge</Icon><input id="registration_number" name="registration_number" type="text" autoComplete="off" required value={registration_number} onChange={(event) => onRegistrationNumberChange(event.target.value)} placeholder="BACS/26D/U/A0000" className={controlClass(!!errors.registration_number)} aria-invalid={!!errors.registration_number} aria-describedby="registration-number-help registration-number-error" /></div><p id="registration-number-help" className="mt-1.5 text-xs text-muted-foreground">Course/Year + mode/Country/Intake + number</p><ErrorMessage id="registration-number-error" message={errors.registration_number} /></div>
          </div>
        </Section>

        <Section icon="school" title="Academic information" description="Connect your account to your university programme and intake.">
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div><label htmlFor="faculty" className={labelClass}>Faculty <span className="text-error-500">*</span></label><div className="relative"><Icon>account_balance</Icon><select id="faculty" name="faculty" required value={faculty} onChange={(event) => { onFacultyChange(event.target.value); onProgramChange(''); }} className={controlClass(!!errors.faculty)} aria-invalid={!!errors.faculty} aria-describedby={errors.faculty ? 'faculty-error' : undefined}><option value="">Select faculty</option>{faculties.map((item) => <option key={item.name} value={item.name}>{item.name}</option>)}</select></div><ErrorMessage id="faculty-error" message={errors.faculty} /></div>
            <div><label htmlFor="program" className={labelClass}>Course / programme <span className="text-error-500">*</span></label><div className="relative"><Icon>menu_book</Icon><select id="program" name="program" required disabled={programs.length === 0} value={program} onChange={(event) => onProgramChange(event.target.value)} className={controlClass(!!errors.program)} aria-invalid={!!errors.program} aria-describedby={errors.program ? 'program-error' : undefined}><option value="">{programs.length === 0 ? 'Select a faculty first' : 'Select programme'}</option>{programs.map((item) => <option key={item} value={item}>{item}</option>)}</select></div><ErrorMessage id="program-error" message={errors.program} /></div>
            <div><label htmlFor="year_of_study" className={labelClass}>Year of study <span className="text-error-500">*</span></label><div className="relative"><Icon>calendar_today</Icon><select id="year_of_study" name="year_of_study" required value={year_of_study} onChange={(event) => onYearOfStudyChange(event.target.value)} className={controlClass(!!errors.year_of_study)} aria-invalid={!!errors.year_of_study} aria-describedby={errors.year_of_study ? 'year-error' : undefined}><option value="">Select year</option>{[1, 2, 3, 4, 5, 6].map((year) => <option key={year} value={year}>Year {year}</option>)}</select></div><ErrorMessage id="year-error" message={errors.year_of_study} /></div>
            <div><label htmlFor="intake" className={labelClass}>Intake <span className="text-error-500">*</span></label><div className="relative"><Icon>event</Icon><select id="intake" name="intake" required value={intake} onChange={(event) => onIntakeChange(event.target.value)} className={controlClass(!!errors.intake)} aria-invalid={!!errors.intake} aria-describedby={errors.intake ? 'intake-error' : undefined}><option value="">Select intake</option><option value="january">January</option><option value="february">February</option><option value="may">May</option><option value="august">August</option></select></div><ErrorMessage id="intake-error" message={errors.intake} /></div>
            <div><label htmlFor="intake_year" className={labelClass}>Intake year <span className="text-error-500">*</span></label><div className="relative"><Icon>date_range</Icon><select id="intake_year" name="intake_year" required value={intake_year} onChange={(event) => onIntakeYearChange(event.target.value)} className={controlClass(!!errors.intake_year)} aria-invalid={!!errors.intake_year} aria-describedby="intake-year-help intake-year-error"><option value="">Select year</option>{Array.from({ length: 6 }, (_, index) => currentYear - 5 + index).map((year) => <option key={year} value={year}>{year}</option>)}</select></div><p id="intake-year-help" className="mt-1.5 text-xs text-muted-foreground">Used with your intake month to calculate membership card expiry.</p><ErrorMessage id="intake-year-error" message={errors.intake_year} /></div>
          </div>
        </Section>

        <Section icon="lock" title="Account security" description="Choose a strong password to protect your club account.">
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div><label htmlFor="password" className={labelClass}>Password <span className="text-error-500">*</span></label><div className="relative"><Icon>lock</Icon><input id="password" name="password" type={showPassword ? 'text' : 'password'} autoComplete="new-password" required value={password} onChange={(event) => onPasswordChange(event.target.value)} placeholder="Create a password" className={`${controlClass(!!errors.password)} pr-12`} aria-invalid={!!errors.password} aria-describedby="password-rules password-error" /><AuthPasswordToggle isVisible={showPassword} onToggle={() => setShowPassword(!showPassword)} /></div><div id="password-rules" className="mt-2 grid gap-1 sm:grid-cols-2">{passwordRules.map(([rule, met]) => <span key={rule} className={`flex items-center gap-1.5 text-xs ${met ? 'text-success-600 dark:text-success-400' : 'text-muted-foreground'}`}><span className="material-symbols-outlined text-[15px]" aria-hidden="true">{met ? 'check_circle' : 'radio_button_unchecked'}</span>{rule}</span>)}</div><ErrorMessage id="password-error" message={errors.password} /></div>
            <div><label htmlFor="password_confirmation" className={labelClass}>Confirm password <span className="text-error-500">*</span></label><div className="relative"><Icon>lock_reset</Icon><input id="password_confirmation" name="password_confirmation" type={showConfirmation ? 'text' : 'password'} autoComplete="new-password" required value={password_confirmation} onChange={(event) => onPasswordConfirmationChange(event.target.value)} placeholder="Repeat your password" className={`${controlClass(!!errors.password_confirmation)} pr-12`} aria-invalid={!!errors.password_confirmation} aria-describedby="password-confirmation-status password-confirmation-error" /><AuthPasswordToggle isVisible={showConfirmation} onToggle={() => setShowConfirmation(!showConfirmation)} label="password confirmation" /></div>{password_confirmation && <p id="password-confirmation-status" className={`mt-1.5 flex items-center gap-1.5 text-xs ${password === password_confirmation ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400'}`}><span className="material-symbols-outlined text-[16px]" aria-hidden="true">{password === password_confirmation ? 'check_circle' : 'error'}</span>{password === password_confirmation ? 'Passwords match' : 'Passwords do not match'}</p>}<ErrorMessage id="password-confirmation-error" message={errors.password_confirmation} /></div>
          </div>
        </Section>

        <section><label htmlFor="terms" className="flex cursor-pointer items-start gap-3 rounded-sm bg-background/80 px-3 py-2.5"><input id="terms" name="terms" type="checkbox" required checked={terms} onChange={(event) => onTermsChange(event.target.checked)} className="mt-0.5 h-4 w-4 rounded-sm border-border text-brand-600 focus:ring-brand-500/20" aria-invalid={!!errors.terms} aria-describedby={errors.terms ? 'terms-error' : undefined} /><span className="text-sm leading-5 text-muted-foreground">I agree to the club platform terms and understand that my membership requires approval. <span className="text-error-500">*</span></span></label><ErrorMessage id="terms-error" message={errors.terms} /></section>

        <div className="flex flex-col-reverse items-center gap-4 sm:flex-row sm:justify-between"><p className="text-sm text-muted-foreground">Already have an account? <Link href="/auth/login" className="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-300">Sign in</Link></p><button type="submit" disabled={processing} className="relative inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-sm bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">{processing ? <><Loader2 className="h-5 w-5 animate-spin" />Creating account…</> : <>Create account<span className="material-symbols-outlined text-[19px]" aria-hidden="true">person_add</span></>}</button></div>
      </form>
    </div>
  );
}
