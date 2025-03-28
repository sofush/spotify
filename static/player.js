const getTimeLabel = (time) => {
    const minutes = Math.floor(time / 60);
    const seconds = Math.floor(time % 60);
    const paddedMinutes = String(minutes).padStart(2, '0');
    const paddedSeconds = String(seconds).padStart(2, '0');
    return `${paddedMinutes}:${paddedSeconds}`;
};

const main = () => {
    const audio = new Audio('/static/Harmony.ogx');
    const togglePlayEl = document.getElementById('controls-play-pause');
    const currentTimeEl = document.getElementById('seeking-current-time');
    const songDurationEl = document.getElementById('seeking-song-duration');
    const seekingMarkEl = document.getElementById('seeking-mark');
    const seekingBarEl = document.getElementById('seeking-bar');
    const progressBarEl = document.getElementById('seeking-progress-bar');
    let isDragging = false;
    let offsetX, offsetY;
    let pct = undefined;

    const updateProgress = (pct) => {
        pct ??= audio.currentTime / audio.duration;
        const progress = Math.min(1, Math.max(0, pct));
        seekingMarkEl.style.marginLeft = `calc(${progress * 100}% - 12px * ${progress})`;
        progressBarEl.style.width = `${progress * 100}%`;
        return progress;
    };

    togglePlayEl.addEventListener('click', () => {
        if (audio.paused) {
            audio.play().catch(_ => console.error('Could not play audio.'));
        } else {
            audio.pause();
        }
    });

    seekingBarEl.addEventListener('mousedown', e => {
        const coords = seekingBarEl.getBoundingClientRect();
        const x = e.clientX - coords.left;
        pct = x / coords.width;
        audio.currentTime = pct * audio.duration;
        isDragging = true;
        updateProgress(pct);
    });

    seekingMarkEl.addEventListener('mousedown', (e) => {
        isDragging = true;

        offsetX = e.clientX - seekingMarkEl.getBoundingClientRect().left;
        offsetY = e.clientY - seekingMarkEl.getBoundingClientRect().top;

        e.preventDefault();
    });

    document.addEventListener('mousemove', (e) => {
        if (isDragging) {
            const coords = seekingBarEl.getBoundingClientRect();
            const x = e.clientX - coords.left;
            pct = x / coords.width;
            pct = updateProgress(pct);
            currentTimeEl.textContent = getTimeLabel(pct * audio.duration);
        }
    });

    document.addEventListener('mouseup', () => {
        if (!pct) return;
        if (isDragging)
            audio.currentTime = pct * audio.duration;

        isDragging = false;
    });

    audio.addEventListener('play', () => {
        togglePlayEl.classList.remove('paused');
    });

    audio.addEventListener('pause', () => {
        togglePlayEl.classList.add('paused');
    });

    audio.addEventListener('timeupdate', _ => {
        currentTimeEl.textContent = getTimeLabel(audio.currentTime);

        if (!isDragging)
            updateProgress();
    });

    audio.addEventListener('durationchange', _ => {
        songDurationEl.textContent = getTimeLabel(audio.duration);
        updateProgress();
    });
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", main);
} else {
    main();
}