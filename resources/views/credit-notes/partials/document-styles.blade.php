@page {
    size: A4;
    margin: 12mm;
}

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}

body.credit-note-print-body {
    background: #eef2f6;
    color: #1d2b3a;
    font-family: "Sarabun", Arial, "Tahoma", sans-serif;
    font-size: 10pt;
    line-height: 1.45;
}

.credit-note-print-shell {
    min-height: 100vh;
    padding: 1.5rem 1rem 3rem;
}

.credit-note-print-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: min(210mm, 100%);
    margin: 0 auto 1rem;
    padding: .65rem .85rem;
    border: 1px solid #d6e0e9;
    border-radius: .35rem;
    background: #fff;
    box-shadow: 0 5px 18px rgba(24, 39, 58, .06);
}

.credit-note-print-toolbar-title {
    color: #536476;
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.credit-note-print-toolbar .btn {
    display: inline-block;
    padding: .42rem .7rem;
    border: 1px solid transparent;
    border-radius: .25rem;
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.3;
    text-decoration: none;
}

.credit-note-print-toolbar .btn-outline {
    border-color: #b8c5d2;
    background: #fff;
    color: #405264;
}

.credit-note-print-toolbar .btn-primary {
    border-color: #007bff;
    background: #007bff;
    color: #fff;
    cursor: pointer;
}

.credit-note-print-content {
    width: min(210mm, 100%);
    margin: 0 auto;
}

.credit-note-paper {
    position: relative;
    width: 210mm;
    min-height: 270mm;
    margin: 0 auto;
    padding: 13mm 14mm 12mm;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(24, 39, 58, .12);
}

.credit-note-document {
    color: #1d2b3a;
}

.credit-note-document-header {
    display: table;
    width: 100%;
    padding-bottom: 1rem;
    border-bottom: 3px solid #007bff;
}

.credit-note-brand-block,
.credit-note-title-block {
    display: table-cell;
    vertical-align: top;
}

.credit-note-brand-block {
    width: 57%;
}

.credit-note-brand-row {
    display: table;
    width: 100%;
}

.credit-note-meta-logo {
    display: table-cell;
    width: 48px;
    height: 52px;
    padding-right: .6rem;
    object-fit: contain;
    vertical-align: top;
}

.credit-note-brand-row > div {
    display: table-cell;
    vertical-align: top;
}

.credit-note-issuer-name {
    display: block;
    margin: 0;
    color: #1d2b3a;
    font-size: 1.05rem;
    font-weight: 700;
}

.credit-note-issuer-tagline {
    display: block;
    margin-top: .25rem;
    color: #667585;
    font-size: .75rem;
}

.credit-note-issuer-contact {
    margin: .45rem 0 0;
    color: #667585;
    font-size: .68rem;
    line-height: 1.35;
}

.credit-note-title-block {
    width: 43%;
    text-align: right;
}

.credit-note-eimaths-logo {
    display: inline-block;
    width: auto;
    height: 42px;
    margin-bottom: .2rem;
    object-fit: contain;
}

.credit-note-title-block h1 {
    margin: 0;
    color: #005cbf;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: .02em;
    line-height: 1.1;
}

.credit-note-title-block p {
    margin: .35rem 0 0;
    color: #667585;
    font-size: .73rem;
}

.credit-note-document-status {
    display: inline-block;
    margin-top: .45rem;
    padding: .22rem .55rem;
    border: 1px solid #e6b94d;
    border-radius: 999px;
    background: #fff3cd;
    color: #664d03;
    font-size: .66rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.credit-note-document-status-issued {
    border-color: #86c79c;
    background: #d1e7dd;
    color: #0f5132;
}

.credit-note-document-status-cancelled {
    border-color: #e5a6ac;
    background: #f8d7da;
    color: #842029;
}

.credit-note-document-watermark {
    position: absolute;
    top: 38%;
    right: 7%;
    z-index: 0;
    color: rgba(0, 123, 255, .055);
    font-size: 4.5rem;
    font-weight: 800;
    letter-spacing: .2em;
    line-height: 1;
    pointer-events: none;
    transform: rotate(-22deg);
}

.credit-note-document > *:not(.credit-note-document-watermark) {
    position: relative;
    z-index: 1;
}

.credit-note-document-meta {
    display: table;
    width: 100%;
    margin: 1rem 0;
    border: 1px solid #dce4eb;
    border-radius: .25rem;
    background: #f7faff;
}

.credit-note-document-meta-item {
    display: table-cell;
    width: 25%;
    padding: .65rem .75rem;
    border-right: 1px solid #dce4eb;
    vertical-align: top;
}

.credit-note-document-meta-item:last-child {
    border-right: 0;
}

.credit-note-document-meta-label,
.credit-note-party-label,
.credit-note-summary-label {
    display: block;
    color: #667585;
    font-size: .64rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.credit-note-document-meta-value {
    display: block;
    margin-top: .25rem;
    color: #1d2b3a;
    font-size: .82rem;
    font-weight: 700;
}

.credit-note-party-table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: separate;
    border-spacing: .75rem 0;
}

.credit-note-party-table td {
    width: 50%;
    padding: .8rem .9rem;
    border: 1px solid #dce4eb;
    vertical-align: top;
}

.credit-note-party-table td:first-child {
    border-top: 3px solid #f39c12;
}

.credit-note-party-table td:last-child {
    border-top: 3px solid #007bff;
}

.credit-note-party-label {
    margin-bottom: .35rem;
}

.credit-note-party-name {
    margin: 0 0 .25rem;
    color: #1d2b3a;
    font-size: .95rem;
    font-weight: 700;
}

.credit-note-party-table p {
    margin: .15rem 0;
    color: #425468;
    font-size: .76rem;
    white-space: pre-line;
}

.credit-note-document-section {
    margin-top: 1rem;
}

.credit-note-document-section h2 {
    margin: 0 0 .55rem;
    padding-bottom: .35rem;
    border-bottom: 1px solid #dce4eb;
    color: #005cbf;
    font-size: .95rem;
    font-weight: 800;
}

.credit-note-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .76rem;
}

.credit-note-items-table th {
    padding: .55rem .5rem;
    border: 1px solid #005cbf;
    background: #005cbf;
    color: #fff;
    font-size: .67rem;
    font-weight: 700;
    letter-spacing: .03em;
    text-align: left;
    text-transform: uppercase;
}

.credit-note-items-table td {
    padding: .55rem .5rem;
    border: 1px solid #dce4eb;
    vertical-align: top;
}

.credit-note-items-table tbody tr:nth-child(even) td {
    background: #f8fafc;
}

.credit-note-items-table .text-right {
    text-align: right;
    white-space: nowrap;
}

.credit-note-items-empty {
    color: #667585;
    text-align: center;
}

.credit-note-summary-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: .45rem;
    margin: -.45rem;
}

.credit-note-summary-table td {
    width: 25%;
    padding: .65rem .7rem;
    border: 1px solid #dce4eb;
    border-left: 3px solid #b4c1ce;
    background: #fbfcfd;
    vertical-align: top;
}

.credit-note-summary-table td.credit-note-summary-highlight {
    border-left-color: #f39c12;
    background: #fffaf0;
}

.credit-note-summary-table td.credit-note-summary-primary {
    border-left-color: #007bff;
    background: #f5f9ff;
}

.credit-note-summary-value {
    display: block;
    margin-top: .22rem;
    color: #1d2b3a;
    font-size: .95rem;
    font-weight: 700;
}

.credit-note-summary-unit {
    color: #667585;
    font-size: .68rem;
}

.credit-note-reason-box {
    padding: .7rem .85rem;
    border-left: 3px solid #f39c12;
    background: #fffaf0;
}

.credit-note-reason-box p {
    margin: .2rem 0;
    font-size: .77rem;
}

.credit-note-document-footer {
    margin-top: 1.6rem;
    padding-top: .85rem;
    border-top: 1px solid #dce4eb;
}

.credit-note-signature-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 1rem 0;
    text-align: center;
}

.credit-note-signature-table td {
    width: 50%;
    padding-top: 1.75rem;
    border-top: 1px solid #718090;
    color: #425468;
    font-size: .72rem;
}

.credit-note-footer-note {
    margin: 1rem 0 0;
    color: #667585;
    font-size: .65rem;
    text-align: center;
}

/* Receipt-compatible print treatment. The orange table and banner intentionally
 * reuse the visual language of the existing receipt print template. */
.credit-note-print-shell {
    padding: 1rem .75rem 2rem;
}

.credit-note-print-toolbar {
    width: min(900px, 100%);
    min-height: 54px;
    margin-bottom: 0;
    padding: .7rem 1rem;
    border: 0;
    border-radius: .25rem .25rem 0 0;
    background: #087ff5;
    box-shadow: 0 5px 14px rgba(0, 0, 0, .12);
}

.credit-note-print-toolbar-title {
    color: #fff;
    font-size: 1rem;
    letter-spacing: 0;
    text-transform: none;
}

.credit-note-print-toolbar .btn-outline {
    border-color: rgba(255, 255, 255, .7);
    background: transparent;
    color: #fff;
}

.credit-note-print-toolbar .btn-primary {
    border-color: #fff;
    background: #fff;
    color: #087ff5;
}

.credit-note-paper {
    width: min(900px, 100%);
    min-height: 0;
    padding: 1.45rem 1.5rem .75rem;
    box-shadow: 0 8px 22px rgba(0, 0, 0, .13);
}

.credit-note-document-header {
    display: block;
    width: 100%;
    padding-bottom: .35rem;
    border-bottom: 0;
}

.credit-note-receipt-brand-table,
.credit-note-receipt-title-table {
    width: 100%;
    border-collapse: collapse;
}

.credit-note-receipt-brand-table td,
.credit-note-receipt-title-table td {
    border: 0;
    vertical-align: top;
}

.credit-note-receipt-meta-logo-cell {
    width: 15%;
    padding: 0 .5rem 0 0;
    text-align: center;
}

.credit-note-receipt-issuer-cell {
    width: 55%;
    padding: .1rem .35rem 0 0;
    text-align: left;
}

.credit-note-receipt-eimaths-logo-cell {
    width: 30%;
    padding: .1rem 0 0 .35rem;
    text-align: right;
}

.credit-note-receipt-title-table td:last-child {
    width: 42%;
    padding: .25rem 0 .35rem;
    text-align: right;
}

.credit-note-brand-block {
    width: 58%;
}

.credit-note-meta-logo {
    display: inline-block;
    width: 74px;
    height: 78px;
    padding-right: 0;
}

.credit-note-issuer-name {
    font-size: 1rem;
}

.credit-note-issuer-tagline,
.credit-note-issuer-contact {
    font-size: .78rem;
}

.credit-note-title-block {
    width: 42%;
}

.credit-note-eimaths-logo {
    height: 56px;
}

.credit-note-title-block h1 {
    color: #ff6900;
    font-size: 1.28rem;
}

.credit-note-title-block p {
    color: #ff6900;
    font-size: .86rem;
    font-weight: 700;
}

.credit-note-document-status {
    margin-top: .2rem;
    padding: .1rem .45rem;
    border-radius: .15rem;
    font-size: .7rem;
}

.credit-note-document-meta {
    margin: .45rem 0 .7rem;
    border: 0;
    border-radius: 0;
    background: transparent;
}

.credit-note-document-meta-item {
    padding: .35rem .55rem;
    border-right: 0;
}

.credit-note-document-meta-label,
.credit-note-party-label,
.credit-note-summary-label {
    font-size: .76rem;
    font-weight: 400;
    letter-spacing: 0;
    text-transform: none;
}

.credit-note-document-meta-value {
    margin-top: .05rem;
    font-size: .86rem;
}

.credit-note-party-table {
    margin-bottom: .85rem;
    border-spacing: 0;
}

.credit-note-party-table td,
.credit-note-party-table td:first-child,
.credit-note-party-table td:last-child {
    padding: .2rem .55rem;
    border: 0;
    border-top: 0;
}

.credit-note-party-table td:first-child {
    width: 55%;
}

.credit-note-party-table td:last-child {
    width: 45%;
    text-align: right;
}

.credit-note-party-label {
    color: #8d8a8a;
}

.credit-note-party-name {
    font-size: .92rem;
}

.credit-note-party-table p {
    font-size: .82rem;
}

.credit-note-document-section {
    margin-top: .7rem;
}

.credit-note-document-section h2 {
    margin-bottom: .4rem;
    padding-bottom: .25rem;
    border-bottom: 2px solid #ff6900;
    color: #ff6900;
    font-size: .9rem;
}

.credit-note-items-table {
    font-size: .78rem;
}

.credit-note-items-table th,
.credit-note-orange-table th {
    padding: .45rem .4rem;
    border-color: #ff6900;
    background: #ff6900;
    font-size: .76rem;
    text-align: center;
    text-transform: none;
}

.credit-note-items-table td {
    padding: .42rem .4rem;
    border-color: #ff6900;
}

.credit-note-items-table tbody tr:nth-child(even) td {
    background: #fff;
}

.credit-note-summary-table {
    margin: -.25rem;
    border-spacing: .25rem;
}

.credit-note-receipt-summary {
    padding: .35rem 0;
    border-top: 2px solid #ff6900;
    border-bottom: 2px solid #ff6900;
}

.credit-note-summary-table td {
    padding: .4rem .55rem;
    border: 0;
    border-left: 0;
    background: transparent;
}

.credit-note-summary-table td.credit-note-summary-highlight,
.credit-note-summary-table td.credit-note-summary-primary {
    border-left: 0;
    background: transparent;
}

.credit-note-summary-value {
    margin-top: .08rem;
    font-size: .92rem;
}

.credit-note-reason-box {
    padding: .45rem .65rem;
    border-left: 2px solid #ff6900;
    background: #fffaf4;
}

.credit-note-reason-box p {
    font-size: .78rem;
}

.credit-note-document-footer,
.credit-note-receipt-footer {
    margin-top: .85rem;
    padding-top: .35rem;
    border-top: 2px solid #ff6900;
}

.credit-note-signature-table {
    border-spacing: 1rem 0;
}

.credit-note-signature-table td {
    padding-top: 1.2rem;
    border-top: 0;
    font-size: .8rem;
}

.credit-note-receipt-banner {
    display: block;
    width: 100%;
    height: auto;
    max-height: 72px;
    margin: .75rem auto 0;
    object-fit: cover;
}

.credit-note-print-actions {
    display: flex;
    justify-content: flex-end;
    gap: .5rem;
    width: min(900px, 100%);
    margin: 0 auto;
    padding: .75rem 1rem;
    border-top: 1px solid #e5e5e5;
    background: #fff;
}

.credit-note-print-action {
    display: inline-block;
    padding: .5rem .85rem;
    border: 0;
    border-radius: .25rem;
    font-family: inherit;
    font-size: .84rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.credit-note-print-action-primary {
    background: #28a745;
    color: #fff;
}

.credit-note-print-action-danger {
    background: #dc3545;
    color: #fff;
}

@media (max-width: 900px) {
    .credit-note-print-shell {
        padding: .75rem;
    }

    .credit-note-print-toolbar {
        align-items: stretch;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .credit-note-print-toolbar-title {
        order: -1;
        width: 100%;
    }

    .credit-note-paper {
        width: 100%;
        min-height: auto;
        padding: 1rem;
    }
}

@media print {
    body.credit-note-print-body {
        background: #fff;
    }

    .credit-note-print-shell {
        min-height: 0;
        padding: 0;
    }

    .credit-note-print-toolbar {
        display: none;
    }

    .credit-note-print-content,
    .credit-note-paper {
        width: auto;
        margin: 0;
    }

    .credit-note-paper {
        min-height: 0;
        padding: 0;
        box-shadow: none;
    }

    .credit-note-print-actions {
        display: none;
    }
}
