const CACHE_NAME = 'chore-tracker-v1.2';
const STATIC_CACHE = 'chore-tracker-static-v1.2';
const DYNAMIC_CACHE = 'chore-tracker-dynamic-v1.2';

// Files to cache immediately
const STATIC_FILES = [
  '/',
  '/build/app.js',
  '/build/app.css',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png',
  '/manifest.json',
  'https://cdn.tailwindcss.com',
  'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js'
];

// Pages to cache dynamically
const DYNAMIC_URLS = [
  '/dashboard',
  '/user/tasks',
  '/user/rankings',
  '/user/balance',
  '/login',
  '/register'
];

// Install event - cache static files
self.addEventListener('install', event => {
  console.log('Service Worker: Installing...');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('Service Worker: Caching static files');
        return cache.addAll(STATIC_FILES);
      })
      .catch(err => {
        console.log('Service Worker: Cache failed', err);
      })
  );
  // Force the waiting service worker to become the active service worker
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== STATIC_CACHE && cache !== DYNAMIC_CACHE) {
            console.log('Service Worker: Clearing old cache');
            return caches.delete(cache);
          }
        })
      );
    })
  );
  // Ensure the service worker takes control immediately
  self.clients.claim();
});

// Fetch event - serve cached content when offline
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip external requests (except whitelisted CDNs)
  if (!url.origin.includes(self.location.origin) && 
      !url.hostname.includes('cdn.tailwindcss.com') && 
      !url.hostname.includes('unpkg.com')) {
    return;
  }

  event.respondWith(
    caches.match(request)
      .then(response => {
        // Return cached version if available
        if (response) {
          console.log('Service Worker: Serving from cache', request.url);
          return response;
        }

        // Otherwise fetch from network
        return fetch(request)
          .then(fetchResponse => {
            // Don't cache if not successful
            if (!fetchResponse || fetchResponse.status !== 200 || fetchResponse.type !== 'basic') {
              return fetchResponse;
            }

            // Clone the response
            const responseToCache = fetchResponse.clone();

            // Determine which cache to use
            const cacheName = STATIC_FILES.includes(url.pathname) ? STATIC_CACHE : DYNAMIC_CACHE;

            // Cache the response
            caches.open(cacheName)
              .then(cache => {
                console.log('Service Worker: Caching new resource', request.url);
                cache.put(request, responseToCache);
              });

            return fetchResponse;
          })
          .catch(() => {
            // If fetch fails and it's a navigation request, return offline page
            if (request.mode === 'navigate') {
              return caches.match('/');
            }
            
            // For images, return a placeholder if available
            if (request.destination === 'image') {
              return caches.match('/icons/icon-192x192.png');
            }
          });
      })
  );
});

// Background sync for offline form submissions
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    console.log('Service Worker: Background sync triggered');
    event.waitUntil(doBackgroundSync());
  }
});

async function doBackgroundSync() {
  // Handle any offline form submissions stored in IndexedDB
  // This would sync with your Laravel backend when connection is restored
  console.log('Service Worker: Performing background sync');
}

// Push notifications (for future use)
self.addEventListener('push', event => {
  if (event.data) {
    const data = event.data.json();
    console.log('Service Worker: Push received', data);
    
    const options = {
      body: data.body,
      icon: '/icons/icon-192x192.png',
      badge: '/icons/icon-72x72.png',
      vibrate: [100, 50, 100],
      data: {
        dateOfArrival: Date.now(),
        primaryKey: data.primaryKey || 1
      },
      actions: [
        {
          action: 'explore',
          title: 'View Tasks',
          icon: '/icons/tasks-shortcut.png'
        },
        {
          action: 'close',
          title: 'Close',
          icon: '/icons/close-icon.png'
        }
      ]
    };

    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  }
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Notification clicked', event);
  event.notification.close();

  if (event.action === 'explore') {
    // Open the app to tasks page
    event.waitUntil(
      clients.openWindow('/user/tasks')
    );
  } else if (event.action === 'close') {
    // Just close the notification
    return;
  } else {
    // Default action - open the app
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// Handle message events from the main app
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

console.log('Service Worker: Loaded successfully'); 