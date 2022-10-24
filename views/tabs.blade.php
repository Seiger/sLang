@php global $content, $richtexteditorIds, $richtexteditorOptions; @endphp

@include('sLang::resourceGeneralTab')
@include('sLang::resourceTemplateVariablesTab')
@include('sLang::resourceSettingsTab')

@foreach (sLang::siteContentFields() as $siteContentField)
    @if($siteContentField == 'content')
        <input name="ta" type="hidden" value="{!!$content[sLang::langDefault() . '_' . $siteContentField]!!}">
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
    .form-row .col-auto {padding-left:0;}
    .warning + [data-tooltip].fa-question-circle {margin:0.3rem 0.5rem 0;}
    .badge.bg-seigerit{background-color:#0057b8 !important;color:#ffd700;font-size:85%;}
</style>

<script>
    const btn = document.querySelectorAll('.js_translate');
    for (let i = 0; i < btn.length; i++) {
        btn[i].addEventListener("click", function(e) {
            this.disabled = true;
            window.parent.document.getElementById('mainloader').classList.add('show');

            let source = '{{sLang::langDefault()}}';
            let target = this.getAttribute('data-lang');
            let element = this.closest('.col').querySelector('[name^="'+target+'_"]');
            if (element.type == 'textarea') {
                tinymce.remove();
            }
            elementName = element.getAttribute('name').replace(target, '');
            let _text = document.querySelector('[name^="'+source+elementName+'"]').value;

            fetch('{!!sLang::moduleUrl()!!}&get=translates&action=translate-only', {
                body: new URLSearchParams({'text':_text, 'source':source, 'target':target}),
                method: "post",
                cache: "no-store",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }).then((response) => {
                return response.text();
            }).then((data) => {
                document.querySelector('[name="'+target+elementName+'"]').value = data;
                if (element.type == 'textarea') {
                    tinymce.init({{evo()->getConfig('tinymce5_theme') ?? 'custom'}});
                }
                this.disabled = false;
                window.parent.document.getElementById('mainloader').classList.remove('show');
            }).catch(function(error) {
                if (error == 'SyntaxError: Unexpected token < in JSON at position 0') {
                    console.error('Request failed SyntaxError: The response must contain a JSON string.');
                } else {
                    console.error('Request failed', error, '.');
                }
                this.disabled = false;
            });
        });
    }
</script>