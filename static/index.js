import { Navigator, FrontPage, SearchPage, AlbumPage, PlayerPage } from '/static/navigation.js';

const main = async () => {
	const mainEl = document.getElementById('main');
	const searchEl = document.getElementById('search');
	const navEl = document.getElementById('nav');
	const navOpenCloseEl = document.getElementsByClassName('open-close')[0];

	const navigator = new Navigator(mainEl);

	navigator.insert(new FrontPage());
	navigator.insert(new SearchPage());
	navigator.insert(new AlbumPage());
	navigator.insert(new PlayerPage());

	await navigator.load('frontpage');
	window.history.replaceState({ name: 'frontpage' }, '', '');

	navOpenCloseEl.addEventListener('click', _ => {
		navEl.classList.toggle('collapsed');
	});

	searchEl.addEventListener('input', async _ => {
		if (searchEl.value === '')
			await navigator.load('frontpage');
		else
			await navigator.load('search', { query: searchEl.value });
	});

	window.addEventListener('popstate', e => {
		navigator.load(e.state.name, e.state.args, false);
	});
};

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", main);
} else {
	main();
}
