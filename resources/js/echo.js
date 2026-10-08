import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Mises à jour en direct (Laravel Reverb). Sans configuration Reverb dans .env,
// l'appli fonctionne normalement : les pages se mettent à jour au rechargement.
if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

// Repli sans temps réel : Reverb absent, injoignable ou coupé. Tant que l'onglet est visible,
// les composants qui écoutent les diffusions (cloche, messages, fiche, fenêtre « reçu »…) se
// rafraîchissent d'eux-mêmes toutes les 30 secondes. Avec Reverb connecté, rien ne change.
const FALLBACK_POLL = 30000;
const listensLive = (component) => (component.originalEffects?.listeners ?? component.effects?.listeners ?? [])
    .some((listener) => listener.startsWith('echo'));

setInterval(() => {
    const connected = window.Echo?.connector?.pusher?.connection?.state === 'connected';
    if (connected || document.hidden || !navigator.onLine || !window.Livewire) {
        return;
    }
    window.Livewire.all().filter(listensLive).forEach((component) => component.$wire.$refresh());
}, FALLBACK_POLL);
