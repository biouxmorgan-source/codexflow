// Lecteur sonore du MJ : joue un morceau de la bibliothèque sur cet appareil, avec un fondu
// quand on passe d'un morceau à l'autre. Placé dans @persist, la musique continue d'une page à l'autre.
// Il écoute l'événement « sagawyn-audio-play » ({ url, title, loop }) envoyé par les boutons « ▶ Ici ».
const FADE_MS = 1500;

function fade(audio, from, to, done) {
    const start = performance.now();
    audio.volume = from;
    const step = (now) => {
        const t = Math.min(1, (now - start) / FADE_MS);
        audio.volume = from + (to - from) * t;
        if (t < 1) {
            requestAnimationFrame(step);
        } else if (done) {
            done();
        }
    };
    requestAnimationFrame(step);
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('audioDeck', () => ({
        title: '',
        playing: false,
        loop: true,
        volume: 80,
        blocked: false,
        current: null,

        play({ url, title, loop }) {
            const previous = this.current;
            const audio = new Audio(url);
            audio.loop = !!loop;
            audio.preload = 'auto';
            audio.addEventListener('ended', () => {
                if (audio === this.current) {
                    this.playing = false;
                }
            });
            this.current = audio;
            this.title = title;
            this.loop = !!loop;
            this.blocked = false;

            audio.volume = 0;
            audio.play().then(() => {
                this.playing = true;
                fade(audio, 0, this.volume / 100);
            }).catch(() => {
                this.blocked = true;
                this.playing = false;
            });

            if (previous) {
                fade(previous, previous.volume, 0, () => previous.pause());
            }
        },

        toggle() {
            if (!this.current) {
                return;
            }
            if (this.current.paused) {
                this.current.volume = this.volume / 100;
                this.current.play().then(() => { this.playing = true; this.blocked = false; }).catch(() => { this.blocked = true; });
            } else {
                this.current.pause();
                this.playing = false;
            }
        },

        toggleLoop() {
            this.loop = !this.loop;
            if (this.current) {
                this.current.loop = this.loop;
            }
        },

        setVolume() {
            if (this.current) {
                this.current.volume = this.volume / 100;
            }
        },

        stop() {
            const audio = this.current;
            this.current = null;
            this.playing = false;
            this.title = '';
            if (audio) {
                fade(audio, audio.volume, 0, () => audio.pause());
            }
        },
    }));
});

// Écran de table : joue la musique lancée par le MJ ({ url, loop, volume, playing, key } ou null).
// Un navigateur refuse le son tant qu'on n'a pas touché la page : un bouton « Activer le son » le débloque.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('tableAudio', (initial) => ({
        key: null,
        audio: null,
        blocked: false,
        state: null,

        init() {
            this.apply(initial);
        },

        apply(state) {
            this.state = state;

            if (!state) {
                this.key = null;
                this.blocked = false;
                const audio = this.audio;
                this.audio = null;
                if (audio) {
                    fade(audio, audio.volume, 0, () => audio.pause());
                }
                return;
            }

            if (state.key !== this.key) {
                const previous = this.audio;
                this.key = state.key;
                this.audio = new Audio(state.url);
                this.audio.preload = 'auto';
                if (previous) {
                    fade(previous, previous.volume, 0, () => previous.pause());
                }
            }

            this.audio.loop = !!state.loop;

            if (state.playing) {
                const target = state.volume / 100;
                const starting = this.audio.paused;
                if (starting) {
                    this.audio.volume = 0;
                }
                this.audio.play().then(() => {
                    this.blocked = false;
                    starting ? fade(this.audio, 0, target) : (this.audio.volume = target);
                }).catch(() => { this.blocked = true; });
            } else {
                this.audio.pause();
            }
        },

        unlock() {
            this.blocked = false;
            this.apply(this.state);
        },
    }));
});
