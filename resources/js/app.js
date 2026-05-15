import './bootstrap';

// ── Delete confirmation & sidebar ──────────────────────────────────────────
document.addEventListener('click', (event) => {
    const deleteTrigger = event.target.closest('[data-confirm-delete]');
    if (deleteTrigger && !window.confirm(deleteTrigger.dataset.confirmDelete)) {
        event.preventDefault();
    }

    const openButton  = event.target.closest('[data-sidebar-open]');
    const closeButton = event.target.closest('[data-sidebar-close]');
    const sidebar     = document.getElementById('adminSidebar');
    if (!sidebar) return;
    if (openButton)  sidebar.classList.add('is-open');
    if (closeButton) sidebar.classList.remove('is-open');
});

// ── Live notification badge ────────────────────────────────────────────────
const notifBadge = document.getElementById('notifBadge');
const userId     = window.__userId ?? null;   // injected by navbar

if (userId && window.Echo && notifBadge) {
    window.Echo.private(`user.${userId}`)
        .listen('.notification.sent', () => {
            const current = parseInt(notifBadge.textContent || '0', 10);
            notifBadge.textContent = current + 1;
            notifBadge.classList.remove('hidden');
        });
}

// ── FCM Web Push opt-in ────────────────────────────────────────────────────
async function initFcm() {
    if (!('serviceWorker' in navigator) || !('Notification' in window)) return;

    // Only prompt if not already denied
    if (Notification.permission === 'denied') return;

    // Register service worker
    const reg = await navigator.serviceWorker.register('/firebase-messaging-sw.js').catch(() => null);
    if (!reg) return;

    if (Notification.permission !== 'granted') {
        const perm = await Notification.requestPermission();
        if (perm !== 'granted') return;
    }

    // Dynamically import Firebase (only when FCM opted in)
    const { initializeApp } = await import('https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js');
    const { getMessaging, getToken } = await import('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging.js');

    const firebaseConfig = {
        apiKey:            import.meta.env.VITE_FIREBASE_API_KEY,
        authDomain:        import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
        projectId:         import.meta.env.VITE_FIREBASE_PROJECT_ID,
        messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
        appId:             import.meta.env.VITE_FIREBASE_APP_ID,
    };

    // Skip if Firebase is not configured
    if (!firebaseConfig.apiKey || firebaseConfig.apiKey.startsWith('your-')) return;

    const app       = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    const token = await getToken(messaging, {
        vapidKey:            import.meta.env.VITE_FIREBASE_VAPID_KEY,
        serviceWorkerRegistration: reg,
    }).catch(() => null);

    if (token) {
        // Save token to backend
        window.axios.post('/fcm/token', { token }).catch(() => {});
    }
}

// Run FCM init for authenticated pages
if (userId) {
    initFcm();
}
