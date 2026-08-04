#book-shipment-editor {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: .25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
}

#book-shipment-editor .book-shipment-card-header {
    background: #007bff;
    border-bottom: 0;
    color: #fff;
    padding: .75rem 1.25rem;
}

#book-shipment-editor .book-shipment-card-header h2,
#book-shipment-editor .book-shipment-card-header h3,
#book-shipment-editor .book-shipment-selected h3 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
}

#book-shipment-editor .book-shipment-card-header h2,
#book-shipment-editor .book-shipment-card-header h3 {
    color: #fff;
}

#book-shipment-editor .book-shipment-card-header .book-shipment-eyebrow {
    color: rgba(255, 255, 255, .85) !important;
}

#book-shipment-editor .book-shipment-field label {
    color: #495057;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .03em;
    margin-bottom: .35rem;
    text-transform: uppercase;
}

#book-shipment-editor .book-shipment-button {
    background: #007bff;
    border-color: #007bff;
    color: #fff;
    font-weight: 700;
}

#book-shipment-editor .book-shipment-button:hover,
#book-shipment-editor .book-shipment-button:focus {
    background: #0069d9;
    border-color: #0062cc;
    color: #fff;
}

#book-shipment-editor .book-shipment-button:focus,
#book-shipment-editor button:focus,
#book-shipment-editor input:focus,
#book-shipment-editor select:focus {
    outline: 3px solid rgba(0, 123, 255, .25);
    outline-offset: 2px;
}

#book-shipment-editor .book-shipment-button:disabled {
    cursor: not-allowed;
    opacity: .65;
}

#book-shipment-editor .book-shipment-live {
    min-height: 1.5rem;
}

#book-shipment-editor .book-shipment-selected {
    background: #f8fafc;
    border: 1px solid #dee2e6;
    border-radius: .4rem;
    padding: .85rem;
}

#book-shipment-editor .book-shipment-selected-row {
    align-items: center;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: .35rem;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: .7rem .8rem;
}

#book-shipment-editor .book-shipment-selected-row + .book-shipment-selected-row {
    margin-top: .5rem;
}

#book-shipment-editor .book-shipment-selected-empty {
    padding: .35rem 0;
}

@media (max-width: 575.98px) {
    #book-shipment-editor .book-shipment-selected-row {
        align-items: flex-start;
        flex-direction: column;
    }
}
