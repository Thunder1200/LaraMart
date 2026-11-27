<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />

<title>{{ $title ?? 'LaraMart' }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<script>
    (function () {
        function resolveTheme() {
            try {
                const stored = localStorage.getItem('laramart-theme');
                if (stored === 'dark' || stored === 'light') {
                    localStorage.setItem('flux.appearance', stored);
                    return stored;
                }
                const fluxStored = localStorage.getItem('flux.appearance');
                if (fluxStored === 'dark' || fluxStored === 'light') {
                    localStorage.setItem('laramart-theme', fluxStored);
                    return fluxStored;
                }
            } catch (e) {}
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        function applyTheme(theme) {
            const normalized = theme === 'dark' ? 'dark' : 'light';
            document.documentElement.classList.toggle('dark', normalized === 'dark');
            document.documentElement.style.colorScheme = normalized;
            return normalized;
        }

        window.laramartTheme = {
            get() {
                return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            },
            set(theme) {
                const normalized = applyTheme(theme);
                try {
                    localStorage.setItem('laramart-theme', normalized);
                    localStorage.setItem('flux.appearance', normalized);
                } catch (e) {}
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: normalized } }));
                return normalized;
            },
            toggle() {
                return this.set(this.get() === 'dark' ? 'light' : 'dark');
            }
        };

        applyTheme(resolveTheme());
    })();
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
