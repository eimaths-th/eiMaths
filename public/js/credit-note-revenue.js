export function NormalizeRevenueRows(rows) {
    if (!Array.isArray(rows)) return [];
    return rows.map((row) => ({
        key: String(row?.payment_date ?? row?.month ?? row?.year ?? ''),
        gross: String(row?.total_fee_sum ?? '0'),
        issued: String(row?.issued_credit_note_total ?? '0.00'),
        cancelled: String(row?.cancelled_credit_note_total ?? '0.00'),
        net: String(row?.net_revenue ?? row?.total_fee_sum ?? '0'),
        receipt_count: Number.isFinite(Number(row?.total_transactions)) ? Number(row.total_transactions) : 0,
    }));
}

export function BuildRevenueSeries(rows) {
    return NormalizeRevenueRows(rows).reduce((series, row) => {
        series.labels.push(row.key);
        series.gross.push(row.gross);
        series.issued.push(row.issued);
        series.cancelled.push(row.cancelled);
        series.net.push(row.net);
        series.receipt_count.push(row.receipt_count);
        return series;
    }, { labels: [], gross: [], issued: [], cancelled: [], net: [], receipt_count: [] });
}

export function FormatRevenueAmount(value) {
    const normalized = String(value ?? '0');
    if (!/^-?\d+(?:\.\d{1,2})?$/.test(normalized)) return '0.00';
    return Number(normalized).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
