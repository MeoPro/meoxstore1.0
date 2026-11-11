@php
    $priceRateData = [
        'idr_per_myr' => (float) $currencyRates->idr_per_myr,
        'idr_per_usd' => (float) $currencyRates->idr_per_usd,
        'idr_per_robux' => (float) $currencyRates->idr_per_robux,
        'idr_per_gamepass_bobux' => (float) $currencyRates->idr_per_gamepass_bobux,
    ];
@endphp
<script>
    (() => {
        let rates = @json($priceRateData);
        const numberFormat = new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 });
        const currencyFormat = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const normalizePrice = (value) => {
            const cleaned = value.replace(/,/g, '').replace(/[^\d.]/g, '');
            const [whole = '0', ...fractionParts] = cleaned.split('.');
            const fraction = fractionParts.join('').slice(0, 2);

            return `${whole || '0'}${fractionParts.length > 0 ? `.${fraction}` : ''}`;
        };

        const normalizeRate = (value) => {
            const cleaned = value.replace(/,/g, '').replace(/[^\d.]/g, '');
            const [whole = '0', ...fractionParts] = cleaned.split('.');
            const fraction = fractionParts.join('').slice(0, 4);

            return `${whole || '0'}${fractionParts.length > 0 ? `.${fraction}` : ''}`;
        };

        const formatRate = (value) => {
            const [whole, fraction = ''] = String(value).split('.');
            const formattedWhole = numberFormat.format(Number(whole) || 0);
            const formattedFraction = fraction.replace(/0+$/, '');

            return formattedFraction ? `${formattedWhole}.${formattedFraction}` : formattedWhole;
        };

        const updatePreview = (group, value) => {
            const price = Number(value) || 0;
            const rateKey = group.dataset.bobuxType === 'gamepass_5_days'
                ? 'idr_per_gamepass_bobux'
                : 'idr_per_robux';
            const idrPrice = ['gift_ingame', 'gamepass_5_days'].includes(group.dataset.bobuxType)
                ? price * rates[rateKey]
                : price;
            group.querySelector('[data-price-idr]').textContent = `Rp ${numberFormat.format(idrPrice)}`;
            group.querySelector('[data-price-myr]').textContent = `≈ RM ${currencyFormat.format(idrPrice / rates.idr_per_myr)}`;
            group.querySelector('[data-price-usd]').textContent = `≈ $${currencyFormat.format(idrPrice / rates.idr_per_usd)}`;
        };

        const updateAllPreviews = () => {
            document.querySelectorAll('[data-price-group]').forEach((group) => {
                updatePreview(group, group.querySelector('[data-price-value]').value);
            });
        };

        document.querySelectorAll('[data-rate-group]').forEach((group) => {
            const display = group.querySelector('[data-rate-display]');
            const value = group.querySelector('[data-rate-value]');
            display.value = formatRate(value.value);
        });

        document.querySelectorAll('[data-price-group]').forEach((group) => {
            const display = group.querySelector('[data-price-display]');
            const value = group.querySelector('[data-price-value]');
            display.value = numberFormat.format(Number(value.value) || 0);
            updatePreview(group, value.value);
        });

        document.querySelectorAll('[data-variant-row]').forEach((row) => {
            const stock = row.querySelector('[data-stock-input]');
            const maximum = row.querySelector('[data-maximum-quantity]');
            if (stock && maximum) maximum.value = stock.value;
        });

        document.addEventListener('input', (event) => {
            if (event.target.matches('[data-rate-display]')) {
                const group = event.target.closest('[data-rate-group]');
                const normalized = normalizeRate(event.target.value);
                const value = group.querySelector('[data-rate-value]');
                value.value = normalized;
                event.target.value = formatRate(normalized);
                const rateKey = event.target.dataset.rateKey;
                rates[rateKey] = Number(normalized) || 0;
                updateAllPreviews();
            }

            if (event.target.matches('[data-price-display]')) {
                const group = event.target.closest('[data-price-group]');
                const normalized = normalizePrice(event.target.value);
                const value = group.querySelector('[data-price-value]');
                value.value = normalized;
                event.target.value = numberFormat.format(Number(normalized) || 0);
                updatePreview(group, normalized);
            }

            if (event.target.matches('[data-stock-input]')) {
                const row = event.target.closest('[data-variant-row]');
                const maximum = row.querySelector('[data-maximum-quantity]');
                if (maximum) maximum.value = event.target.value;
            }
        });
    })();
</script>