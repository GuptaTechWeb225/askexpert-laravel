importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyDtOpInxjGIj2cb9QMjb2atPY9BVVxTHcU",
    authDomain: "buiobites.firebaseapp.com",
    projectId: "buiobites",
    storageBucket: "buiobites.firebasestorage.app",
    messagingSenderId: "1039474110878",
    appId: "1:1039474110878:web:256ae2e2577e68a3e2a68f",
    measurementId: "G-QEN92FRHSX"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});