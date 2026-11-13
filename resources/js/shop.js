const shopResultsSelector = '[data-shop-product-results]';
const itemCategoryLinkSelector = '[data-shop-item-filter-link]';
let activeShopRequest = null;
let shopRequestSequence = 0;

const syncItemCategory = function(url) {
	const selectedItemCategory = url.searchParams.get('item_category');

	document.querySelectorAll(itemCategoryLinkSelector).forEach(function(link) {
		const linkUrl = new URL(link.href, window.location.href);
		const isSelected = linkUrl.searchParams.get('item_category') === selectedItemCategory;
		link.classList.toggle('is_active', isSelected);
		if (isSelected) {
			link.setAttribute('aria-current', 'page');
		} else {
			link.removeAttribute('aria-current');
		}
	});

	document.querySelectorAll('.shop-search-form').forEach(function(form) {
		const categoryInput = form.querySelector('input[name="item_category"]');
		if (selectedItemCategory === null) {
			categoryInput?.remove();

			return;
		}

		if (categoryInput) {
			categoryInput.value = selectedItemCategory;

			return;
		}

		const input = document.createElement('input');
		input.type = 'hidden';
		input.name = 'item_category';
		input.value = selectedItemCategory;
		form.append(input);
	});
};

const loadShopResults = async function(url, pushHistory) {
	if (url.origin !== window.location.origin || url.pathname !== window.location.pathname) {
		return;
	}

	activeShopRequest?.abort();
	const controller = new AbortController();
	activeShopRequest = controller;
	const requestSequence = ++shopRequestSequence;
	const currentResults = document.querySelector(shopResultsSelector);
	if (!currentResults) {
		return;
	}
	currentResults.setAttribute('aria-busy', 'true');

	try {
		const response = await fetch(url.toString(), {
			credentials: 'same-origin',
			headers: {
				Accept: 'text/html',
				'X-Requested-With': 'XMLHttpRequest',
			},
			signal: controller.signal,
		});
		if (!response.ok) {
			throw new Error(`Shop request failed with status ${response.status}.`);
		}

		const responseDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
		const nextResults = responseDocument.querySelector(shopResultsSelector);
		if (!nextResults) {
			throw new Error('The shop response did not contain product results.');
		}
		if (requestSequence !== shopRequestSequence) {
			return;
		}

		currentResults.replaceWith(nextResults);
		syncItemCategory(url);
		if (pushHistory) {
			window.history.pushState(null, '', url.toString());
		}
	} catch (error) {
		if (error instanceof DOMException && error.name === 'AbortError') {
			return;
		}

		console.error('Unable to update shop products without reloading the page.', error);
		window.location.assign(url.toString());
	} finally {
		if (requestSequence === shopRequestSequence) {
			document.querySelector(shopResultsSelector)?.setAttribute('aria-busy', 'false');
			activeShopRequest = null;
		}
	}
};

document.addEventListener('click', function(event) {
	if (!(event.target instanceof Element) || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
		return;
	}

	const link = event.target.closest(itemCategoryLinkSelector);
	if (!link) {
		return;
	}

	const url = new URL(link.href, window.location.href);
	if (url.origin !== window.location.origin || url.pathname !== window.location.pathname) {
		return;
	}

	event.preventDefault();
	if (url.href !== window.location.href) {
		loadShopResults(url, true);
	}
});

window.addEventListener('popstate', function() {
	loadShopResults(new URL(window.location.href), false);
});

syncItemCategory(new URL(window.location.href));
