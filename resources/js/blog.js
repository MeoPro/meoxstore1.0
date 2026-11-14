import './owl';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('img[data-blog-cover]').forEach((image) => {
        image.addEventListener('error', () => {
            if (image.dataset.blogFallbackApplied === 'true') {
                return;
            }

            image.dataset.blogFallbackApplied = 'true';
            image.src = image.dataset.fallbackSrc;
        });
    });

    const $ = window.jQuery;

    if (!$ || !$('.owl-banner').length) {
        return;
    }

    const itemCount = $('.owl-banner .item').length;

    $('.owl-banner').owlCarousel({
        loop: itemCount > 1,
        nav: true,
        dots: true,
        items: 3,
        margin: 10,
        autoplay: false,
        smartSpeed: 700,
        responsive: {
            0: { items: 1, margin: 0 },
            576: { items: 1, margin: 10 },
            992: { items: 3, margin: 10 },
        },
    });
});
