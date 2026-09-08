<style>
    #CreateModal .modal-body,
    #EditModal .modal-body {
        max-height: calc(100vh - 190px);
        overflow-y: auto;
    }

    .permission-panel {
        margin-top: .75rem;
    }

    .permission-toolbar {
        position: sticky;
        top: -1rem;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .75rem;
        margin-bottom: 1rem;
        background: #f8fafc;
        border: 1px solid #dbe4ee;
        border-radius: .6rem;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .06);
    }

    .permission-search-wrap {
        position: relative;
        flex: 1 1 300px;
        max-width: 430px;
    }

    .permission-search-wrap > i {
        position: absolute;
        left: .8rem;
        top: 50%;
        z-index: 2;
        color: #64748b;
        transform: translateY(-50%);
    }

    .permission-search {
        padding-left: 2.35rem;
    }

    .permission-toolbar-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .permission-total {
        color: #475569;
        white-space: nowrap;
        margin-right: .25rem;
    }

    .permission-groups-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    }

    .permission-group-card {
        overflow: hidden;
        background: #fff;
        border: 1px solid #dbe4ee;
        border-radius: .65rem;
        box-shadow: 0 2px 7px rgba(15, 23, 42, .05);
    }

    .permission-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1rem;
        background: #f1f7ff;
        border-bottom: 1px solid #dbeafe;
    }

    .permission-group-heading {
        display: flex;
        align-items: center;
        gap: .65rem;
        min-width: 0;
    }

    .permission-group-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 2rem;
        width: 2rem;
        height: 2rem;
        color: #0d6efd;
        background: #fff;
        border-radius: .5rem;
    }

    .permission-group-heading h5 {
        margin: 0;
        color: #1e293b;
        font-size: .98rem;
        font-weight: 700;
    }

    .permission-group-heading p {
        margin: .15rem 0 0;
        color: #64748b;
        font-size: .76rem;
        line-height: 1.25;
    }

    .permission-group-control {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex: 0 0 auto;
    }

    .permission-group-count {
        padding: .16rem .45rem;
        color: #1d4ed8;
        background: #dbeafe;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
    }

    .permission-group-control .custom-control-label {
        cursor: pointer;
        color: #334155;
        font-size: .78rem;
        white-space: nowrap;
    }

    .permission-group-items {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .5rem;
        padding: .75rem;
    }

    .permission-option {
        min-width: 0;
        padding: .55rem .65rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }

    .permission-option:hover {
        border-color: #93c5fd;
        box-shadow: 0 2px 6px rgba(37, 99, 235, .08);
    }

    .permission-option.is-selected {
        background: #eff6ff;
        border-color: #60a5fa;
    }

    .permission-option .custom-control-label {
        display: block;
        width: 100%;
        min-height: 2rem;
        cursor: pointer;
    }

    .permission-friendly-name,
    .permission-code {
        display: block;
        overflow-wrap: anywhere;
    }

    .permission-friendly-name {
        color: #1e293b;
        font-size: .86rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .permission-code {
        margin-top: .18rem;
        color: #64748b;
        font-family: Consolas, Monaco, monospace;
        font-size: .69rem;
        line-height: 1.2;
    }

    .permission-no-results {
        padding: 2rem;
        color: #64748b;
        text-align: center;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: .65rem;
    }

    @media (max-width: 1199.98px) {
        .permission-groups-grid,
        .permission-group-items {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .permission-toolbar,
        .permission-group-header {
            align-items: stretch;
            flex-direction: column;
        }

        .permission-search-wrap {
            flex-basis: auto;
            max-width: none;
        }

        .permission-toolbar-actions,
        .permission-group-control {
            justify-content: space-between;
        }
    }
</style>
