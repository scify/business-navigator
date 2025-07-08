import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface FlashMessage {
    id: string;
    message: string;
    type: 'success' | 'error' | 'info' | 'warning';
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    app: {
        name: string;
        version: string;
        locale: string;
    };
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
    flash: FlashMessage | null;
};
