// Application installable (PWA) : service worker, bouton d'installation, notifications push.
const guest = document.querySelector('meta[name="sagawyn-guest"]') !== null;

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then(async () => {
        // Page de connexion : on oublie les fiches gardées pour le compte précédent.
        if (guest) {
            const registration = await navigator.serviceWorker.ready;
            registration.active?.postMessage({ type: 'clear-pages' });
        }
    }).catch(() => {});
}

// Le navigateur propose l'installation une seule fois, tôt : on garde l'événement pour le bouton.
let installPrompt = null;
window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    installPrompt = event;
    window.dispatchEvent(new CustomEvent('sagawyn:installable'));
});

// Textes traduits fournis par la page (layout base), le français restant la valeur par défaut.
const text = (key, fallback) => window.sagaWynText?.[key] ?? fallback;

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const vapidKey = () => document.querySelector('meta[name="vapid-public-key"]')?.content ?? '';

function keyToBytes(base64) {
    const padded = (base64 + '='.repeat((4 - (base64.length % 4)) % 4)).replace(/-/g, '+').replace(/_/g, '/');
    return Uint8Array.from(atob(padded), (char) => char.charCodeAt(0));
}

async function saveSubscription(method, body) {
    const response = await fetch('/push/abonnement', {
        method,
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(body),
    });
    if (!response.ok) throw new Error('push');
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('deviceSettings', () => ({
        installable: installPrompt !== null,
        installed: window.matchMedia('(display-mode: standalone)').matches,
        pushSupported: 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window && vapidKey() !== '',
        secure: window.isSecureContext,
        permission: 'Notification' in window ? Notification.permission : 'default',
        subscribed: false,
        busy: false,
        error: '',

        async init() {
            window.addEventListener('sagawyn:installable', () => { this.installable = true; });
            if (this.pushSupported) {
                const registration = await navigator.serviceWorker.ready;
                this.subscribed = (await registration.pushManager.getSubscription()) !== null;
            }
        },

        async install() {
            if (!installPrompt) return;
            installPrompt.prompt();
            const { outcome } = await installPrompt.userChoice;
            installPrompt = null;
            this.installable = false;
            this.installed = outcome === 'accepted';
        },

        async enablePush() {
            this.busy = true;
            this.error = '';
            try {
                this.permission = await Notification.requestPermission();
                if (this.permission !== 'granted') return;
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyToBytes(vapidKey()) });
                const json = subscription.toJSON();
                await saveSubscription('POST', { endpoint: json.endpoint, keys: json.keys, contentEncoding: (PushManager.supportedContentEncodings ?? ['aes128gcm'])[0] });
                this.subscribed = true;
            } catch (error) {
                this.error = text('pushSubscribeFailed', "L'abonnement n'a pas abouti. Réessayez, ou vérifiez les réglages de notification du navigateur.");
            } finally {
                this.busy = false;
            }
        },

        async disablePush() {
            this.busy = true;
            this.error = '';
            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                if (subscription) {
                    await saveSubscription('DELETE', { endpoint: subscription.endpoint });
                    await subscription.unsubscribe();
                }
                this.subscribed = false;
            } catch (error) {
                this.error = text('pushDisableFailed', 'La désactivation a échoué. Réessayez.');
            } finally {
                this.busy = false;
            }
        },
    }));
});
