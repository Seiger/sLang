(() => {
    const root = document.querySelector('[data-slang-module-title]');
    const moduleTitle = root?.dataset?.slangModuleTitle || '';

    if (!moduleTitle) {
        return;
    }

    const syncDocument = (doc) => {
        if (!doc?.querySelectorAll) {
            return;
        }

        doc.title = moduleTitle;
    };

    const syncManagerChrome = () => {
        syncDocument(document);

        let frame = window;

        for (let level = 0; level < 5; level += 1) {
            try {
                if (!frame.parent || frame.parent === frame) {
                    break;
                }

                frame = frame.parent;
                syncDocument(frame.document);
            } catch (error) {
                break;
            }
        }
    };

    syncManagerChrome();
    window.addEventListener('load', syncManagerChrome);
    window.setTimeout(syncManagerChrome, 150);
    window.setTimeout(syncManagerChrome, 600);

    if (document.body && window.MutationObserver) {
        new MutationObserver(syncManagerChrome).observe(document.body, {
            childList: true,
            subtree: true,
        });
    }
})();
