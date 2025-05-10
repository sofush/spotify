import { Player } from '/static/player.js';

const fetchHtml = async (url) => {
	console.log(`Fetching HTML at ${url}`);

	const header = await fetch(url);

	if (!header.ok) {
		return header;
	}

	return await header.text();
};

export class Page {
	constructor(name, url, recordHistory = true) {
		this.name = name;
		this.url = url;
		this.recordHistory = recordHistory;
	}

	async load(containerEl, args, pushState) {
		pushState ??= true;

		containerEl.innerHTML = await fetchHtml(this.url.toString());

		if (this.recordHistory && pushState) {
			window.history.pushState({ name: this.name, args: args }, '', '');
		}
	}
}

export class Navigator {
	constructor(containerEl) {
		this.containerEl = containerEl;
		this.pages = [];
		this.currentPage = undefined;
	}

	insert(page) {
		this.pages.push(page);
	}

	async load(name, args, pushState) {
		const page = this.pages.find(page => page.name == name);

		if (!page) {
			console.error(`Could not find page with name "${name}"`);
			return;
		}

		if (this.currentPage && this.currentPage.unload) {
			console.log(`Unloading page "${this.currentPage.name}"...`);
			await this.currentPage.unload();
		}

		console.log(`Loading page "${name}"...`);
		await page.load(this.containerEl, args, pushState);

		this.currentPage = page;

		const observer = new MutationObserver((_, instance) => {
			const albumEls = this.containerEl.getElementsByClassName('album');

			for (const el of albumEls) {
				el.addEventListener('click', _ => {
					const id = el.dataset.id;
					this.load('album', { id });
				});
			}

			const playEls = this.containerEl.getElementsByClassName('play');

			for (const el of playEls) {
				el.addEventListener('click', _ => {
					const id = el.dataset.id;
					const songUrl = el.dataset.songUrl;
					this.load('player', { id, songUrl });
				});
			}

			const addAlbumEls = this.containerEl.getElementsByClassName('add-album');

			for (const el of addAlbumEls) {
				el.addEventListener('click', _ => {
					this.load('add-album', {});
				});
			}

			instance.disconnect();
		});

		observer.observe(this.containerEl, { childList: true, subtree: true });
	}
}

export class FrontPage extends Page {
	constructor() {
		super('frontpage', '/static/front.html', true);
	}

	async load(containerEl, args, pushState) {
		super.load(containerEl, args, pushState);
	}
}

export class SearchPage extends Page {
	constructor() {
		super('search', undefined, false);
	}

	async load(containerEl, args, pushState) {
		const url = new URL(`${window.location.origin}/search`);
		url.searchParams.set('q', args.query);
		this.url = url;
		super.load(containerEl, args, pushState);
	}
}

export class AlbumPage extends Page {
	constructor() {
		super('album', undefined, true);
	}

	async load(containerEl, args, pushState) {
		const id = args.id;
		const url = new URL(`${window.location.origin}/album/${id}`);
		this.url = url;
		super.load(containerEl, args, pushState);
	}
}

export class AddAlbumPage extends Page {
	constructor() {
		super('add-album', undefined, true);
	}

	async load(containerEl, args, pushState) {
		const url = new URL(`${window.location.origin}/add-album`);
		this.url = url;
		super.load(containerEl, args, pushState);

		const observer = new MutationObserver((_, instance) => {
			const addAlbumEl = document.getElementById('add-album');
			const searchInputEl = addAlbumEl.getElementsByClassName('search')[0];
			const keyInputEl = addAlbumEl.getElementsByClassName('key')[0];
			const secretInputEl = addAlbumEl.getElementsByClassName('secret')[0];
			const containerEl = addAlbumEl.getElementsByClassName('container')[0];

			addAlbumEl.addEventListener('submit', async e => {
				e.preventDefault();

				console.log(`Searching discogs for "${searchInputEl.value}"`);
				const baseURL = 'https://api.discogs.com';
				const url = new URL('/database/search', baseURL);
				url.searchParams.append('q', searchInputEl.value);
				url.searchParams.append('key', keyInputEl.value);
				url.searchParams.append('secret', secretInputEl.value);
				url.searchParams.append('type', 'master');
				url.searchParams.append('format', 'album');
				console.log(`API request URL: ${url.toString()}`);

				const headers = new Headers({
					"Accept": "application/json",
					"Content-Type": "application/json",
					"User-Agent": "SofushMusicPlayer/0.1"
				});
				const header = await fetch(url, {
					headers: headers
				});

				if (!header.ok) {
					console.log('Header is not OK.');
					return;
				}

				const body = await header.json();

				for (const result of body.results.slice(3)) {
					const title = result.title;
					console.log(`Title: ${title}`);
					const img = document.createElement("img");
					img.src = result.thumb;
					containerEl.appendChild(img);
				}
			});

			instance.disconnect();
		});

		observer.observe(containerEl, { childList: true, subtree: true });
	}
}

export class PlayerPage extends Page {
	constructor() {
		super('player', undefined, true);
		this.player = new Player();
	}

	async load(containerEl, args, pushState) {
		const url = new URL(`${window.location.origin}/song/${args.id}`);
		this.url = url;
		super.load(containerEl, args, pushState);

		if (!args.songUrl) {
			console.error('Could not load song because no song URL was set.');
		}

		const songUrl = new URL(`${window.location.origin}/static/${args.songUrl}`);

		const observer = new MutationObserver((_, instance) => {
			this.player.init();
			this.player.play(songUrl);
			instance.disconnect();
		});

		observer.observe(containerEl, { childList: true, subtree: true });
	}

	async unload() {
		this.player.stop();
	}
}
