const numberFormat = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 });

function trimDecimals(value) {
    return Number(value.toFixed(2)).toString();
}

/**
 * South-Asian short form: 2,500,000 => "25 Lakh", 12,500,000 => "1.25 Crore".
 */
export function shortAmount(amount) {
    const value = Number(amount) || 0;
    if (value >= 10000000) return `${trimDecimals(value / 10000000)} Crore`;
    if (value >= 100000) return `${trimDecimals(value / 100000)} Lakh`;
    if (value >= 1000) return `${trimDecimals(value / 1000)}K`;
    return numberFormat.format(value);
}

export function money(amount, currency = window.SiteConfig?.currency ?? 'PKR', short = window.SiteConfig?.shortPrices ?? false) {
    if (amount === null || amount === undefined || amount === '') return '—';
    const value = Number(amount) || 0;
    return `${currency} ${short ? shortAmount(value) : numberFormat.format(value)}`.trim();
}

export function plural(count, word) {
    return `${count} ${word}${Number(count) === 1 ? '' : 's'}`;
}
