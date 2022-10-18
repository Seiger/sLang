@php global $content, $richtexteditorIds, $richtexteditorOptions; @endphp

@include('sLang::resourceGeneralTab')

@include('sLang::resourceSettingsTab')

@foreach (sLang::siteContentFields() as $siteContentField)
    @if($siteContentField == 'content')
        <input name="ta" type="hidden" value="{!!$content[sLang::langDefault() . '_' . $siteContentField]!!}">
    @else
        <input name="{{$siteContentField}}" type="hidden" value="{!!$content[sLang::langDefault() . '_' . $siteContentField]!!}">
    @endif
@endforeach

<style>
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
            let element = this.closest('td').querySelector('[name^="'+target+'_"]').getAttribute('name').replace(target, '');
            let _text = document.querySelector('[name^="'+source+element+'"]').value;

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
                document.querySelector('[name="'+target+element+'"]').value = data;
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