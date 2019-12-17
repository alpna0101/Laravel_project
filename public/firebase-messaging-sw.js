importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
  'messagingSenderId': '929512786508'
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  if(payload && payload.data && payload.data.notification) {
    var notificationData = JSON.parse(payload.data.notification);
    var notificationTitle = (notificationData.title) ? notificationData.title : 'New Notification';
    var notificationOptions = {
      body: (notificationData.body) ? notificationData.body : 'You have a notification on CJC Live',
      icon: (notificationData.icon) ? notificationData.icon : '/uploads/images/afff0074db2f4ad1dcd11d82e0cc214d247914cc.png'
    };
  } else {
    var notificationTitle = 'New Notification';
    var notificationOptions = {
      body: 'You have a notification on CJC Live',
      icon: '/uploads/images/afff0074db2f4ad1dcd11d82e0cc214d247914cc.png'
    };
  }
  

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});
