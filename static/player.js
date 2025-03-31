const getTimeLabel = (time) => {
    const minutes = Math.floor(time / 60);
    const seconds = Math.floor(time % 60);
    const paddedMinutes = String(minutes).padStart(2, '0');
    const paddedSeconds = String(seconds).padStart(2, '0');
    return `${paddedMinutes}:${paddedSeconds}`;
};

export class Player {
    constructor() {
        this.init();

        document.addEventListener('mousemove', (e) => {
            if (!this.isDragging || !this.audio) return;
            const coords = this.seekingBarEl.getBoundingClientRect();
            const x = e.clientX - coords.left;
            this.pct = x / coords.width;
            this.pct = this.updateProgress(this.pct);
            this.currentTimeEl.textContent = getTimeLabel(this.pct * this.audio.duration);
        });

        document.addEventListener('mouseup', () => {
            if (!this.isDragging) return;
            if (this.pct && this.audio)
                this.audio.currentTime = this.pct * this.audio.duration;

            this.isDragging = false;
        });
    }

    init() {
        this.isDragging = false;
        this.offsetX = undefined;
        this.offsetY = undefined;
        this.pct = undefined;

        this.togglePlayEl = document.getElementById('controls-play-pause');
        this.currentTimeEl = document.getElementById('seeking-current-time');
        this.songDurationEl = document.getElementById('seeking-song-duration');
        this.seekingMarkEl = document.getElementById('seeking-mark');
        this.seekingBarEl = document.getElementById('seeking-bar');
        this.progressBarEl = document.getElementById('seeking-progress-bar');

        if (this.togglePlayEl) {
            this.togglePlayEl.addEventListener('click', () => {
                if (!this.audio) return;

                if (this.audio.paused) {
                    this.audio.play().catch(_ => console.error('Could not play audio.'));
                } else {
                    this.audio.pause();
                }
            });
        }

        if (this.seekingBarEl) {
            this.seekingBarEl.addEventListener('mousedown', e => {
                const coords = this.seekingBarEl.getBoundingClientRect();
                const x = e.clientX - coords.left;
                this.pct = x / coords.width;
                this.isDragging = true;
                this.updateProgress(this.pct);
            });
        }

        if (this.seekingMarkEl) {
            this.seekingMarkEl.addEventListener('mousedown', (e) => {
                this.isDragging = true;

                this.offsetX = e.clientX - this.seekingMarkEl.getBoundingClientRect().left;
                this.offsetY = e.clientY - this.seekingMarkEl.getBoundingClientRect().top;

                e.preventDefault();
            });
        }
    }

    play(url) {
        this.audio = new Audio(url);

        this.audio.addEventListener('play', () => {
            if (this.togglePlayEl)
                this.togglePlayEl.classList.remove('paused');
        });

        this.audio.addEventListener('pause', () => {
            if (this.togglePlayEl)
                this.togglePlayEl.classList.add('paused');
        });

        this.audio.addEventListener('timeupdate', _ => {
            if (this.isDragging)
                return;

            if (this.currentTimeEl && this.audio)
                this.currentTimeEl.textContent = getTimeLabel(this.audio.currentTime);

            this.updateProgress();
        });

        this.audio.addEventListener('durationchange', _ => {
            if (this.songDurationEl)
                this.songDurationEl.textContent = getTimeLabel(this.audio.duration);

            this.updateProgress();
        });
    }

    stop() {
        if (!this.audio)
            return;

        this.audio.pause();
        this.audio = undefined;
    }

    updateProgress(pct) {
        if (!this.audio || !this.seekingMarkEl || !this.progressBarEl)
            return 0;

        pct ??= this.audio.currentTime / this.audio.duration;
        const progress = Math.min(1, Math.max(0, pct));
        this.seekingMarkEl.style.marginLeft = `calc(${progress * 100}% - 12px * ${progress})`;
        this.progressBarEl.style.width = `${progress * 100}%`;
        return progress;
    }
};
