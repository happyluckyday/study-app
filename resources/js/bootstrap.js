import 'bootstrap';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 409 && error.response.data.error === 'kicked_out') {
            // Hard reset: clear all local data
            localStorage.clear();
            sessionStorage.clear();
            
            // Show modal
            const modalHtml = `
                <div class="modal fade" id="kickedModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">系統提示</h5>
                            </div>
                            <div class="modal-body">
                                ${error.response.data.message}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="confirmKickBtn">確定</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            if (!document.getElementById('kickedModal')) {
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }
            const modal = new window.bootstrap.Modal(document.getElementById('kickedModal'));
            modal.show();
            
            document.getElementById('confirmKickBtn').addEventListener('click', () => {
                // Acknowledge kick
                axios.post('/api/session/acknowledge-kick').then(() => {
                    window.location.href = '/login';
                }).catch(() => {
                    window.location.href = '/login';
                });
            });
            
            return new Promise(() => {}); // Stop further promise chain
        }
        return Promise.reject(error);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
