<style>
    .nav-tabs .nav-item:first-child .nav-link {
        margin-left: 10px;
        /* Adjust the value to your desired space */
    }

    .modal-xxl {
        max-width: 1200px !important;
    }

    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }

    .overlay {
        position: fixed;
        display: none;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Semi-transparent black background */
        justify-content: center;
        align-items: center;
        z-index: 9999;
        /* Set a high z-index to make sure it's on top of everything */
    }

    .markdown-html-body p img {
        width: 50%;
        object-fit: cover;
        border-radius: 12px;
    }

    .markdown-html-body .youtube-embed {
        aspect-ratio: 16 / 9;
        margin: 1rem 0;
        max-width: 100%;
        width: 100%;
    }

    .markdown-html-body .youtube-embed iframe {
        height: 100%;
        width: 100%;
    }

    @media screen and (max-width: 768px) {
        .markdown-html-body p img {
            width: 100%;
        }
    }
</style>
