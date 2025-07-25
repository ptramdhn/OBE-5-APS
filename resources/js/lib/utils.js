import { router } from '@inertiajs/react';
import { clsx } from 'clsx';
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';
import { toast } from 'sonner';
import { twMerge } from 'tailwind-merge';

function cn(...inputs) {
    return twMerge(clsx(inputs));
}

function flashMessage(params) {
    return params.props.flas_message;
}

const deleteAction = (url, { closeModal, ...options } = {}) => {
    const defaultOptions = {
        preserveScroll: true,
        preserveState: true,

        onSuccess: (success) => {
            const flash = flashMessage(success);

            if (flash) {
                toast[flash.type](flash.message);
            }

            if (closeModal && typeof closeModal === 'function') {
                closeModal();
            }
        },
        ...options,
    };

    router.delete(url, defaultOptions);
};

const formatDateIndo = (dateString) => {
    if (!dateString) return '-';

    return format(parseISO(dateString), 'eeee, dd MMMM yyyy', {
        locale: id,
    });
};

const messages = {
    503: {
        title: 'Service Unavailable',
        description: 'Sorry, we are doing some maintenance. Please chech back soon',
        status: 503,
    },

    500: {
        title: 'Server Error',
        description: 'Oops, something went wrong',
        status: 500,
    },

    404: {
        title: 'Not Found',
        description: 'Sorry, the page you are looking for could not be found',
        status: 404,
    },

    403: {
        title: 'Forbidden',
        description: 'Sorry, you are forbidden from accessing this page',
        status: 403,
    },

    401: {
        title: 'Unauthorized',
        description: 'Sorry, you are unauthorized to access this page',
        status: 401,
    },

    429: {
        title: 'Too Many Request',
        description: 'Please try again in just a second',
        status: 429,
    },
};

export { cn, deleteAction, flashMessage, formatDateIndo, messages };
