const CACHE_NAME = 'chore-tracker-v2.0';
const STATIC_CACHE = 'chore-tracker-static-v2.0';
const DYNAMIC_CACHE = 'chore-tracker-dynamic-v2.0';

// Files to cache immediately
const STATIC_FILES = [
  '/',
  '/build/app.js',
  '/build/app.css',
  '/app_icons/icon-192x192.png',
  '/app_icons/icon-512x512.png',
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
            console.log('Service Worker: Clearing old cache', cache);
            return caches.delete(cache);
          }
        })
      );
    }).then(() => {
      // Clear dynamic cache on activation to ensure fresh data
      console.log('Service Worker: Clearing dynamic cache for fresh start');
      return caches.delete(DYNAMIC_CACHE);
    })
  );
  // Ensure the service worker takes control immediately
  self.clients.claim();
});

// Fetch event - smart caching strategy
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

  // Determine if this is a static asset or dynamic content
  const isStaticAsset = url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i) ||
                        STATIC_FILES.includes(url.pathname) ||
                        url.hostname.includes('cdn.tailwindcss.com') ||
                        url.hostname.includes('unpkg.com');

  // Check if this is a Livewire request or dynamic route
  const isDynamicContent = url.pathname.includes('/livewire/') ||
                          url.search.includes('_token') ||
                          url.pathname.includes('/dashboard') ||
                          url.pathname.includes('/user/') ||
                          url.pathname.includes('/admin/') ||
                          request.headers.get('X-Livewire') ||
                          request.headers.get('X-Requested-With') === 'XMLHttpRequest' ||
                          request.headers.get('Cache-Control') === 'no-cache';

  if (isStaticAsset) {
    // Cache-first strategy for static assets
    event.respondWith(
      caches.match(request)
        .then(response => {
          if (response) {
            console.log('Service Worker: Serving static asset from cache', request.url);
            return response;
          }

          return fetch(request)
            .then(fetchResponse => {
              if (fetchResponse && fetchResponse.status === 200) {
                const responseToCache = fetchResponse.clone();
                caches.open(STATIC_CACHE)
                  .then(cache => {
                    console.log('Service Worker: Caching static asset', request.url);
                    cache.put(request, responseToCache);
                  });
              }
              return fetchResponse;
            })
            .catch(() => {
              return caches.match(request);
            });
        })
    );
  } else if (isDynamicContent) {
    // Network-first strategy for dynamic content - always try to get fresh data
    event.respondWith(
      fetch(request)
        .then(fetchResponse => {
          console.log('Service Worker: Serving fresh dynamic content', request.url);
          return fetchResponse;
        })
        .catch(() => {
          // Only serve cached dynamic content if network fails
          console.log('Service Worker: Network failed, trying cache for', request.url);
          return caches.match(request)
            .then(response => {
              if (response) {
                console.log('Service Worker: Serving stale dynamic content from cache', request.url);
                return response;
              }
              
              // If it's a navigation request and no cache, return offline page
              if (request.mode === 'navigate') {
                return caches.match('/');
              }
              
              throw new Error('No cache available');
            });
        })
    );
  } else {
    // Network-first for other content (HTML pages, etc.)
    event.respondWith(
      fetch(request)
        .then(fetchResponse => {
          if (fetchResponse && fetchResponse.status === 200) {
            // Only cache successful responses
            const responseToCache = fetchResponse.clone();
            caches.open(DYNAMIC_CACHE)
              .then(cache => {
                console.log('Service Worker: Caching page', request.url);
                cache.put(request, responseToCache);
              });
          }
          return fetchResponse;
        })
        .catch(() => {
          // Fallback to cache
          return caches.match(request)
            .then(response => {
              if (response) {
                console.log('Service Worker: Serving from cache (offline)', request.url);
                return response;
              }
              
              // For images, return a placeholder if available
              if (request.destination === 'image') {
                return caches.match('/app_icons/icon-192x192.png');
              }
              
              // If it's a navigation request, return cached home page
              if (request.mode === 'navigate') {
                return caches.match('/');
              }
              
              throw new Error('No cache available');
            });
        })
    );
  }
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
      icon: '/app_icons/icon-192x192.png',
      badge: '/app_icons/icon-72x72.png',
      vibrate: [100, 50, 100],
      data: {
        dateOfArrival: Date.now(),
        primaryKey: data.primaryKey || 1
      },
      actions: [
        {
          action: 'explore',
          title: 'View Tasks',
          icon: '/app_icons/tasks-shortcut.png'
        },
        {
          action: 'close',
          title: 'Close',
          icon: '/app_icons/close-icon.png'
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
  } else if (event.data && event.data.type === 'CLEAR_CACHE') {
    console.log('Service Worker: Clearing cache on request');
    event.waitUntil(
      caches.delete(DYNAMIC_CACHE).then(() => {
        console.log('Service Worker: Dynamic cache cleared');
        // Notify the client that cache was cleared
        event.ports[0].postMessage({ success: true });
      })
    );
  } else if (event.data && event.data.type === 'CLEAR_ALL_CACHE') {
    console.log('Service Worker: Clearing all caches on request');
    event.waitUntil(
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cache => caches.delete(cache))
        );
      }).then(() => {
        console.log('Service Worker: All caches cleared');
        // Notify the client that cache was cleared
        event.ports[0].postMessage({ success: true });
      })
    );
  }
});

console.log('Service Worker: Loaded successfully'); 