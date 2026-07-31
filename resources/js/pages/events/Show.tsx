import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import PublicLayout from '@/components/PublicLayout';
import { GlowyWavesBackground } from '@/components/ui/glowy-waves-hero-shadcnui';
import { motion } from 'framer-motion';
import { ArrowLeft, Calendar, Clock, MapPin, Users, ExternalLink, DollarSign } from 'lucide-react';
import type { PageProps as InertiaPageProps } from '@inertiajs/react';

interface EventData {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    type: string;
    start_date: string;
    end_date: string | null;
    location: string | null;
    banner_image: string | null;
    max_participants: number | null;
    registration_required: boolean;
    waitlist_enabled: boolean;
    registration_deadline: string | null;
    is_public: boolean;
    status: string;
    requirements: string | null;
    registration_fee: number | null;
    external_link: string | null;
    is_recurring: boolean;
    registered_count: number;
    is_full: boolean;
    remaining_spots: number;
    organizer: { id: number; name: string } | null;
    categories: { id: number; name: string; slug: string; color: string }[];
    instructors: { id: number; name: string; role: string }[];
    resources: { id: number; title: string; type: string; url: string | null }[];
    user_registration: {
        id: number;
        status: string;
        rsvp_status: string | null;
        registered_at: string | null;
        waitlisted_at: string | null;
    } | null;
    can_submit_feedback: boolean;
    user_feedback: {
        rating: number;
        content_quality: number | null;
        instructor_rating: number | null;
        pace_rating: number | null;
        feedback_text: string | null;
    } | null;
}

interface PageProps extends InertiaPageProps {
    event: EventData;
    categories: { id: number; name: string; slug: string; color: string }[];
}

const typeLabels: Record<string, string> = {
    workshop: 'Workshop', competition: 'Competition', ctf: 'CTF',
    bootcamp: 'Bootcamp', awareness_campaign: 'Awareness Campaign',
    talk: 'Talk/Seminar', social: 'Social', hackathon: 'Hackathon',
};

const statusColors: Record<string, string> = {
    scheduled: 'bg-blue-500/20 text-blue-300',
    published: 'bg-green-500/20 text-green-300',
    ongoing: 'bg-yellow-500/20 text-yellow-300',
    completed: 'bg-white/10 text-white/50',
    cancelled: 'bg-red-500/20 text-red-300',
};

export default function EventShow() {
    const { event, auth } = usePage<PageProps>().props;
    const [confirmingCancel, setConfirmingCancel] = useState(false);
    const [registering, setRegistering] = useState(false);
    const [rsvpLoading, setRsvpLoading] = useState(false);

    const isAuthenticated = !!auth.user;
    const isOrganizer = isAuthenticated && auth.user?.id === event.organizer?.id;
    const isAdmin = isAuthenticated && auth.user?.roles?.includes('admin');
    const canEdit = isOrganizer || isAdmin;

    const registration = event.user_registration;
    const isRegistered = registration?.status === 'registered';
    const isWaitlisted = registration?.status === 'waitlist';
    const isAttending = registration?.rsvp_status === 'attending';

    const formatDate = (dateStr: string) =>
        new Date(dateStr).toLocaleDateString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric',
            year: 'numeric', hour: '2-digit', minute: '2-digit',
        });

    const formatDateShort = (dateStr: string) =>
        new Date(dateStr).toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
        });

    const [feedbackOpen, setFeedbackOpen] = useState(false);
    const [feedbackData, setFeedbackData] = useState({
        rating: 5,
        content_quality: 0,
        instructor_rating: 0,
        pace_rating: 0,
        feedback_text: '',
        suggestions: '',
        is_anonymous: false,
    });
    const [submittingFeedback, setSubmittingFeedback] = useState(false);
    const [feedbackError, setFeedbackError] = useState<string | null>(null);
    const stars = (n: number) => '★'.repeat(n) + '☆'.repeat(5 - n);

    const renderStars = (n: number | null) => {
        return (
            <span className="text-yellow-400">
                {stars(n ?? 0)}
            </span>
        );
    };

    const submitFeedback = () => {
        setSubmittingFeedback(true);
        setFeedbackError(null);
        router.post(`/events/${event.slug}/feedback`, feedbackData, {
            preserveScroll: true,
            onSuccess: () => {
                setSubmittingFeedback(false);
                setFeedbackOpen(false);
            },
            onError: (errors) => {
                setSubmittingFeedback(false);
                setFeedbackError(Object.values(errors).join(', '));
            },
        });
    };

    const submitRsvp = () => {
        setRsvpLoading(true);
        router.post(`/events/${event.slug}/rsvp`, {}, {
            preserveScroll: true,
            onSuccess: () => { setConfirmingCancel(false); setRsvpLoading(false); },
            onError: () => setRsvpLoading(false),
        });
    };

    const cancelRsvp = () => {
        setRsvpLoading(true);
        router.post(`/events/${event.slug}/cancel-rsvp`, {}, {
            preserveScroll: true,
            onSuccess: () => { setConfirmingCancel(false); setRsvpLoading(false); },
            onError: () => setRsvpLoading(false),
        });
    };

    const submitRegister = () => {
        setRegistering(true);
        router.post(`/events/${event.slug}/register`, {}, {
            preserveScroll: true,
            onFinish: () => setRegistering(false),
        });
    };

    const submitUnregister = () => {
        setRegistering(true);
        router.post(`/events/${event.slug}/unregister`, {}, {
            preserveScroll: true,
            onFinish: () => setRegistering(false),
        });
    };

    return (
        <PublicLayout transparentNav>
            <GlowyWavesBackground>
                <section className="relative flex w-full items-center justify-center px-6 pt-28 pb-16 md:px-8 lg:px-12">
                    <div className="mx-auto w-full max-w-5xl">
                        <motion.div
                            initial={{ opacity: 0, y: 16 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.4 }}
                        >
                            <Link
                                href="/events"
                                className="mb-8 inline-flex items-center gap-1.5 text-sm text-white/60 transition-colors hover:text-white"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Back to Events
                            </Link>
                        </motion.div>

                        {event.banner_image && (
                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.05 }}
                                className="relative mb-8 overflow-hidden rounded-2xl border border-white/10"
                            >
                                <div
                                    className="h-64 bg-cover bg-center"
                                    style={{ backgroundImage: `url(/storage/${event.banner_image})` }}
                                />
                                <div className="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent" />
                            </motion.div>
                        )}

                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.5, delay: 0.1 }}
                            className="rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm"
                        >
                            <div className="p-6 md:p-8">
                                <div className="flex items-start justify-between gap-4 mb-6">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-3 mb-2">
                                            {event.categories.map((cat) => (
                                                <span
                                                    key={cat.id}
                                                    className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                                    style={{ backgroundColor: `${cat.color}20`, color: cat.color }}
                                                >
                                                    <span className="w-2 h-2 rounded-full" style={{ backgroundColor: cat.color }} />
                                                    {cat.name}
                                                </span>
                                            ))}
                                        </div>
                                        <h1 className="text-3xl font-bold text-white">{event.title}</h1>
                                    </div>
                                    <div className="flex items-center gap-2 shrink-0">
                                        {canEdit && (
                                            <Link
                                                href={`/events/${event.slug}/edit`}
                                                className="inline-flex items-center gap-1 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-sm font-medium text-white/80 hover:bg-white/10"
                                            >
                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </Link>
                                        )}
                                        <span className={`px-3 py-1 rounded-full text-sm font-medium ${statusColors[event.status] || 'bg-white/10 text-white/70'}`}>
                                            {event.status.charAt(0).toUpperCase() + event.status.slice(1)}
                                        </span>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="md:col-span-2 space-y-6">
                                        {event.description && (
                                            <div>
                                                <h2 className="text-lg font-semibold text-white mb-2">About</h2>
                                                <div className="prose prose-invert text-white/60 leading-relaxed max-w-none" dangerouslySetInnerHTML={{ __html: event.description }} />
                                            </div>
                                        )}

                                        {event.requirements && (
                                            <div>
                                                <h2 className="text-lg font-semibold text-white mb-2">Requirements</h2>
                                                <div className="prose prose-invert text-white/60 leading-relaxed max-w-none" dangerouslySetInnerHTML={{ __html: event.requirements }} />
                                            </div>
                                        )}

                                        {event.instructors.length > 0 && (
                                            <div>
                                                <h2 className="text-lg font-semibold text-white mb-3">Instructors</h2>
                                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    {event.instructors.map((inst) => (
                                                        <div key={inst.id} className="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-3">
                                                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/20 text-sm font-semibold text-indigo-300">
                                                                {inst.name.charAt(0)}
                                                            </div>
                                                            <div>
                                                                <p className="text-sm font-medium text-white">{inst.name}</p>
                                                                <p className="text-xs text-white/50">{inst.role}</p>
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {event.resources.length > 0 && (
                                            <div>
                                                <h2 className="text-lg font-semibold text-white mb-3">Resources</h2>
                                                <div className="space-y-2">
                                                    {event.resources.map((res) => (
                                                        <div key={res.id} className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                                                            <p className="text-sm font-medium text-white">{res.title}</p>
                                                            <p className="text-xs text-white/50 mb-1">{res.type}</p>
                                                            {res.url && (
                                                                <a href={res.url} target="_blank" rel="noopener noreferrer"
                                                                    className="text-sm text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1">
                                                                    View Resource <ExternalLink className="h-3 w-3" />
                                                                </a>
                                                            )}
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    <div className="space-y-4">
                                        <div className="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                                            <h3 className="font-semibold text-white mb-3">Details</h3>
                                            <div className="space-y-3 text-sm">
                                                <div className="flex items-center gap-2 text-white/60">
                                                    <Calendar className="w-4 h-4 shrink-0" />
                                                    <span className="px-2 py-0.5 rounded text-xs font-medium bg-white/10 text-white/70">
                                                        {typeLabels[event.type] || event.type}
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-2 text-white/60">
                                                    <Clock className="w-4 h-4 shrink-0" />
                                                    <span>{formatDate(event.start_date)}</span>
                                                </div>
                                                {event.end_date && (
                                                    <div className="flex items-center gap-2 text-white/60">
                                                        <Clock className="w-4 h-4 shrink-0" />
                                                        <span>Until {formatDate(event.end_date)}</span>
                                                    </div>
                                                )}
                                                {event.location && (
                                                    <div className="flex items-center gap-2 text-white/60">
                                                        <MapPin className="w-4 h-4 shrink-0" />
                                                        <span>{event.location}</span>
                                                    </div>
                                                )}
                                                {event.is_recurring && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-purple-500/20 px-2 py-0.5 text-xs font-medium text-purple-300">
                                                        Recurring
                                                    </span>
                                                )}
                                                {event.registration_fee > 0 && (
                                                    <div className="flex items-center gap-2 text-white/60">
                                                        <DollarSign className="w-4 h-4 shrink-0" />
                                                        <span>Fee: UGX {event.registration_fee.toLocaleString()}</span>
                                                    </div>
                                                )}
                                                {event.max_participants && (
                                                    <div>
                                                        <div className="flex items-center justify-between text-sm mb-1">
                                                            <span className="text-white/60">Capacity</span>
                                                            <span className={`font-medium ${event.is_full ? 'text-red-400' : 'text-green-400'}`}>
                                                                {event.registered_count}/{event.max_participants}
                                                            </span>
                                                        </div>
                                                        <div className="h-2 w-full rounded-full bg-white/10">
                                                            <div
                                                                className={`h-2 rounded-full ${event.is_full ? 'bg-red-500' : 'bg-green-500'}`}
                                                                style={{ width: `${(event.registered_count / event.max_participants) * 100}%` }}
                                                            />
                                                        </div>
                                                        <p className="text-xs text-white/50 mt-1">
                                                            {event.remaining_spots} spot{event.remaining_spots !== 1 ? 's' : ''} remaining
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                        </div>

                                        {event.external_link && (
                                            <a href={event.external_link} target="_blank" rel="noopener noreferrer"
                                                className="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                                Register on External Site <ExternalLink className="h-4 w-4" />
                                            </a>
                                        )}

                                        {event.registration_required && !event.external_link && (
                                            <div className="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                                                {!isAuthenticated ? (
                                                    <div className="text-center">
                                                        <p className="text-sm text-white/60 mb-3">Login to register</p>
                                                        <Link href="/auth/login"
                                                            className="block rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 text-center">
                                                            Login
                                                        </Link>
                                                    </div>
                                                ) : isRegistered ? (
                                                    <div className="text-center">
                                                        <div className="mb-2">
                                                            <svg className="w-12 h-12 mx-auto text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2a1 1 0 00-1.414-1.414L10 11.586l-1.293-1.293z" clipRule="evenodd" />
                                                            </svg>
                                                        </div>
                                                        <p className="font-semibold text-white mb-1">You're Registered!</p>
                                                        <p className="text-xs text-white/50 mb-3">
                                                            Registered {event.user_registration?.registered_at ? formatDateShort(event.user_registration.registered_at) : ''}
                                                        </p>
                                                        <button onClick={submitUnregister} disabled={registering}
                                                            className="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500 disabled:opacity-50">
                                                            {registering ? 'Processing...' : 'Cancel Registration'}
                                                        </button>
                                                    </div>
                                                ) : isWaitlisted ? (
                                                    <div className="text-center">
                                                        <div className="mb-2">
                                                            <svg className="w-12 h-12 mx-auto text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                                            </svg>
                                                        </div>
                                                        <p className="font-semibold text-white mb-1">On the Waitlist</p>
                                                        <p className="text-xs text-white/50 mb-3">You'll be auto-promoted if a spot opens.</p>
                                                        <button onClick={submitUnregister} disabled={registering}
                                                            className="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500 disabled:opacity-50">
                                                            {registering ? 'Processing...' : 'Leave Waitlist'}
                                                        </button>
                                                    </div>
                                                ) : (
                                                    <div className="text-center">
                                                        <p className="text-sm text-white/60 mb-1">
                                                            {event.is_full && event.waitlist_enabled
                                                                ? 'Event is full — join the waitlist'
                                                                : event.is_full
                                                                    ? 'This event is full'
                                                                    : `${event.remaining_spots} spot${event.remaining_spots !== 1 ? 's' : ''} remaining`
                                                            }
                                                        </p>
                                                        {event.registration_deadline && (
                                                            <p className="text-xs text-white/50 mb-3">
                                                                Deadline: {formatDate(event.registration_deadline)}
                                                            </p>
                                                        )}
                                                        {event.is_full && !event.waitlist_enabled ? (
                                                            <p className="text-sm font-medium text-red-400">This event is full</p>
                                                        ) : (
                                                        <button onClick={submitRegister} disabled={registering}
                                                            className={`mt-2 rounded-xl px-4 py-2 text-sm font-semibold text-white disabled:opacity-50 ${
                                                                event.is_full
                                                                    ? 'bg-amber-500 hover:bg-amber-400'
                                                                    : 'bg-indigo-600 hover:bg-indigo-500'
                                                            }`}>
                                                            {registering ? 'Processing...' : event.is_full ? 'Join Waitlist' : 'Register Now'}
                                                        </button>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        )}

                                        <div className="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                                            <h3 className="font-semibold text-white mb-3">RSVP</h3>
                                            {!isAuthenticated ? (
                                                <p className="text-sm text-white/50">Login to RSVP</p>
                                            ) : isAttending ? (
                                                <div>
                                                    <div className="flex items-center gap-2 mb-3">
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-green-500/20 px-3 py-1 text-sm font-medium text-green-300">
                                                            Going
                                                        </span>
                                                    </div>
                                                    {confirmingCancel ? (
                                                        <div className="space-y-2 rounded-xl border border-white/10 bg-white/[0.03] p-3">
                                                            <p className="text-sm text-white/70">Cancel your RSVP?</p>
                                                            <div className="flex gap-2">
                                                                <button onClick={cancelRsvp} disabled={rsvpLoading}
                                                                    className="flex-1 rounded-xl bg-red-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-600 disabled:opacity-50">
                                                                    {rsvpLoading ? 'Processing...' : 'Yes, Cancel'}
                                                                </button>
                                                                <button onClick={() => setConfirmingCancel(false)} disabled={rsvpLoading}
                                                                    className="flex-1 rounded-xl bg-white/10 px-3 py-1.5 text-sm font-medium text-white/70 hover:bg-white/20 disabled:opacity-50">
                                                                    Keep
                                                                </button>
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <button onClick={() => setConfirmingCancel(true)}
                                                            className="text-sm text-white/60 hover:text-white">
                                                            Can't Go
                                                        </button>
                                                    )}
                                                </div>
                                            ) : event.is_full && !event.waitlist_enabled ? (
                                                <p className="text-sm font-medium text-red-400">Event Full</p>
                                            ) : (
                                                <button onClick={submitRsvp} disabled={rsvpLoading}
                                                    className="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                                                    {rsvpLoading ? 'Processing...' : 'RSVP'}
                                                </button>
                                            )}
                                        </div>

                                        {event.user_feedback ? (
                                            <div className="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                                                <h3 className="font-semibold text-white mb-3">Your Feedback</h3>
                                                <div className="space-y-2 text-sm">
                                                    <div className="flex justify-between">
                                                        <span className="text-white/60">Overall</span>
                                                        {renderStars(event.user_feedback.rating)}
                                                    </div>
                                                    {event.user_feedback.content_quality && (
                                                        <div className="flex justify-between">
                                                            <span className="text-white/60">Content</span>
                                                            {renderStars(event.user_feedback.content_quality)}
                                                        </div>
                                                    )}
                                                    {event.user_feedback.feedback_text && (
                                                        <p className="text-white/80 italic mt-2">"{event.user_feedback.feedback_text}"</p>
                                                    )}
                                                </div>
                                            </div>
                                        ) : event.can_submit_feedback && (
                                            <div className="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                                                {!feedbackOpen ? (
                                                    <button onClick={() => setFeedbackOpen(true)}
                                                        className="w-full rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                                        Leave Feedback
                                                    </button>
                                                ) : (
                                                    <div>
                                                        <div className="flex items-center justify-between mb-3">
                                                            <h3 className="font-semibold text-white">Share Your Feedback</h3>
                                                            <button onClick={() => setFeedbackOpen(false)}
                                                                className="text-xs text-white/50 hover:text-white">Cancel</button>
                                                        </div>
                                                        <div className="space-y-3">
                                                            <div>
                                                                <label className="block text-sm text-white/60 mb-1">Overall Rating *</label>
                                                                <div className="flex gap-1">
                                                                    {[1, 2, 3, 4, 5].map(n => (
                                                                        <button key={n} onClick={() => setFeedbackData(f => ({ ...f, rating: n }))}
                                                                            className={`text-xl ${n <= feedbackData.rating ? 'text-yellow-400' : 'text-white/20'} hover:text-yellow-300 transition-colors`}>
                                                                            ★
                                                                        </button>
                                                                    ))}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label className="block text-sm text-white/60 mb-1">Content Quality</label>
                                                                <div className="flex gap-1">
                                                                    {[1, 2, 3, 4, 5].map(n => (
                                                                        <button key={n} onClick={() => setFeedbackData(f => ({ ...f, content_quality: n }))}
                                                                            className={`text-xl ${n <= feedbackData.content_quality ? 'text-yellow-400' : 'text-white/20'} hover:text-yellow-300 transition-colors`}>
                                                                            ★
                                                                        </button>
                                                                    ))}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label className="block text-sm text-white/60 mb-1">Instructor</label>
                                                                <div className="flex gap-1">
                                                                    {[1, 2, 3, 4, 5].map(n => (
                                                                        <button key={n} onClick={() => setFeedbackData(f => ({ ...f, instructor_rating: n }))}
                                                                            className={`text-xl ${n <= feedbackData.instructor_rating ? 'text-yellow-400' : 'text-white/20'} hover:text-yellow-300 transition-colors`}>
                                                                            ★
                                                                        </button>
                                                                    ))}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label className="block text-sm text-white/60 mb-1">Pace</label>
                                                                <div className="flex gap-1">
                                                                    {[1, 2, 3, 4, 5].map(n => (
                                                                        <button key={n} onClick={() => setFeedbackData(f => ({ ...f, pace_rating: n }))}
                                                                            className={`text-xl ${n <= feedbackData.pace_rating ? 'text-yellow-400' : 'text-white/20'} hover:text-yellow-300 transition-colors`}>
                                                                            ★
                                                                        </button>
                                                                    ))}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label className="block text-sm text-white/60 mb-1">Comments</label>
                                                                <textarea value={feedbackData.feedback_text} onChange={e => setFeedbackData(f => ({ ...f, feedback_text: e.target.value }))}
                                                                    className="w-full rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2 text-sm text-white placeholder-white/40 resize-none"
                                                                    rows={3} placeholder="What did you think of the event?" />
                                                            </div>
                                                            <div>
                                                                <label className="block text-sm text-white/60 mb-1">Suggestions</label>
                                                                <textarea value={feedbackData.suggestions} onChange={e => setFeedbackData(f => ({ ...f, suggestions: e.target.value }))}
                                                                    className="w-full rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2 text-sm text-white placeholder-white/40 resize-none"
                                                                    rows={2} placeholder="Any suggestions for improvement?" />
                                                            </div>
                                                            <label className="flex items-center gap-2 text-sm text-white/60 cursor-pointer">
                                                                <input type="checkbox" checked={feedbackData.is_anonymous} onChange={e => setFeedbackData(f => ({ ...f, is_anonymous: e.target.checked }))}
                                                                    className="rounded border-white/20 bg-white/[0.04] text-indigo-600" />
                                                                Submit anonymously
                                                            </label>
                                                            {feedbackError && (
                                                                <p className="text-sm text-red-400">{feedbackError}</p>
                                                            )}
                                                            <button onClick={submitFeedback} disabled={submittingFeedback}
                                                                className="w-full rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                                                                {submittingFeedback ? 'Submitting...' : 'Submit Feedback'}
                                                            </button>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </motion.div>
                    </div>
                </section>
            </GlowyWavesBackground>
        </PublicLayout>
    );
}
