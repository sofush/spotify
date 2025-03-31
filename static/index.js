let page = 'index.html';

const loadPage = async (url) => {
    const header = await fetch('/static/front.html');

    if (!header.ok) {
        return header;
    }

    return header.text();
};

const main = async () => {
    const mainEl = document.getElementById('main');

    loadPage('/static/front.html').then(r => {
        mainEl.innerHTML = r;
    });
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", main);
} else {
    main();
}