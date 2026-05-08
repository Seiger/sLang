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
    input[type=checkbox], input[type=radio] {padding:0.5em;}
    form#mutate input[name="menuindex"] {padding:0.5em;text-align:left;}
    .form-row .row-col {display:flex; flex-wrap:wrap; flex-direction:row; align-content:start; padding-right:0.75rem;}
    .form-row .row-col > .row:not(.col):not(.col-sm):not(.col-md):not(.col-lg):not(.col-xl) {-ms-flex:0 0 100%;flex:0 0 100%;max-width:100%;}
    .form-row-checkbox {align-items:center;}
    .form-row .col-title-6{width:6rem;}
    .form-row .col-title-7{width:7rem;}
    .form-row .col-title{width:8rem;}
    .form-row .col-title-9{width:9rem;}
    .form-row .col-title-10{width:10rem;}
    .form-row .col-title-11{width:11rem;}
    .form-row .col-auto {padding-left:0;}
    .warning + [data-tooltip].fa-question-circle {margin:0.3rem 0.5rem 0;}
    .badge.bg-seigerit{background-color:#0057b8!important;color:#ffd700;border-radius:0;padding:0.25rem;font-size:83%;}
</style>

<script>
    const which_editor = '{{evo()->getConfig("which_editor")}}';
    document.querySelectorAll('.js_translate').forEach( btn => {
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
