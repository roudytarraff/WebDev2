// Firebase Cloud Messaging Service Worker
// Handles background push notifications when the tab is not focused.
// Replace the config values below with your actual Firebase project credentials.

importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey:            self.__FIREBASE_API_KEY__            || 'your-web-api-key',
    authDomain:        self.__FIREBASE_AUTH_DOMAIN__        || 'your-project.firebaseapp.com',
    projectId:         self.__FIREBASE_PROJECT_ID__        || 'your-firebase-project-id',
    messagingSenderId: self.__FIREBASE_MESSAGING_SENDER_ID__ || 'your-sender-id',
    appId:             self.__FIREBASE_APP_ID__             || 'your-app-id',
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    const { title, body, icon } = payload.notification ?? {};

    self.registration.showNotification(title ?? 'E-Services', {
        body:  body  ?? 'You have a new notification.',
        icon:  icon  ?? '/favicon.ico',
        badge: '/favicon.ico',
        data:  payload.data ?? {},
    });
});

// Click on notification opens the app
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url ?? '/';
    event.waitUntil(clients.openWindow(url));
});
