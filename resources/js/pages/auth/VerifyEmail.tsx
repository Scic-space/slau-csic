import { useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/components/GuestLayout';
import { FormEventHandler } from 'react';

export default function VerifyEmail() {
    const { auth } = usePage().props;
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/auth/verify-email/resend');
    };

    return (
        <GuestLayout>
            <div className="text-center">
                <div className="mb-4 text-4xl">📧</div>
                <h2 className="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                    Verify your email
                </h2>
                <p className="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Thanks for signing up! Before getting started, could you verify your email address
                    by clicking on the link we just emailed to you? If you didn't receive the email,
                    we will gladly send you another.
                </p>

                <form onSubmit={submit} className="space-y-4">
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        {processing ? 'Sending...' : 'Resend Verification Email'}
                    </button>
                </form>

                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '/auth/logout';
                        document.body.appendChild(form);
                        form.submit();
                    }}
                    className="mt-4"
                >
                    <button
                        type="submit"
                        className="text-sm text-gray-600 underline hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                    >
                        Log out
                    </button>
                </form>
            </div>
        </GuestLayout>
    );
}
