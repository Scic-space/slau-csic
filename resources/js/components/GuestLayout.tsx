import { Link } from '@inertiajs/react';
import { type ReactNode } from 'react';

interface GuestLayoutProps {
    children: ReactNode;
}

export default function GuestLayout({ children }: GuestLayoutProps) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-50 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-950">
            <div className="mb-6">
                <Link href="/">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                        SLAU-CSIC
                    </h1>
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-900">
                {children}
            </div>
        </div>
    );
}
