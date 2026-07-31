import { Link, usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';
import { NavBar } from '@/components/ui/tubelight-navbar';
import { MinimalFooter } from '@/components/ui/minimal-footer';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

interface PageProps {
    auth?: {
        user?: AuthUser | null;
    };
}

interface PublicLayoutProps {
    children: ReactNode;
    title?: string;
    transparentNav?: boolean;
}

export default function PublicLayout({ children, title, transparentNav }: PublicLayoutProps) {
    const { auth } = usePage<PageProps>().props;
    const user = auth?.user;

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-950">
            <nav className={transparentNav
                ? 'fixed top-0 left-0 right-0 z-50 bg-transparent'
                : 'border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900'
            }>
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-6 lg:gap-10">
                        <a href={user ? '/dashboard' : '/'}>
                            <img
                                src="/images/club_logo.png"
                                alt="SLAU-CSIC"
                                className={`h-14 w-auto ${transparentNav ? 'brightness-0 invert' : ''}`}
                            />
                        </a>
                        <NavBar
                            items={[
                                { name: user ? 'Dashboard' : 'Home', url: user ? '/dashboard' : '/' },
                                { name: 'CTF Arena', url: user ? '/ctf' : '/ctf-arena' },
                                { name: 'Projects', url: '/projects' },
                                { name: 'News', url: '/news' },
                                { name: 'Leaderboard', url: '/leaderboard' },
                            ]}
                            className={transparentNav ? 'text-white' : ''}
                        />
                    </div>

                    <div className="flex items-center gap-3">
                        {user ? (
                            <>
                                <a
                                    href="/dashboard"
                                    className={`text-sm font-medium ${
                                        transparentNav
                                            ? 'text-white/80 hover:text-white'
                                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                                    }`}
                                >
                                    {user.name}
                                </a>
                                <Link
                                    href="/auth/logout"
                                    method="post"
                                    as="button"
                                    className={`rounded-lg px-4 py-2 text-sm font-semibold ${
                                        transparentNav
                                            ? 'bg-white/10 text-white hover:bg-white/20'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                    }`}
                                >
                                    Logout
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link
                                    href="/auth/login"
                                    className={`text-sm font-medium ${
                                        transparentNav
                                            ? 'text-white/80 hover:text-white'
                                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                                    }`}
                                >
                                    Sign In
                                </Link>
                                <Link
                                    href="/auth/register"
                                    className={`rounded-lg px-4 py-2 text-sm font-semibold ${
                                        transparentNav
                                            ? 'bg-white/10 text-white hover:bg-white/20'
                                            : 'bg-indigo-600 text-white hover:bg-indigo-500'
                                    }`}
                                >
                                    Join Us
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            <main className={transparentNav ? 'pt-0' : ''}>
                {title && (
                    <div className="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{title}</h1>
                        </div>
                    </div>
                )}
                {children}
            </main>

            <MinimalFooter />
        </div>
    );
}
