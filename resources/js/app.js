import './bootstrap';
import './custom';

const buyerTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
document.querySelectorAll('[data-buyer-timezone]').forEach((input) => {
    input.value = buyerTimezone;
});

const localDateTimeFormatter = new Intl.DateTimeFormat(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    timeZoneName: 'short',
});

document.querySelectorAll('time[data-local-time]').forEach((element) => {
    const date = new Date(element.dateTime);

    if (!Number.isNaN(date.getTime())) {
        element.textContent = localDateTimeFormatter.format(date);
    }
});
