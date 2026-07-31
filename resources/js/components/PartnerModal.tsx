import { X, ArrowRight, Handshake } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useState, FormEvent } from 'react';

interface PartnerModalProps {
    onClose: () => void;
}

export default function PartnerModal({ onClose }: PartnerModalProps) {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [organization, setOrganization] = useState('');
    const [interest, setInterest] = useState('');
    const [message, setMessage] = useState('');
    const [submitted, setSubmitted] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setSubmitting(true);

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            await fetch('/contact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'text/html,application/xhtml+xml',
                },
                body: JSON.stringify({
                    name,
                    email,
                    topic: 'Collaboration or partnership',
                    message: `[Organization: ${organization}] [Interest: ${interest}] ${message}`,
                }),
            });
            setSubmitted(true);
        } catch {
            setSubmitted(true);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" onClick={onClose}>
            <div
                className="relative max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-2xl"
                onClick={(e) => e.stopPropagation()}
            >
                <button onClick={onClose} className="absolute right-4 top-4 z-10 rounded-lg p-1 text-gray-400 hover:text-white hover:bg-white/10 transition-colors">
                    <X className="h-5 w-5" />
                </button>

                <div className="h-1 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500" />

                {submitted ? (
                    <div className="p-10 text-center space-y-4">
                        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-green-500/20 bg-green-500/[0.08]">
                            <Handshake className="h-7 w-7 text-green-400" />
                        </div>
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white">Thank you!</h2>
                        <p className="text-sm text-gray-500 dark:text-white/50">
                            We've received your partnership inquiry. Our team will reach out to you within 48 hours.
                        </p>
                        <Button onClick={onClose} variant="outline" className="mt-2 rounded-full border-gray-300 dark:border-white/20 bg-gray-100 dark:bg-white/5 px-6 py-3 text-sm text-gray-700 dark:text-white/80">
                            Close
                        </Button>
                    </div>
                ) : (
                    <form onSubmit={handleSubmit} className="p-6 space-y-5">
                        <div>
                            <h2 className="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Partner with SLAU CSIC</h2>
                            <p className="mt-1 text-sm text-gray-500 dark:text-white/50">
                                Collaborate with East Africa's student cybersecurity community.
                            </p>
                        </div>

                        <div className="space-y-4">
                            <div>
                                <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-white/40">Your Name</label>
                                <input
                                    type="text"
                                    required
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.03] px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/30 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                    placeholder="e.g. Jane Mwangi"
                                />
                            </div>

                            <div>
                                <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-white/40">Email</label>
                                <input
                                    type="email"
                                    required
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                    className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.03] px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/30 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                    placeholder="you@company.com"
                                />
                            </div>

                            <div>
                                <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-white/40">Organization</label>
                                <input
                                    type="text"
                                    required
                                    value={organization}
                                    onChange={(e) => setOrganization(e.target.value)}
                                    className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.03] px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/30 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                    placeholder="Company or institution name"
                                />
                            </div>

                            <div>
                                <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-white/40">Area of Interest</label>
                                <select
                                    required
                                    value={interest}
                                    onChange={(e) => setInterest(e.target.value)}
                                    className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.03] px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                >
                                    <option value="" disabled className="bg-white text-gray-900 dark:bg-[#0f172a] dark:text-white">Select an option</option>
                                    <option value="sponsorship" className="bg-white text-gray-900 dark:bg-[#0f172a] dark:text-white">Event Sponsorship</option>
                                    <option value="mentorship" className="bg-white text-gray-900 dark:bg-[#0f172a] dark:text-white">Mentorship & Guest Speaking</option>
                                    <option value="internship" className="bg-white text-gray-900 dark:bg-[#0f172a] dark:text-white">Internship & Job Opportunities</option>
                                    <option value="hackathon" className="bg-white text-gray-900 dark:bg-[#0f172a] dark:text-white">Hackathon / CTF Sponsorship</option>
                                    <option value="training" className="bg-white text-gray-900 dark:bg-[#0f172a] dark:text-white">Training & Certification Partnership</option>
                                    <option value="other" className="bg-white text-gray-900 dark:bg-[#0f172a] dark:text-white">Other</option>
                                </select>
                            </div>

                            <div>
                                <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-white/40">Message</label>
                                <textarea
                                    rows={3}
                                    value={message}
                                    onChange={(e) => setMessage(e.target.value)}
                                    className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.03] px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/30 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors resize-none"
                                    placeholder="Tell us about your partnership idea..."
                                />
                            </div>
                        </div>

                        <Button type="submit" className="w-full group gap-2 rounded-full px-6 py-5 text-sm uppercase tracking-[0.2em] bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25">
                            Submit Inquiry
                            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </Button>
                    </form>
                )}
            </div>
        </div>
    );
}
