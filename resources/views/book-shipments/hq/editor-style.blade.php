#book-shipment-editor {
    background: #fff;
    border: 1px solid #dbe2ec;
    border-radius: .45rem;
    box-shadow: 0 8px 24px rgba(21, 34, 56, .05);
}

#book-shipment-editor .book-shipment-card-header {
    border-bottom: 1px solid #dbe2ec;
    padding: 1rem 1.25rem;
}

#book-shipment-editor .book-shipment-card-header h2,
#book-shipment-editor .book-shipment-card-header h3,
#book-shipment-editor .book-shipment-selected h3 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
}

#book-shipment-editor .book-shipment-field label {
    color: #536174;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .03em;
    margin-bottom: .35rem;
    text-transform: uppercase;
}

#book-shipment-editor .book-shipment-button {
    background: #f2b84b;
    border-color: #f2b84b;
    color: #152238;
    font-weight: 700;
}

#book-shipment-editor .book-shipment-button:hover,
#book-shipment-editor .book-shipment-button:focus {
    background: #f5c866;
    border-color: #f5c866;
    color: #152238;
}

#book-shipment-editor .book-shipment-button:focus,
#book-shipment-editor button:focus,
#book-shipment-editor input:focus,
#book-shipment-editor select:focus {
    outline: 3px solid rgba(242, 184, 75, .55);
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
    border: 1px solid #dbe2ec;
    border-radius: .4rem;
    padding: .85rem;
}

#book-shipment-editor .book-shipment-selected-row {
    align-items: center;
    background: #fff;
    border: 1px solid #dbe2ec;
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
