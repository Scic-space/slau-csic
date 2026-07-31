/// <reference types="vite/client" />

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    roles: string[];
    permissions: string[];
    membership_status: string | null;
    membership_type: string | null;
    created_at: string;
    updated_at: string;
}

interface PageProps {
    auth: {
        user: User | null;
    };
    flash?: {
        success?: string;
        error?: string;
        status?: string;
    };
    errors?: Record<string, string>;
}

declare module '@inertiajs/react' {
    interface PageProps {
        auth: {
            user: User | null;
        };
        flash?: {
            success?: string;
            error?: string;
            status?: string;
        };
    }
}

declare module '@fullcalendar/react' {
    interface FullCalendarProps {
        // Extended props are handled via the default interface
    }
}
