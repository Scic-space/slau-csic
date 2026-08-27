import { Link, usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';
import { NavBar } from '@/components/ui/tubelight-navbar';
import { MinimalFooter } from '@/components/ui/minimal-footer';
import { ThemeToggle } from '@/components/ui/theme-toggle';

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
        <div className="min-h-screen overflow-x-hidden bg-background font-sans text-foreground">
            <nav className="fixed inset-x-0 top-0 z-50 border-b border-border bg-card/95 shadow-theme-xs backdrop-blur-xl">
                <div className="grid h-16 w-full grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-2 px-4 sm:gap-3 sm:px-6 lg:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] lg:gap-10 lg:px-8">
                    <div className="flex min-w-0 items-center justify-self-start">
                        <a href={user ? '/dashboard' : '/'} className="flex shrink-0 items-center gap-2">
                            <img
                                src="/images/club_logo.png"
                                alt="SLAU-CSIC"
                            className="h-10 w-auto dark:brightness-0 dark:invert"
                            />
                            <span className="hidden text-sm font-bold leading-tight text-foreground sm:block">SCIC <span className="block text-xs font-medium text-muted-foreground">Cyber</span></span>
                        </a>
                    </div>

                    <NavBar
                        className="justify-self-center"
                        items={[
                            { name: user ? 'Dashboard' : 'Home', url: user ? '/dashboard' : '/' },
                            { name: 'About', url: '/about' },
                            { name: 'Events', url: '/events' },
                            { name: 'Courses', url: '/workshops' },
                            { name: 'CTF Arena', url: user ? '/ctf' : '/ctf-arena' },
                            { name: 'Contact', url: '/contact' },
                            ...(!user ? [
                                { name: 'Sign In', url: '/auth/login' },
                                { name: 'Join Us', url: '/auth/register' },
                            ] : []),
                        ]}
                    />

                    <div className="flex items-center justify-self-end gap-2 sm:gap-3">
                        <ThemeToggle />
                        {user ? (
                            <>
                                <a
                                    href="/dashboard"
                                    className="inline-flex min-h-10 items-center text-sm font-medium text-muted-foreground transition-colors duration-200 hover:text-foreground"
                                >
                                    {user.name}
                                </a>
                                <Link
                                    href="/auth/logout"
                                    method="post"
                                    as="button"
                                    className="hidden min-h-10 items-center rounded-sm border border-border px-4 py-2 text-sm font-semibold text-foreground transition-colors duration-200 hover:bg-card-hover sm:inline-flex"
                                >
                                    Logout
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link
                                    href="/auth/login"
                                    className="hidden min-h-10 items-center text-sm font-medium text-muted-foreground transition-colors duration-200 hover:text-foreground lg:inline-flex"
                                >
                                    Sign In
                                </Link>
                                <Link
                                    href="/auth/register"
                                    className="hidden min-h-10 items-center rounded-sm bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-brand-700 sm:inline-flex"
                                >
                                    Join Us
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            <main className={transparentNav ? '' : 'pt-16'}>
                {title && (
                    <div className="border-b border-border bg-card">
                        <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                            <h1 className="text-3xl font-bold text-foreground">{title}</h1>
                        </div>
                    </div>
                )}
                {children}
            </main>

            <MinimalFooter />
        </div>
    );
}
