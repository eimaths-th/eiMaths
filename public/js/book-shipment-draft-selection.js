function NormalizeBook(book) {
    if (!book || typeof book !== 'object') {
        return null;
    }

    const source_book_id = Number(book.source_book_id);
    if (!Number.isInteger(source_book_id) || source_book_id < 1) {
        return null;
    }

    return {
        ...book,
        source_book_id,
    };
}

export function CreateBookShipmentDraftSelection(initial_books = []) {
    const selected_books = new Map();

    const AddBook = (book) => {
        const normalized_book = NormalizeBook(book);
        if (!normalized_book) {
            return false;
        }

        selected_books.set(normalized_book.source_book_id, normalized_book);
        return true;
    };

    initial_books.forEach(AddBook);

    return {
        AddBook,
        RemoveBook: (source_book_id) => {
            const normalized_id = Number(source_book_id);
            if (!Number.isInteger(normalized_id) || normalized_id < 1) {
                return false;
            }

            return selected_books.delete(normalized_id);
        },
        HasBook: (source_book_id) => {
            const normalized_id = Number(source_book_id);
            return Number.isInteger(normalized_id)
                && normalized_id > 0
                && selected_books.has(normalized_id);
        },
        Books: () => Array.from(selected_books.values())
            .sort((left_book, right_book) => left_book.source_book_id - right_book.source_book_id)
            .map((book) => ({ ...book })),
        SourceBookIds: () => Array.from(selected_books.keys()).sort((left_id, right_id) => left_id - right_id),
        Count: () => selected_books.size,
    };
}
