import { Player } from '/static/player.js';

const player = new Player();

const loadStatic = async (url) => {
    const header = await fetch(url);

    if (!header.ok) {
        return header;
    }

    return await header.text();
};

const loadPlayerPage = async (mainEl, id, songUrl) => {
    console.log('player');
    const html = await loadStatic(`/song/${id}`);
    mainEl.innerHTML = html;

    player.init();

    if (songUrl)
        player.play(`/static/${songUrl}`);
};

const loadFrontpage = async (mainEl) => {
    console.log('front');
    const html = await loadStatic(`/static/front.html`);
    mainEl.innerHTML = html;

    const elements = document.getElementsByClassName('play');

    Array.from(elements).forEach(element => {
        element.addEventListener('click', _ => {
            const id = element.dataset.id;
            const songUrl = element.dataset.songUrl;
            loadPlayerPage(mainEl, id, songUrl);

            history.pushState({
                name: 'player',
                songId: id,
                songUrl,
            }, '', '');
        });
    });

    player.stop();
};

const main = async () => {
    const mainEl = document.getElementById('main');

    window.addEventListener('popstate', e => {
        if (e.state) {
            switch (e.state.name) {
                case 'player':
                    loadPlayerPage(mainEl, e.state.songId, e.state.songUrl);
                    break;
                default:
                    loadFrontpage(mainEl);
                    break;
            }
        } else {
            loadFrontpage(mainEl);
        }
    });

    loadFrontpage(mainEl);
    window.history.replaceState({ name: 'frontpage' }, '', '');
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", main);
} else {
    main();
}