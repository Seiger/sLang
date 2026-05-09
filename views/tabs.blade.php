@php global $content, $richtexteditorIds, $richtexteditorOptions; @endphp

@include('sLang::resourceGeneralTab')
@include('sLang::resourceTemplateVariablesTab')
@include('sLang::resourceSettingsTab')

@foreach (sLang::siteContentFields() as $siteContentField)
    @if($siteContentField == 'content')
        @if(evo()->getConfig('use_editor') && evo()->getConfig('which_editor') !== 'none')
            <input name="ta" type="hidden" value="{{$content[sLang::langDefault() . '_' . $siteContentField]}}">
        @endif
    @else
        <input name="{{$siteContentField}}" type="hidden" value="{!!$content[sLang::langDefault() . '_' . $siteContentField]!!}">
    @endif
@endforeach

<style>
    .slang-resource-tab-page input[type=checkbox], .slang-resource-tab-page input[type=radio] {padding:0.5em;}
    .slang-resource-tab-page input[name="menuindex"] {padding:0.5em;text-align:left;}
    .slang-resource-tab-page > .form-row:first-of-type {margin-top:0;}
    .slang-resource-tab-page .form-row {margin-bottom:0.25rem;}
    .slang-resource-tab-page .form-row label {margin-bottom:0.15rem;}
    .slang-resource-tab-page .form-row .row-col {display:flex; flex-wrap:wrap; flex-direction:row; align-content:start; padding-right:0.75rem;}
    .slang-resource-tab-page .form-row .row-col > .row:not(.col):not(.col-sm):not(.col-md):not(.col-lg):not(.col-xl) {-ms-flex:0 0 100%;flex:0 0 100%;max-width:100%;}
    .slang-resource-tab-page .form-row-checkbox {align-items:center;}
    .slang-resource-tab-page .form-row .col-title-6{width:6rem;}
    .slang-resource-tab-page .form-row .col-title-7{width:7rem;}
    .slang-resource-tab-page .form-row .col-title{width:8rem;}
    .slang-resource-tab-page .form-row .col-title-9{width:9rem;}
    .slang-resource-tab-page .form-row .col-title-10{width:10rem;}
    .slang-resource-tab-page .form-row .col-title-11{width:11rem;}
    .slang-resource-tab-page .form-row .col-auto {padding-left:0;}
    .slang-resource-tab-page .warning + [data-tooltip].fa-question-circle {margin:0.3rem 0.5rem 0;}
    .tab-row .slang-lang-badge, .tab-pane .slang-lang-badge{display:inline-block;background-color:#0057b8!important;color:#ffd700!important;border-radius:0;padding:0.25rem;font-size:83%;line-height:1;text-transform:uppercase;vertical-align:baseline;}
    .slang-resource-tab-page .slang-resource-input {min-width:0;flex:1 1 auto;}
    .slang-resource-tab-page .evo-ui-btn {display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:0 0.75rem;border:1px solid #d3d3d3;border-radius:0;background:#fff;color:#555;line-height:1;box-shadow:none;cursor:pointer;}
    .slang-resource-tab-page .evo-ui-btn:hover, .slang-resource-tab-page .evo-ui-btn:focus {border-color:#80bdff;color:#007bff;outline:0;box-shadow:0 0 0 0.2rem rgba(0,123,255,0.15);}
    .slang-resource-tab-page .evo-ui-btn:disabled {opacity:0.65;cursor:not-allowed;}
    .slang-resource-tab-page .evo-ui-btn--icon {width:36px;min-width:36px;padding:0;}
    .slang-resource-tab-page .evo-ui-btn--primary {color:#007bff;background:#fff;}
    .slang-resource-tab-page .evo-ui-btn__icon {width:18px;height:18px;display:block;}
    .slang-resource-tab-page .evo-ui-sr-only {position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
    .slang-resource-tab-page .slang-resource-translate {flex:0 0 auto;}
    .slang-resource-tab-page .input-group > .slang-resource-translate {border-top-left-radius:0;border-bottom-left-radius:0;}
    .slang-resource-tv-surface {width:100%;}
</style>

<script>
    const which_editor = '{{evo()->getConfig("which_editor")}}';
    window.sLangResourceTabs = window.sLangResourceTabs || {};
    window.sLangResourceTabs.markDirty = function () {
        window.documentDirty = true;
    };
    window.sLangResourceTabs.changeState = function (field) {
        if (document.mutate && document.mutate[field] && typeof window.changestate === 'function') {
            window.changestate(document.mutate[field]);
        }
        window.sLangResourceTabs.markDirty();
    };
    window.sLangResourceTabs.adjustMenuIndex = function (step) {
        if (!document.mutate || !document.mutate.menuindex) {
            return;
        }

        const input = document.mutate.menuindex;
        const next = parseInt(input.value + '', 10) + step;
        input.value = next > 0 ? next : 0;
        input.focus();
        window.sLangResourceTabs.markDirty();
    };
    window.sLangResourceTabs.clearField = function (field) {
        if (document.mutate && document.mutate[field]) {
            document.mutate[field].value = '';
            window.sLangResourceTabs.markDirty();
        }
    };

    document.addEventListener('change', (event) => {
        const dirtyTarget = event.target.closest('[data-slang-dirty]');
        if (dirtyTarget) {
            window.sLangResourceTabs.markDirty();
        }

        const stateTarget = event.target.closest('[data-slang-change-state]');
        if (stateTarget) {
            window.sLangResourceTabs.changeState(stateTarget.dataset.slangChangeState);
        }

        const resourceActionTarget = event.target.closest('[data-slang-resource-action]');
        if (
            resourceActionTarget
            && resourceActionTarget.dataset.slangResourceAction === 'template-warning'
            && typeof window.templateWarning === 'function'
        ) {
            window.templateWarning();
        }

        if (
            resourceActionTarget
            && resourceActionTarget.dataset.slangResourceAction === 'change-rte'
            && typeof window.changeRTE === 'function'
        ) {
            window.changeRTE();
        }
    });

    document.addEventListener('click', (event) => {
        const actionTarget = event.target.closest('[data-slang-resource-action]');
        if (!actionTarget) {
            return;
        }

        const action = actionTarget.dataset.slangResourceAction;

        if (action === 'select-parent' && typeof window.enableParentSelection === 'function') {
            window.enableParentSelection(!window.allowParentSelection);
            return;
        }

        if (action === 'select-link' && typeof window.enableLinkSelection === 'function') {
            window.enableLinkSelection(!window.allowLinkSelection);
            return;
        }

        if (action === 'browse-file' && typeof window.BrowseFileServer === 'function') {
            window.BrowseFileServer(actionTarget.dataset.slangTarget || '');
            return;
        }

        if (action === 'menuindex-step') {
            window.sLangResourceTabs.adjustMenuIndex(parseInt(actionTarget.dataset.slangStep || '0', 10));
            event.preventDefault();
            return;
        }

        if (action === 'clear-field') {
            window.sLangResourceTabs.clearField(actionTarget.dataset.slangField || '');
            event.preventDefault();
        }
    });

    document.querySelectorAll('[data-slang-translate]').forEach( btn => {
        btn.addEventListener("click", (e) => {
            let clicked = e.target.closest('button');
            clicked.disabled = true;
            window.parent.document.getElementById('mainloader').classList.add('show');

            let targetLang = clicked.dataset.lang;
            let element = clicked.closest('.col').querySelector(`[name^="${targetLang}_"]`);
            let elementName = element.getAttribute('name').replace(targetLang, '');
            let sourceField = defaultLang + elementName;
            let targetField = targetLang + elementName;
            let _text = (element.type == 'textarea' && which_editor != 'none' && tinymce.get(sourceField)) ? tinymce.get(sourceField).getContent() : document.querySelector(`[name="${sourceField}"]`).value;

            fetch('{!!sLang::moduleUrl()!!}&get=translates&action=translate-only', {
                body: new URLSearchParams({'text': _text, 'source': defaultLang, 'target': targetLang}),
                method: "post",
                cache: "no-store",
                headers: { "X-Requested-With": "XMLHttpRequest" }
            }).then((response) => {
                return response.text();
            }).then((data) => {
                if (element.type == 'textarea' && which_editor != 'none' && tinymce.get(sourceField)) {
                    tinymce.get(targetField).setContent(data);
                } else {
                    document.querySelector(`[name="${targetField}"]`).value = data;
                }
                clicked.disabled = false;
                window.parent.document.getElementById('mainloader').classList.remove('show');
            }).catch(function(error) {
                if (error == 'SyntaxError: Unexpected token < in JSON at position 0') {
                    console.error('Request failed SyntaxError: The response must contain a JSON string.');
                } else {
                    console.error('Request failed', error, '.');
                }
                clicked.disabled = false;
            });
        });
    });

    const defaultLang = '{{sLang::langDefault()}}';
    const mutateForm = document.querySelector('form#mutate');
    const defaultContent = document.querySelector('[data-slang-default-content="1"]') || document.querySelector('[name="' + defaultLang + '_content"]');
    const taProxy = document.querySelector('input[name="ta"][type="hidden"]');
    const defaultContentProxy = document.querySelector('[data-slang-default-content-proxy="1"]');

    function syncTaProxy() {
        if (!defaultContent) {
            return;
        }

        if (window.myCodeMirrors && window.myCodeMirrors.ta && typeof window.myCodeMirrors.ta.getValue === 'function') {
            const codeMirrorContent = window.myCodeMirrors.ta.getValue();
            if (defaultContent.value !== codeMirrorContent) {
                defaultContent.value = codeMirrorContent;
            }
            if (defaultContentProxy) {
                defaultContentProxy.value = codeMirrorContent;
            }
            return;
        }

        if (window.tinymce && typeof window.tinymce.get === 'function') {
            const editor = window.tinymce.get(defaultLang + '_content');
            if (editor) {
                const editorContent = editor.getContent();
                if (taProxy) {
                    taProxy.value = editorContent;
                }
                if (defaultContentProxy) {
                    defaultContentProxy.value = editorContent;
                }
                return;
            }
        }

        if (taProxy) {
            taProxy.value = defaultContent.value;
        }
        if (defaultContentProxy) {
            defaultContentProxy.value = defaultContent.value;
        }
    }

    if (defaultContent) {
        defaultContent.addEventListener('input', syncTaProxy);
        defaultContent.addEventListener('change', syncTaProxy);
    }

    if (mutateForm) {
        mutateForm.addEventListener('submit', syncTaProxy);
    }

    if (which_editor === 'none') {
        const syncAllContentEditors = () => {
            if (!window.myCodeMirrors) {
                syncTaProxy();
                return;
            }

            Object.entries(window.myCodeMirrors).forEach(([editorKey, editor]) => {
                if (!editor || typeof editor.getValue !== 'function') {
                    return;
                }

                const value = editor.getValue();

                if (editorKey === 'ta') {
                    if (defaultContent && defaultContent.value !== value) {
                        defaultContent.value = value;
                    }
                    if (defaultContentProxy) {
                        defaultContentProxy.value = value;
                    }
                    return;
                }

                const sourceTextarea = document.querySelector(`[data-slang-editor-key="${editorKey}"]`);
                if (sourceTextarea && sourceTextarea.value !== value) {
                    sourceTextarea.value = value;
                }
            });
        };

        document.addEventListener('click', () => {
            window.setTimeout(() => {
                Object.values(window.myCodeMirrors || {}).forEach((editor) => {
                    if (editor && typeof editor.refresh === 'function') {
                        editor.refresh();
                    }
                });
                syncAllContentEditors();
            }, 0);
        }, true);

        if (mutateForm) {
            mutateForm.addEventListener('submit', syncAllContentEditors);
        }
    }
</script>

@if(evo()->getConfig('which_editor') === 'none')
    @php($codeMirrorTargets = [])
    @foreach(sLang::langConfig() as $lang)
        @if($lang !== sLang::langDefault())
            @php($codeMirrorTargets[] = $lang . '_content')
        @endif
    @endforeach
    @php($codeMirrorInit = evo()->invokeEvent('OnRichTextEditorInit', [
        'editor' => 'Codemirror',
        'elements' => $codeMirrorTargets,
        'options' => [],
        'contentType' => 'text/html',
    ]))
    @if(is_array($codeMirrorInit))
        {!! implode('', $codeMirrorInit) !!}
    @endif
@endif
