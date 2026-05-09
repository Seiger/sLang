@php
    $manager = app(\EvoUI\Support\ManagerContext::class);
    $theme = $manager->theme();
    $themeMode = $manager->themeMode($theme);
    $themeClasses = $manager->themeClasses($theme);
    $moduleTitle = __('sLang::global.module_title') !== 'sLang::global.module_title' ? __('sLang::global.module_title') : __('sLang::global.slang');
@endphp

@include('evo::partials.assets')

<div
    class="evo-ui {{ $themeClasses }}"
    data-evo-ui-root
    data-theme="{{ $theme }}"
    data-theme-mode="{{ $themeMode }}"
>
    <livewire:slang.module-panel
        :tabs="$tabs"
        :active-tab="$get"
        :context="['moduleUrl' => $moduleUrl]"
    />
</div>
<script>
    (() => {
        const moduleTitle = @json($moduleTitle);

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
</script>
