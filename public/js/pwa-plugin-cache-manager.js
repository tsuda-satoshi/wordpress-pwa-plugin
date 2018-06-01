/* global STATIC_FILES, CACHE_NAME, NEVER_CACHE_URLS, blacklist */

class CacheManager {
    /**
     *
     * @param serviceWorker ServiceWorker
     * @param caches Cache
     * @param db Dexie
     * @param settings
     */
    constructor(serviceWorker, caches, db, settings) {
        this.serviceWorker = serviceWorker;
        this.caches = caches;
        this.settings = settings;
        this.db = db;
    }

    // イベント登録処理
    initialize() {
        this.db.version(1).stores({caches: "url,cached_at,ttl"});
        this.serviceWorker.addEventListener('install', (event) => {
            console.log('service worker installed');
            // インストール処理
            event.waitUntil(
                this.caches.open(this.settings.cacheName)
                    .then((cache) => {
                        return cache.addAll(this.settings.initialCaches);
                    })
            )
        });
        this.serviceWorker.addEventListener('fetch', (event) => {
            return this.onFetch(event);
        })
    }

    onFetch(event) {
        // キャッシュ可能かどうか
        let isGetRequest = event.request.method === 'GET';
        let isExcluded = this.settings.exclusions.some((pattern) => {
            let isMatch = (new RegExp(pattern)).test(event.request.url);
            return isMatch;
        });
        let cacheable = event.request.method === 'GET' && !this.settings.exclusions.some((pattern) => {
            return (new RegExp(pattern)).test(event.request.url);
        });
        if (!cacheable) {
            // キャッシュ対象外の場合はサーバーにリクエスト。
            return event.respondWith(fetch(event.request).catch(() => {
                return this.caches.match(this.settings.offlinePage);
            }));
        }
        // キャッシュ対象の場合はキャッシュ優先方式でレスポンスを返す。
        return event.respondWith(this.cacheFirstFetch(event.request).catch(() => {
            return this.caches.match(this.settings.offlinePage);
        }));
    }

    /**

     * @param request Request
     */
    cacheFirstFetch(request) {
        return this.db.caches.get(request.url)
            .then((data) => {
                // キャッシュ情報がundefined,もしくはttl以上の時間が経過していた場合はサーバーから再取得優先
                if (!data || (Date.now() - data.cached_at > data.ttl)) {
                    return this.remoteFirstFetch(request);
                }

                return this.caches.match(request).then((response) => {
                    if (response) {
                        return response;
                    }
                    return fetchAndCache(request);
                });
            }).catch(() => {
                return this.fetchAndCache(request);
            });
    }

    remoteFirstFetch(request) {
        console.log('remoteFirstFetch', request.url);
        return fetchAndCache(request).catch(() => {
            console.log('fail to fetch. use cache');
            return this.caches.match(request);
        });
    }

    fetchAndCache(request) {
        // TODO: Indexeddbにキャッシュ日時を保存し、expireする。
        return fetch(request).then((response) => {
            if (response.status !== 200) {
                return response;
            }
            let res = response.clone();
            this.caches.open(this.settings.cacheName).then((cache) => {
                cache.add(request.url, res).then((result) => {
                    console.log(result);
                }, (err) => {
                    console.log(err);
                });
            });
            this.db.caches.put({
                url: request.url,
                cached_at: Date.now(),
                ttl: this.settings.ttl
            });
            return response;
        });
    }
}

// self.addEventListener('activate', function (event) {
//     console.log('service worker activated');
//     event.waitUntil(
//         caches.open(CACHE_NAME).then((cache) => {
//             purgelist.map((cachedUrl) => {
//                 return cache.delete(cachedUrl);
//             });
//         })
//     );
// });

// self.addEventListener('sync', function (event) {
//     if (event.tag === 'myFirstSync') {
//         event.waitUntil(doSomeStuff());
//     }
// });

// function getPosts() {
//     return fetch('/?rest_route=/wp/v2/posts')
//         .then((response) => {
//             const res = response.clone();
//             return res.json();
//         });
// }
//
// function doSomeStuff() {
//     Promise.all([getPosts(), caches.open(CACHE_NAME)]).then((values) => {
//         const posts = values[0];
//         const cache = values[1];
//         return cache.keys().then((cachedRequests) => {
//             const cachedUrls = cachedRequests.map((request) => {
//                 return request.url;
//             });
//             const urls = posts.map((post) => {
//                 return post.guid.rendered;
//             }).filter((url) => {
//                 return !cachedUrls.includes(url);
//             });
//             // console.log('urls to cache', urls);
//             return cache.addAll(urls);
//         });
//     });
// }


