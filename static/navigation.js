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
