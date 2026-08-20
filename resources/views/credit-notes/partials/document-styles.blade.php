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
    color: #24313f;
    font-family: "Sarabun", "Tahoma", Arial, sans-serif;
    font-size: 10pt;
    line-height: 1.4;
}

.credit-note-print-shell {
    min-height: 100vh;
    padding: 1rem .75rem 2rem;
}

.credit-note-print-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 900px;
    min-height: 54px;
    margin: 0 auto;
    padding: .7rem 1rem;
    border: 0;
    border-radius: .25rem .25rem 0 0;
    background: #087ff5;
    box-shadow: 0 5px 14px rgba(0, 0, 0, .12);
}

.credit-note-print-toolbar-title {
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
}

.credit-note-print-toolbar .btn {
    display: inline-block;
    padding: .42rem .7rem;
    border: 1px solid transparent;
    border-radius: .25rem;
    font-family: inherit;
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.3;
    text-decoration: none;
}

.credit-note-print-toolbar .btn:focus-visible,
.credit-note-print-action:focus-visible {
    outline: 3px solid #1d2b3a;
    outline-offset: 2px;
}

.credit-note-print-toolbar .btn-outline {
    border-color: rgba(255, 255, 255, .75);
    background: transparent;
    color: #fff;
}

.credit-note-print-toolbar .btn-primary {
    border-color: #fff;
    background: #fff;
    color: #087ff5;
    cursor: pointer;
}

.credit-note-print-content {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
}

.credit-note-paper {
    position: relative;
    width: 100%;
    min-height: 270mm;
    margin: 0 auto;
    padding: 1.35rem 1.5rem .75rem;
    background: #fff;
    box-shadow: 0 8px 22px rgba(0, 0, 0, .13);
}

.credit-note-document {
    color: #24313f;
}

.credit-note-document > *:not(.credit-note-document-watermark) {
    position: relative;
    z-index: 1;
}

.credit-note-document-watermark {
    position: absolute;
    top: 42%;
    right: 8%;
    z-index: 0;
    color: rgba(255, 105, 0, .065);
    font-family: Arial, sans-serif;
    font-size: 4.6rem;
    font-weight: 800;
    letter-spacing: .18em;
    line-height: 1;
    pointer-events: none;
    transform: rotate(-22deg);
}

.credit-note-document-header {
    width: 100%;
    page-break-inside: avoid;
}

.credit-note-receipt-brand-table,
.credit-note-receipt-title-table,
.credit-note-receipt-information-table,
.credit-note-reference-fields,
.credit-note-items-table,
.credit-note-adjustment-table,
.credit-note-summary-table,
.credit-note-total-credit-table,
.credit-note-signature-table {
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
    padding: 0 .55rem 0 0;
    text-align: center;
}

.credit-note-receipt-issuer-cell {
    width: 58%;
    padding: .15rem .4rem 0 0;
    text-align: left;
}

.credit-note-receipt-eimaths-logo-cell {
    width: 27%;
    padding: .15rem 0 0 .4rem;
    text-align: right;
}

.credit-note-meta-logo {
    display: inline-block;
    width: 78px;
    height: 82px;
    object-fit: contain;
}

.credit-note-eimaths-logo {
    display: inline-block;
    width: auto;
    height: 58px;
    object-fit: contain;
}

.credit-note-issuer-name {
    margin: 0;
    color: #24313f;
    font-size: .94rem;
    font-weight: 700;
}

.credit-note-issuer-contact {
    margin: .2rem 0 0;
    color: #526273;
    font-size: .73rem;
    line-height: 1.45;
}

.credit-note-receipt-title-table {
    margin-top: -.05rem;
}

.credit-note-receipt-title-table td:first-child {
    width: 60%;
}

.credit-note-receipt-title-table td:last-child {
    width: 40%;
    padding: 0 0 .45rem;
    text-align: right;
}

.credit-note-receipt-title-table h1 {
    margin: 0;
    color: #ff6900;
    font-size: 1.02rem;
    font-weight: 800;
    letter-spacing: .01em;
    line-height: 1.25;
}

.credit-note-document-status {
    display: inline-block;
    margin-top: .25rem;
    padding: .1rem .45rem;
    border: 1px solid #e6b94d;
    border-radius: .15rem;
    background: #fff3cd;
    color: #664d03;
    font-family: Arial, sans-serif;
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.credit-note-document-status-issued {
    border-color: #86c79c;
    background: #e4f4e9;
    color: #176b35;
}

.credit-note-document-status-cancelled {
    border-color: #e5a6ac;
    background: #f8d7da;
    color: #842029;
}

.credit-note-receipt-information-table {
    margin: .2rem 0 .75rem;
    page-break-inside: avoid;
}

.credit-note-receipt-information-table > tbody > tr > td {
    padding: .18rem .5rem;
    border: 0;
    vertical-align: top;
}

.credit-note-buyer-cell {
    width: 55%;
}

.credit-note-reference-cell {
    width: 45%;
}

.credit-note-muted-label {
    display: block;
    margin-bottom: .15rem;
    color: #8d8a8a;
    font-size: .72rem;
}

.credit-note-buyer-name,
.credit-note-buyer-address,
.credit-note-buyer-tax {
    display: block;
    margin: .08rem 0;
    font-size: .77rem;
}

.credit-note-buyer-address {
    color: #425468;
    white-space: pre-line;
}

.credit-note-buyer-tax {
    color: #526273;
    font-size: .7rem;
}

.credit-note-reference-fields th,
.credit-note-reference-fields td {
    padding: .1rem 0 .1rem .45rem;
    border: 0;
    font-size: .72rem;
    line-height: 1.35;
    vertical-align: top;
}

.credit-note-reference-fields th {
    width: 67%;
    color: #8d8a8a;
    font-weight: 400;
    text-align: right;
}

.credit-note-reference-fields td {
    width: 33%;
    color: #24313f;
    font-weight: 700;
    text-align: left;
    white-space: nowrap;
}

.credit-note-document-section {
    margin-top: .65rem;
}

.credit-note-document-section h2 {
    margin: 0 0 .35rem;
    padding: 0;
    border-bottom: 0;
    color: #ff6900;
    font-size: .78rem;
    font-weight: 800;
}

.credit-note-line-items {
    page-break-inside: auto;
}

.credit-note-items-table {
    table-layout: fixed;
    font-size: .68rem;
}

.credit-note-items-table th {
    padding: .38rem .28rem;
    border: 1px solid #ff6900;
    background: #ff6900;
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    line-height: 1.25;
    text-align: center;
    vertical-align: middle;
}

.credit-note-items-table td {
    padding: .38rem .32rem;
    border: 0;
    border-bottom: 1px solid #f2c39f;
    color: #24313f;
    line-height: 1.3;
    vertical-align: top;
}

.credit-note-items-table tr {
    page-break-inside: avoid;
}

.credit-note-items-table small {
    display: block;
    margin-top: .08rem;
    color: #7a8794;
    font-size: .6rem;
}

.credit-note-col-number {
    width: 6%;
}

.credit-note-col-description {
    width: 30%;
}

.credit-note-col-quantity {
    width: 11%;
}

.credit-note-col-money {
    width: 14%;
}

.credit-note-items-table .text-center {
    text-align: center;
}

.credit-note-items-table .text-right {
    text-align: right;
    white-space: nowrap;
}

.credit-note-items-empty {
    color: #7a8794;
    text-align: center;
}

.credit-note-receipt-summary {
    margin-top: .75rem;
    page-break-inside: avoid;
}

.credit-note-adjustment-table {
    border-top: 2px solid #ff6900;
}

.credit-note-adjustment-table > tbody > tr > td {
    padding: .55rem .65rem;
    border: 0;
    vertical-align: top;
}

.credit-note-reason-cell {
    width: 43%;
}

.credit-note-summary-cell {
    width: 57%;
    border-left: 2px solid #ff6900 !important;
}

.credit-note-reason-list {
    margin: 0;
}

.credit-note-reason-list dt {
    margin: .35rem 0 .05rem;
    color: #8d8a8a;
    font-size: .66rem;
    font-weight: 400;
}

.credit-note-reason-list dt:first-child {
    margin-top: 0;
}

.credit-note-reason-list dd {
    margin: 0 0 .2rem;
    color: #24313f;
    font-size: .72rem;
    line-height: 1.4;
}

.credit-note-summary-table th,
.credit-note-summary-table td {
    padding: .15rem .15rem .15rem .45rem;
    border: 0;
    font-size: .69rem;
    line-height: 1.35;
}

.credit-note-summary-table th {
    width: 75%;
    color: #6f7b87;
    font-weight: 400;
    text-align: left;
}

.credit-note-summary-table td {
    width: 25%;
    color: #24313f;
    font-family: Arial, sans-serif;
    font-weight: 700;
    text-align: right;
    white-space: nowrap;
}

.credit-note-total-credit-table {
    border-top: 2px solid #ff6900;
    border-bottom: 2px solid #ff6900;
    page-break-inside: avoid;
}

.credit-note-total-credit-table th,
.credit-note-total-credit-table td {
    padding: .35rem .5rem;
    border: 0;
    vertical-align: middle;
}

.credit-note-total-credit-table th {
    width: 34%;
    color: #6f7b87;
    font-size: .76rem;
    font-weight: 600;
    text-align: center;
}

.credit-note-total-credit-copy {
    width: 39%;
    color: #526273;
    font-size: .69rem;
    text-align: center;
}

.credit-note-total-credit-value {
    width: 19%;
    color: #1f2933;
    font-family: Arial, sans-serif;
    font-size: .94rem;
    font-weight: 800;
    text-align: right;
    white-space: nowrap;
}

.credit-note-total-credit-unit {
    width: 8%;
    color: #8d8a8a;
    font-size: .67rem;
    text-align: right;
}

.credit-note-document-footer,
.credit-note-receipt-footer {
    margin-top: .8rem;
    padding-top: 0;
    border-top: 0;
    page-break-inside: avoid;
}

.credit-note-signature-table {
    text-align: center;
}

.credit-note-signature-table td {
    width: 50%;
    padding: .8rem 1rem 0;
    border: 0;
    color: #425468;
    font-size: .69rem;
    vertical-align: top;
}

.credit-note-signature-table strong,
.credit-note-signature-table span {
    display: block;
}

.credit-note-signature-line {
    margin-bottom: .12rem;
    color: #425468;
    letter-spacing: .04em;
}

.credit-note-receipt-banner {
    display: block;
    width: 100%;
    height: auto;
    max-height: 76px;
    margin: .7rem auto 0;
    object-fit: cover;
}

.credit-note-footer-note {
    margin: .35rem 0 0;
    color: #7a8794;
    font-size: .58rem;
    text-align: center;
}

.credit-note-print-actions {
    display: flex;
    justify-content: flex-end;
    gap: .5rem;
    width: 100%;
    max-width: 900px;
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

.credit-note-reason-footer {
    border-top: 0;
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
        min-height: auto;
        padding: 1rem;
    }

    .credit-note-items-table {
        font-size: .62rem;
    }

    .credit-note-items-table th {
        font-size: .6rem;
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

    .credit-note-print-toolbar,
    .credit-note-print-actions {
        display: none;
    }

    .credit-note-print-content,
    .credit-note-paper {
        width: auto;
        max-width: none;
        margin: 0;
    }

    .credit-note-paper {
        min-height: 0;
        padding: 0;
        box-shadow: none;
    }

    .credit-note-items-table th {
        background: #ff6900 !important;
        color: #fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

.credit-note-document-section h2 {
    border-bottom: 0;
}
