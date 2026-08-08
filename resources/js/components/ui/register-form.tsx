import { Link } from '@inertiajs/react';
import { User, Mail, Phone, GraduationCap, Calendar, Lock, ArrowRight, Loader2, Eye, EyeOff, Hash, CheckCircle2, BookOpen } from 'lucide-react';
import { useState } from 'react';

export interface Faculty {
  name: string;
  programs: string[];
}

interface RegisterFormProps {
  onSubmit: (e: React.FormEvent) => void;
  faculties: Faculty[];
  name: string;
  email: string;
  registration_number: string;
  phone: string;
  program: string;
  faculty: string;
  year_of_study: string;
  intake: string;
  intake_year: string;
  password: string;
  password_confirmation: string;
  terms: boolean;
  onNameChange: (value: string) => void;
  onEmailChange: (value: string) => void;
  onRegistrationNumberChange: (value: string) => void;
  onPhoneChange: (value: string) => void;
  onProgramChange: (value: string) => void;
  onFacultyChange: (value: string) => void;
  onYearOfStudyChange: (value: string) => void;
  onIntakeChange: (value: string) => void;
  onIntakeYearChange: (value: string) => void;
  onPasswordChange: (value: string) => void;
  onPasswordConfirmationChange: (value: string) => void;
  onTermsChange: (value: boolean) => void;
  processing: boolean;
  errors: Record<string, string>;
}

export function RegisterForm({
  onSubmit, faculties, name, email, registration_number, phone,
  program, faculty, year_of_study, intake, intake_year, password, password_confirmation, terms,
  onNameChange, onEmailChange, onRegistrationNumberChange,
  onPhoneChange, onProgramChange, onFacultyChange,
  onYearOfStudyChange, onIntakeChange, onIntakeYearChange, onPasswordChange, onPasswordConfirmationChange,
  onTermsChange, processing, errors,
}: RegisterFormProps) {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);

  const programsForFaculty = faculties.find((f) => f.name === faculty)?.programs ?? [];

  const inputClass = (hasError: boolean) =>
    `h-12 w-full rounded-xl border bg-white/5 pl-11 pr-4 text-sm text-white placeholder:text-white/30 transition-all duration-200 focus:outline-none ${
      hasError
        ? 'border-red-500/50 focus:border-red-500/70 focus:ring-2 focus:ring-red-500/20'
        : 'border-white/10 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20'
    }`;

  const labelClass = 'text-sm font-medium text-white/60';
  const selectClass = (hasError: boolean) =>
    `h-12 w-full rounded-xl border bg-white/5 pl-11 pr-4 text-sm text-white focus:outline-none ${
      hasError
        ? 'border-red-500/50 focus:border-red-500/70 focus:ring-2 focus:ring-red-500/20'
        : 'border-white/10 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20'
    } [&>option]:bg-[#0f172a] [&>option]:text-white`;

  return (
    <div className="w-full max-w-md px-4 sm:px-0">
      <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 shadow-2xl shadow-black/20 backdrop-blur-2xl">
        <div className="mb-7 text-center">
          <img
            src="/images/club_logo.png"
            alt="SLAU-CSIC"
            className="mx-auto mb-4 h-11 w-auto invert brightness-[1.2] drop-shadow-[0_0_20px_rgba(99,102,241,0.4)]"
          />
          <h1 className="text-2xl font-bold text-white">Create Account</h1>
          <p className="mt-1.5 text-sm text-white/45">Join the SLAU-CSIC community</p>
        </div>

        <form onSubmit={onSubmit} className="space-y-5">
          {/* Personal Info */}
          <div className="space-y-4">
            <div className="flex items-center gap-3">
              <span className="text-xs font-semibold uppercase tracking-widest text-white/25">Personal</span>
              <div className="h-px flex-1 bg-white/8" />
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Full Name <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <User className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <input
                  type="text"
                  value={name}
                  onChange={(e) => onNameChange(e.target.value)}
                  className={inputClass(!!errors.name)}
                  placeholder="Type your full name"
                  required
                  autoFocus
                />
              </div>
              {errors.name && <p className="text-sm text-red-400">{errors.name}</p>}
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Email <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <Mail className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <input
                  type="email"
                  value={email}
                  onChange={(e) => onEmailChange(e.target.value)}
                  className={inputClass(!!errors.email)}
                  placeholder="Type your email address"
                  required
                />
              </div>
              {errors.email && <p className="text-sm text-red-400">{errors.email}</p>}
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Phone <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <Phone className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <input
                  type="tel"
                  value={phone}
                  onChange={(e) => onPhoneChange(e.target.value)}
                  className={inputClass(!!errors.phone)}
                  placeholder="Type your phone number"
                  required
                />
              </div>
              {errors.phone && <p className="text-sm text-red-400">{errors.phone}</p>}
            </div>
          </div>

          {/* Academic Info */}
          <div className="space-y-4">
            <div className="flex items-center gap-3">
              <span className="text-xs font-semibold uppercase tracking-widest text-white/25">Academic</span>
              <div className="h-px flex-1 bg-white/8" />
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Registration Number <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <Hash className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <input
                  type="text"
                  value={registration_number}
                  onChange={(e) => onRegistrationNumberChange(e.target.value)}
                  className={inputClass(!!errors.registration_number)}
                  placeholder="Type your registration number"
                  required
                />
              </div>
              <p className="text-xs text-white/25">Format: Course/Year+Mode/Country/Intake+Number</p>
              {errors.registration_number && <p className="text-sm text-red-400">{errors.registration_number}</p>}
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Faculty <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <GraduationCap className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <select
                  value={faculty}
                  onChange={(e) => {
                    onFacultyChange(e.target.value);
                    onProgramChange('');
                  }}
                  className={selectClass(!!errors.faculty)}
                  required
                >
                  <option value="">Select faculty</option>
                  {faculties.map((f) => (
                    <option key={f.name} value={f.name}>{f.name}</option>
                  ))}
                </select>
              </div>
              {errors.faculty && <p className="text-sm text-red-400">{errors.faculty}</p>}
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Programme <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <BookOpen className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <select
                  value={program}
                  onChange={(e) => onProgramChange(e.target.value)}
                  disabled={programsForFaculty.length === 0}
                  className={`${selectClass(!!errors.program)} disabled:cursor-not-allowed disabled:opacity-50`}
                  required
                >
                  <option value="">{programsForFaculty.length === 0 ? 'Select a faculty first' : 'Select programme'}</option>
                  {programsForFaculty.map((p) => (
                    <option key={p} value={p}>{p}</option>
                  ))}
                </select>
              </div>
              {errors.program && <p className="text-sm text-red-400">{errors.program}</p>}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div className="space-y-1.5">
                <label className={labelClass}>Year of Study <span className="text-indigo-400">*</span></label>
                <div className="relative">
                  <Calendar className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                  <select
                    value={year_of_study}
                    onChange={(e) => onYearOfStudyChange(e.target.value)}
                    className={selectClass(!!errors.year_of_study)}
                    required
                  >
                    <option value="">Select year</option>
                    {[1, 2, 3, 4, 5, 6].map((y) => (
                      <option key={y} value={y}>Year {y}</option>
                    ))}
                  </select>
                </div>
                {errors.year_of_study && <p className="text-sm text-red-400">{errors.year_of_study}</p>}
              </div>
              <div className="space-y-1.5">
                <label className={labelClass}>Intake <span className="text-indigo-400">*</span></label>
                <div className="relative">
                  <Calendar className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                  <select
                    value={intake}
                    onChange={(e) => onIntakeChange(e.target.value)}
                    className={selectClass(!!errors.intake)}
                    required
                  >
                    <option value="">Select intake</option>
                    <option value="august">August</option>
                    <option value="january">January</option>
                    <option value="may">May</option>
                  </select>
                </div>
                {errors.intake && <p className="text-sm text-red-400">{errors.intake}</p>}
              </div>
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Intake Year <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <Calendar className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <select
                  value={intake_year}
                  onChange={(e) => onIntakeYearChange(e.target.value)}
                  className={selectClass(!!errors.intake_year)}
                  required
                >
                  <option value="">Select year</option>
                  {Array.from(
                    { length: 6 },
                    (_, i) => new Date().getFullYear() - 5 + i,
                  ).map((year) => (
                    <option key={year} value={year}>{year}</option>
                  ))}
                </select>
              </div>
              {errors.intake_year && <p className="text-sm text-red-400">{errors.intake_year}</p>}
            </div>
            <p className="text-xs text-white/25">Intake month and year are used to calculate your membership card expiry</p>
          </div>

          {/* Security */}
          <div className="space-y-4">
            <div className="flex items-center gap-3">
              <span className="text-xs font-semibold uppercase tracking-widest text-white/25">Security</span>
              <div className="h-px flex-1 bg-white/8" />
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Password <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => onPasswordChange(e.target.value)}
                  className={`${inputClass(!!errors.password)} pr-10`}
                  placeholder="Type your password"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-2 text-white/25 transition-colors hover:bg-white/5 hover:text-white/50"
                >
                  {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
              {password.length > 0 && (
                <div className="mt-2 space-y-1">
                  {[
                    { label: 'At least 8 characters', met: password.length >= 8 },
                    { label: 'One uppercase letter', met: /[A-Z]/.test(password) },
                    { label: 'One lowercase letter', met: /[a-z]/.test(password) },
                    { label: 'One number', met: /[0-9]/.test(password) },
                    { label: 'One special character', met: /[^A-Za-z0-9]/.test(password) },
                  ].map((rule) => (
                    <div key={rule.label} className="flex items-center gap-2">
                      <CheckCircle2 className={`h-3 w-3 ${rule.met ? 'text-emerald-400' : 'text-white/15'}`} />
                      <span className={`text-xs ${rule.met ? 'text-emerald-400' : 'text-white/25'}`}>{rule.label}</span>
                    </div>
                  ))}
                </div>
              )}
              {errors.password && <p className="text-sm text-red-400">{errors.password}</p>}
            </div>

            <div className="space-y-1.5">
              <label className={labelClass}>Confirm Password <span className="text-indigo-400">*</span></label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25" />
                <input
                  type={showConfirm ? 'text' : 'password'}
                  value={password_confirmation}
                  onChange={(e) => onPasswordConfirmationChange(e.target.value)}
                  className={`${inputClass(!!errors.password_confirmation)} pr-10`}
                  placeholder="Confirm your password"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowConfirm(!showConfirm)}
                  className="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-2 text-white/25 transition-colors hover:bg-white/5 hover:text-white/50"
                >
                  {showConfirm ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
              {password_confirmation.length > 0 && (
                <div className="mt-1 flex items-center gap-2">
                  <CheckCircle2 className={`h-3 w-3 ${password === password_confirmation ? 'text-emerald-400' : 'text-white/15'}`} />
                  <span className={`text-xs ${password === password_confirmation ? 'text-emerald-400' : 'text-white/25'}`}>
                    {password === password_confirmation ? 'Passwords match' : 'Passwords do not match'}
                  </span>
                </div>
              )}
              {errors.password_confirmation && <p className="text-sm text-red-400">{errors.password_confirmation}</p>}
            </div>
          </div>

          {/* Terms */}
          <div className="space-y-3">
            <label className="flex cursor-pointer items-start gap-3 group">
              <div className="relative mt-0.5">
                <input
                  type="checkbox"
                  checked={terms}
                  onChange={(e) => onTermsChange(e.target.checked)}
                  className="peer sr-only"
                />
                <div className="h-4 w-4 rounded border border-white/20 bg-white/5 transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-500">
                  {terms && <CheckCircle2 className="h-4 w-4 text-white" />}
                </div>
              </div>
              <span className="text-xs leading-relaxed text-white/35 transition-colors group-hover:text-white/50">
                I confirm that the information provided is accurate and I agree to the club platform terms and membership review process.
              </span>
            </label>
            {errors.terms && <p className="text-sm text-red-400">{errors.terms}</p>}

            <button
              type="submit"
              disabled={processing}
              className="relative h-12 w-full rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-400 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:shadow-indigo-500/40 hover:brightness-110 active:scale-[0.98] disabled:opacity-50 disabled:active:scale-100"
            >
              <span className={`flex items-center justify-center gap-2 ${processing ? 'opacity-0' : ''}`}>
                Create Account
                <ArrowRight className="h-4 w-4" />
              </span>
              {processing && (
                <span className="absolute inset-0 flex items-center justify-center">
                  <Loader2 className="h-5 w-5 animate-spin text-white" />
                </span>
              )}
            </button>
          </div>
        </form>

        <div className="mt-5 pt-4 text-center text-xs text-white/35">
          Already have an account?{' '}
          <Link href="/auth/login" className="font-semibold text-indigo-400 transition-colors hover:text-indigo-300">
            Sign In
          </Link>
        </div>
      </div>
    </div>
  );
}
