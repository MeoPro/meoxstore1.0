const hidePreloader = function() {
    const preloader = document.querySelector('#js-preloader');
    if (preloader) {
        preloader.classList.add('loaded');
    }
};

const storefrontCurrencies = ['idr', 'myr', 'usd'];
const storefrontCurrencyKey = 'meoxstore_currency';
const requestedCurrency = new URL(window.location.href).searchParams.get('currency');
let selectedStorefrontCurrency = storefrontCurrencies.includes(requestedCurrency)
	? requestedCurrency
	: localStorage.getItem(storefrontCurrencyKey);
if (!storefrontCurrencies.includes(selectedStorefrontCurrency)) {
	selectedStorefrontCurrency = 'idr';
}
if (storefrontCurrencies.includes(requestedCurrency)) {
	localStorage.setItem(storefrontCurrencyKey, requestedCurrency);
}

const updateStorefrontCurrencyLinks = function(currency) {
	document.querySelectorAll('a[href]').forEach(function(link) {
		const linkUrl = new URL(link.href, window.location.href);
		if (linkUrl.origin !== window.location.origin || !/\/(shop|games|cart|product-details)(\/|$)/.test(linkUrl.pathname)) {
			return;
		}

		if (currency === 'idr') {
			linkUrl.searchParams.delete('currency');
		} else {
			linkUrl.searchParams.set('currency', currency);
		}
		link.href = linkUrl.toString();
	});
};

const persistStorefrontCurrency = function(currency) {
	if (!storefrontCurrencies.includes(currency)) {
		return;
	}

	selectedStorefrontCurrency = currency;
	localStorage.setItem(storefrontCurrencyKey, currency);
	updateStorefrontCurrencyLinks(currency);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hidePreloader, { once: true });
} else {
    hidePreloader();
}

document.querySelectorAll('[data-storefront-shop-toggle]').forEach(function(toggle) {
	const menu = toggle.closest('.storefront-shop-menu');
	const dropdown = document.getElementById(toggle.getAttribute('aria-controls'));

	if (!menu || !dropdown) {
		return;
	}

	const setOpen = function(isOpen) {
		menu.classList.toggle('is-open', isOpen);
		if (isOpen) {
			menu.classList.remove('is-dismissed');
		}
		toggle.setAttribute('aria-expanded', String(isOpen));
	};

	toggle.addEventListener('click', function() {
		setOpen(toggle.getAttribute('aria-expanded') !== 'true');
	});

	menu.addEventListener('keydown', function(event) {
		if (event.key === 'Escape') {
			setOpen(false);
			toggle.focus();
			menu.classList.add('is-dismissed');
		}
	});

	menu.addEventListener('focusin', function() {
		menu.classList.remove('is-dismissed');
	});

	menu.addEventListener('pointerenter', function(event) {
		if (event.pointerType === 'mouse') {
			setOpen(true);
		}
	});
	menu.addEventListener('pointerleave', function(event) {
		if (event.pointerType === 'mouse' && !menu.contains(document.activeElement)) {
			setOpen(false);
		}
	});

	document.addEventListener('click', function(event) {
		if (!menu.contains(event.target)) {
			setOpen(false);
			toggle.blur();
		}
	});
});

const cartNotice = document.querySelector('[data-cart-notice]');
let cartNoticeTimeout;

const showCartNotice = function(message, isError) {
	if (!cartNotice) {
		return;
	}

	cartNotice.textContent = message;
	cartNotice.classList.toggle('is-error', isError);
	cartNotice.classList.add('is-visible');
	window.clearTimeout(cartNoticeTimeout);
	cartNoticeTimeout = window.setTimeout(function() {
		cartNotice.classList.remove('is-visible');
	}, 3500);
};

const submitCartAdd = async function(form, submitButton) {
	const buttonLabel = submitButton.matches('[data-cart-button-label]')
		? submitButton
		: submitButton.querySelector('[data-cart-button-label]');
	const submitButtons = Array.from(form.querySelectorAll('[type="submit"]'));

	if (!submitButtons.includes(submitButton) || submitButton.disabled) {
		return;
	}

	const originalLabel = buttonLabel?.textContent ?? submitButton.textContent;
	const disabledStates = submitButtons.map(function(button) {
		const wasDisabled = button.disabled;
		button.disabled = true;

		return wasDisabled;
	});
	if (buttonLabel) {
		buttonLabel.textContent = form.dataset.addingLabel;
	}

	try {
		const body = new FormData(form);

		const response = await fetch(form.action, {
			method: 'POST',
			body,
			credentials: 'same-origin',
			headers: {
				Accept: 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
			},
		});
		const contentType = response.headers.get('content-type') ?? '';

		if (!contentType.includes('application/json')) {
			throw new Error(form.dataset.requestErrorLabel);
		}

		const result = await response.json();
		if (!response.ok) {
			const validationMessage = result.errors
				? Object.values(result.errors).flat()[0]
				: result.message;
			showCartNotice(validationMessage || form.dataset.requestErrorLabel, true);
			if (buttonLabel) {
				buttonLabel.textContent = originalLabel;
			}
			return;
		}

		document.querySelectorAll('[data-storefront-cart-count]').forEach(function(count) {
			count.textContent = result.cart_count;
		});
		if (buttonLabel) {
			buttonLabel.textContent = form.dataset.addedLabel;
		}
		showCartNotice(result.message, false);
		window.setTimeout(function() {
			if (buttonLabel) {
				buttonLabel.textContent = originalLabel;
			}
		}, 1800);
	} catch (error) {
		console.error('Unable to add the selected product to the cart.', error);
		showCartNotice(form.dataset.requestErrorLabel, true);
		if (buttonLabel) {
			buttonLabel.textContent = originalLabel;
		}
	} finally {
		submitButtons.forEach(function(button, index) {
			button.disabled = disabledStates[index];
		});
	}
};

document.querySelectorAll('form[data-cart-add]').forEach(function(form) {
	form.addEventListener('submit', function(event) {
		if (event.submitter?.matches('[data-checkout-submit]')) {
			return;
		}

		event.preventDefault();

		const submitButton = event.submitter ?? form.querySelector('[type="submit"]');
		const submitButtons = Array.from(form.querySelectorAll('[type="submit"]'));

		if (!submitButtons.includes(submitButton) || submitButton.disabled) {
			return;
		}

		if (!form.reportValidity()) {
			return;
		}

		submitCartAdd(form, submitButton);
	});
});

const productVariants = document.querySelectorAll('[data-product-variant]');
const productPrice = document.querySelector('[data-product-price]');
const productStock = document.querySelector('[data-product-stock]');
const productCurrencyToggle = document.querySelector('[data-product-currency-toggle]');
const orderVariantId = document.querySelector('[data-order-variant-id]');
const orderCurrency = document.querySelector('[data-order-currency]');
const orderQuantity = document.querySelector('[data-order-quantity]');
const orderSubmit = document.querySelector('[data-order-submit]');

const productGalleryMain = document.querySelector('[data-product-gallery-main]');
if (productGalleryMain) {
	document.querySelectorAll('[data-product-gallery-thumbnail]').forEach(function(thumbnail) {
		thumbnail.addEventListener('click', function() {
			productGalleryMain.src = thumbnail.dataset.imageUrl;
			document.querySelectorAll('[data-product-gallery-thumbnail]').forEach(function(button) {
				button.setAttribute('aria-pressed', String(button === thumbnail));
			});
		});
	});
}

document.querySelectorAll('[data-stock-error-label]').forEach(function(quantityInput) {
	const updateQuantityValidity = function() {
		const exceedsStock = Number(quantityInput.value) > Number(quantityInput.dataset.stock);
		quantityInput.setCustomValidity(exceedsStock ? quantityInput.dataset.stockErrorLabel : '');
	};

	quantityInput.addEventListener('input', updateQuantityValidity);
	quantityInput.addEventListener('change', updateQuantityValidity);
});

if (productVariants.length && productPrice && productStock && productCurrencyToggle) {
	let selectedProductCurrency = selectedStorefrontCurrency;
	productCurrencyToggle.querySelectorAll('[data-product-currency]').forEach(function(currencyButton) {
		currencyButton.setAttribute('aria-pressed', String(currencyButton.dataset.productCurrency === selectedProductCurrency));
	});
	if (orderCurrency) {
		orderCurrency.value = selectedProductCurrency;
	}

	const updateProductVariant = function(selectedVariant) {
		if (!selectedVariant) {
			return;
		}

		productPrice.textContent = selectedVariant.dataset[selectedProductCurrency];
		const stock = Number(selectedVariant.dataset.stock);
		const isAvailable = stock > 0;

		productStock.textContent = isAvailable
			? productStock.dataset.availableLabel + stock.toLocaleString()
			: productStock.dataset.unavailableLabel;
		productStock.classList.toggle('is-unavailable', !isAvailable);

		if (orderVariantId && orderQuantity && orderSubmit) {
			orderVariantId.value = selectedVariant.value;
			orderQuantity.min = selectedVariant.dataset.minimum;
			orderQuantity.max = selectedVariant.dataset.maximum;
			orderQuantity.value = selectedVariant.dataset.minimum;
			orderQuantity.dataset.stock = selectedVariant.dataset.stock;
			orderQuantity.setCustomValidity('');
			document.querySelectorAll('[data-order-submit]').forEach(function(button) {
				button.disabled = !isAvailable;
			});
		}
	};

	productVariants.forEach(function(variantButton) {
		variantButton.addEventListener('click', function() {
			if (variantButton.disabled) {
				return;
			}

			productVariants.forEach(function(button) {
				button.setAttribute('aria-pressed', String(button === variantButton));
			});
			updateProductVariant(variantButton);
		});
	});

	productCurrencyToggle.addEventListener('click', function(event) {
		const button = event.target.closest('[data-product-currency]');
		if (!button) {
			return;
		}

		selectedProductCurrency = button.dataset.productCurrency;
		persistStorefrontCurrency(selectedProductCurrency);
		if (orderCurrency) {
			orderCurrency.value = selectedProductCurrency;
		}
		productCurrencyToggle.querySelectorAll('[data-product-currency]').forEach(function(currencyButton) {
			currencyButton.setAttribute('aria-pressed', String(currencyButton === button));
		});
		updateProductVariant(document.querySelector('[data-product-variant][aria-pressed="true"]'));
	});

	updateProductVariant(document.querySelector('[data-product-variant][aria-pressed="true"]'));
}

(function ($) {
	
	"use strict";

	var previousScroll = $(window).scrollTop();

	$(window).scroll(function() {
	  var scroll = $(window).scrollTop();
	  var box = $('.header-text').height();
	  var header = $('header.header-area').height();
	  var threshold = box === undefined ? 1 : box - header;

	  if (scroll >= threshold) {
	    $("header").addClass("background-header");
	  } else {
	    $("header").removeClass("background-header");
	  }

	  if (document.body.classList.contains("product-detail-page") || $("header.header-area").hasClass("header-scroll-toggle")) {
	    if (scroll <= 0 || scroll < previousScroll) {
	      $("header.header-area").removeClass("header-hidden");
	    } else if (scroll > header) {
	      $("header.header-area").addClass("header-hidden");
	    }
	  }

	  previousScroll = scroll;
	})

	var width = $(window).width();
		$(window).resize(function() {
		if (width > 767 && $(window).width() < 767) {
			location.reload();
		}
		else if (width < 767 && $(window).width() > 767) {
			location.reload();
		}
	})

	const elem = document.querySelector('.trending-box');
	const filtersElems = document.querySelectorAll('[data-shop-filter-group]');
	const currencyToggle = document.querySelector('[data-shop-currency-toggle]');
	if (currencyToggle) {
		const priceElements = document.querySelectorAll('[data-shop-price]');
		const updateShopCurrency = function(currency, updateUrl) {
			priceElements.forEach(function(price) {
				price.textContent = price.dataset[currency];
			});
			currencyToggle.querySelectorAll('[data-shop-currency]').forEach(function(currencyButton) {
				currencyButton.setAttribute('aria-pressed', String(currencyButton.dataset.shopCurrency === currency));
			});
			persistStorefrontCurrency(currency);

			if (updateUrl) {
				const shopUrl = new URL(window.location.href);
				if (currency === 'idr') {
					shopUrl.searchParams.delete('currency');
				} else {
					shopUrl.searchParams.set('currency', currency);
				}
				window.history.replaceState(null, '', shopUrl.toString());
			}
		};

		updateShopCurrency(selectedStorefrontCurrency, false);

		currencyToggle.addEventListener('click', function(event) {
			const button = event.target.closest('[data-shop-currency]');
			if (!button) {
				return;
			}

			updateShopCurrency(button.dataset.shopCurrency, true);
		});
	}

	if (elem) {
		const selectedFilters = new Map();
		const applyFilters = function() {
			const activeSelectors = Array.from(selectedFilters.values()).filter(function(selector) {
				return selector !== '*';
			});

			elem.querySelectorAll('.trending-items').forEach(function(item) {
				const matches = activeSelectors.every(function(selector) {
					return item.classList.contains(selector.slice(1));
				});

				item.hidden = !matches;
			});
		};

		filtersElems.forEach(function(filtersElem) {
			const activeButton = filtersElem.querySelector('.is_active');
			selectedFilters.set(filtersElem.dataset.shopFilterGroup, activeButton?.dataset.filter || '*');
			filtersElem.addEventListener('click', function(event) {
				const filterButton = event.target.closest('a[data-filter]');
				if (!filterButton) {
					return;
				}

				const activeButton = filtersElem.querySelector('.is_active');
				if (activeButton) {
					activeButton.classList.remove('is_active');
				}
				filterButton.classList.add('is_active');
				selectedFilters.set(filtersElem.dataset.shopFilterGroup, filterButton.dataset.filter);
				applyFilters();
				event.preventDefault();
			});
		});
		applyFilters();
	}


	// Menu Dropdown Toggle
	if($('.menu-trigger').length){
		$(".menu-trigger").on('click', function() {	
			const menuTrigger = $(this);
			const isExpanded = menuTrigger.attr('aria-expanded') === 'true';
			menuTrigger.toggleClass('active', !isExpanded);
			menuTrigger.attr('aria-expanded', String(!isExpanded));
			$('.header-area .main-nav > .nav').stop(true, true).slideToggle(200);
		});
	}


	// Menu elevator animation
	$('.scroll-to-section a[href*=\\#]:not([href=\\#])').on('click', function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
			var target = $(this.hash);
			target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
			if (target.length) {
				var width = $(window).width();
				if(width < 991) {
					$('.menu-trigger').removeClass('active');
					$('.header-area .nav').slideUp(200);	
				}				
				$('html,body').animate({
					scrollTop: (target.offset().top) - 80
				}, 700);
				return false;
			}
		}
	});


	// Page loading animation
	$(window).on('load', function() {
		if($('.cover').length){
			$('.cover').parallax({
				imageSrc: $('.cover').data('image'),
				zIndex: '1'
			});
		}

		$("#preloader").animate({
			'opacity': '0'
		}, 600, function(){
			setTimeout(function(){
				$("#preloader").css("visibility", "hidden").fadeOut();
			}, 300);
		});
	});
    


})(window.jQuery);