importScripts('https://www.gstatic.com/firebasejs/8.2.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.2.0/firebase-messaging.js');

const firebaseConfig = {
    apiKey: "AIzaSyCmXZDK-epySx74Xi4eUaz2qRwVz1Kql9g",
    authDomain: "web-push-1f247.firebaseapp.com",
    projectId: "web-push-1f247",
    storageBucket: "web-push-1f247.appspot.com",
    messagingSenderId: "282728661969",
    appId: "1:282728661969:web:08803fc32ee51ebf2816f0",
    measurementId: "G-GQC9C9JQLY"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    const notificationTitle = payload.notification.title;
    const notificationOptions = { body: payload.notification.body, };
    self.registration.showNotification(notificationTitle, notificationOptions);
});
