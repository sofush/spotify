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

const loadSearchPage = async (mainEl, query) => {
    player.stop();

    const url = `/search?q=${encodeURIComponent(query)}`;
    const html = await loadStatic(url);
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
};

const loadFrontpage = async (mainEl) => {
    player.stop();

    const html = await loadStatic(`/static/front.html`);
    mainEl.innerHTML = html;

    const playButtonEls = document.getElementsByClassName('play');

    Array.from(playButtonEls).forEach(element => {
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

    const albumEls = document.getElementsByClassName('album');

    Array.from(albumEls).forEach(element => {
        element.addEventListener('click', _ => {
            const id = element.dataset.id;
            loadAlbumPage(mainEl, id);

            history.pushState({
                name: 'album',
                albumId: id,
            }, '', '');
        });
    });
};

const loadAlbumPage = async (mainEl, id) => {
    player.stop();

    const url = `/album/${id}`;
    const html = await loadStatic(url);
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
};

const main = async () => {
    const mainEl = document.getElementById('main');
    const searchEl = document.getElementById('search');
    const navEl = document.getElementById('nav');
    const navOpenCloseEl = document.getElementsByClassName('open-close')[0];

    navOpenCloseEl.addEventListener('click', _ => {
        navEl.classList.toggle('collapsed');
    });

    searchEl.addEventListener('input', async _ => {
        console.log(searchEl.value);
        if (searchEl.value === '')
            loadFrontpage(mainEl);
        else
            loadSearchPage(mainEl, searchEl.value);
    });

    window.addEventListener('popstate', e => {
        if (e.state) {
            switch (e.state.name) {
                case 'player':
                    loadPlayerPage(mainEl, e.state.songId, e.state.songUrl);
                    break;
                case 'search':
                    loadSearchPage(mainEl, e.state.query);
                    break;
                case 'album':
                    loadAlbumPage(mainEl, e.state.albumId);
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