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
    const progressBarEl = document.getElementById('seeking-progress-bar');

    const updateMark = () => {
        const pct = audio.currentTime / audio.duration;
        const progress = Math.min(1, Math.max(0, pct));
        seekingMarkEl.style.marginLeft = `calc(${progress * 100}% - 12px * ${progress})`;
        progressBarEl.style.width = `${progress * 100}%`;
    };

    togglePlayEl.addEventListener('click', () => {
        if (audio.paused) {
            audio.play().catch(_ => console.error('Could not play audio.'));
        } else {
            audio.pause();
        }
    });

    audio.addEventListener('play', () => {
        togglePlayEl.classList.remove('paused');
    });

    audio.addEventListener('pause', () => {
        togglePlayEl.classList.add('paused');
    });

    audio.addEventListener('timeupdate', _ => {
        currentTimeEl.textContent = getTimeLabel(audio.currentTime);
        updateMark();
    });

    audio.addEventListener('durationchange', _ => {
        songDurationEl.textContent = getTimeLabel(audio.duration);
        updateMark();
    });
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", main);
} else {
    main();
}