<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HonTomo</title>

    {{-- Favicon / app icons (files live in /public). ?v= busts the browser's aggressive favicon cache --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=7" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=7" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=7" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=7" />
    <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=7" />
    <meta name="theme-color" content="#0A84F0" />

    {{-- Installable app (PWA): iOS "Add to Home Screen" + Android install --}}
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-title" content="HonTomo" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />

    <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
    <style>
        /* Loading screen logo: sizing only. Which image is used is set by the
           inline script below, directly on the single <img> tag — there is
           only ever ONE <img> in the DOM here, so two logos can never show
           at once, no matter what that script does or doesn't do. */
        #loading-bg .loading-logo img { display: block; width: 180px; max-width: 60vw; height: auto; }
    </style>
    <script>
        // Runs before the first paint: sets the loader background/spinner
        // colour AND picks the light/dark logo file, both from the app's own
        // saved theme preference (not the OS/browser dark-mode setting) so
        // the loading screen matches whatever the user chose inside HonTomo.
        (function () {
            function read(name) {
                try { return localStorage.getItem('HonTomo-' + name) || localStorage.getItem('vuexy-' + name) } catch (e) { return null }
            }

            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
            var loaderColor = read('initial-loader-bg') || (prefersDark ? '#2F3349' : '#FFFFFF')
            var primaryColor = read('initial-loader-color') || '#0A84F0'
            var root = document.documentElement

            root.style.setProperty('--initial-loader-bg', loaderColor)
            root.style.setProperty('--initial-loader-color', primaryColor)

            // Same colour -> dark/light decision the app itself uses, applied
            // to a single <img id="loading-logo-img"> (added further down).
            var m = /^#?([0-9a-f]{6})$/i.exec(loaderColor.trim())
            var dark = false
            if (m) {
                var n = parseInt(m[1], 16)
                dark = (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) < 128
            }
            window.__hontomoLoaderLogo = dark
                ? '{{ asset('logo-dark.png') }}?v=7'
                : '{{ asset('logo.png') }}?v=7'
        })()
    </script>
    <script>
        // Keep Chrome's install prompt so the app can offer its own "Install app" button.
        // (This must be captured early: the event can fire before the Vue app has mounted.)
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault()
            window.__hontomoInstallPrompt = e
            window.dispatchEvent(new Event('hontomo:installable'))
        })

        window.addEventListener('appinstalled', function () {
            window.__hontomoInstallPrompt = null
            window.dispatchEvent(new Event('hontomo:installed'))
        })

        // Offline support (production / https only, so it never interferes with the Vite dev server)
        if ('serviceWorker' in navigator && location.protocol === 'https:') {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function () { })
            })
        }
    </script>
    @vite(['resources/js/main.js'])
</head>

<body>
    <div id="app">
        <div id="loading-bg">
            <div class="loading-logo">
                {{-- Only ONE <img> tag exists here — no second element to hide,
                     so two logos can never show at once. Its src is written by
                     the inline script above, before this line even parses. --}}
                <img
                    id="loading-logo-img"
                    src="{{ asset('logo.png') }}?v=7"
                    alt="HonTomo Logo"
                />
                <script>
                    // Synchronous + right after the <img>: runs before the browser
                    // paints, so there is no visible flash of the wrong logo.
                    if (window.__hontomoLoaderLogo)
                        document.getElementById('loading-logo-img').src = window.__hontomoLoaderLogo
                </script>
            </div>
            <div class=" loading">
                <div class="effect-1 effects"></div>
                <div class="effect-2 effects"></div>
                <div class="effect-3 effects"></div>
            </div>
        </div>
    </div>

</body>

</html>
