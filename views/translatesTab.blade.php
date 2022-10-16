<form action="{!!$url!!}&get=translates&action=save" method="post">
    <div class="input-group">
        <input type="text" class="form-control" name="search" value="{{request()->search ?? ''}}" />
        <span class="input-group-btn">
            <button class="btn btn-light js_search" type="button" title="Search" style="padding:0 5px;color:#0275d8;">
                <i class="fa fa-search" style="font-size:large;margin:5px;"></i>
            </button>
        </span>
    </div>
    <div class="split my-1"></div>
    <div class="table-responsive langTable">
        <table class="table table-condensed table-hover sectionTrans">
            <thead>
            <tr>
                <td style="text-align:center !important;"><b>KEY</b></td>
                @foreach(sLang::langConfig() as $langConfig)
                    <td style="text-align:center !important;"><b>{{strtoupper($langConfig)}}</b></td>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($sLangController->dictionary() as $dictionary)
                <tr>
                    <td>{{$dictionary['key']}}</td>
                    @foreach(sLang::langConfig() as $langConfig)
                        <td data-tid="{{$dictionary['tid']}}" data-lang="{{$langConfig}}">
                            @if($langConfig == sLang::langDefault())
                                <input type="text" class="form-control" name="sLang[{{$dictionary['tid']}}][{{$langConfig}}]" value="{{$dictionary[$langConfig]}}" />
                            @else
                                <div class="input-group">
                                    <input type="text" class="form-control" name="sLang[{{$dictionary['tid']}}][{{$langConfig}}]" value="{{$dictionary[$langConfig]}}" />
                                    <span class="input-group-btn">
                                        <button class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($langConfig)}}" style="padding:0 5px;color:#0275d8;">
                                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                                        </button>
                                    </span>
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</form>
{{$sLangController->dictionary()->render()}}
<div class="modal fade" id="addTranslate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <form>
            <div class="modal-content">
                <div class="modal-header">@lang('sLang::global.add_translation')</div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text" style="padding: 0.25rem 0.75rem;">KEY</span>
                        <input type="text" name="translate[key]" class="form-control" value="">
                    </div>
                    @foreach(sLang::langConfig() as $langConfig)
                        <div class="input-group mb-3">
                            <span class="input-group-text" style="padding: 0.25rem 0.75rem;">{{strtoupper($langConfig)}}</span>
                            <input type="text" name="translate[{{$langConfig}}]" class="form-control" value="">
                            @if(sLang::langDefault() != $langConfig)
                                <span class="input-group-btn">
                                    <button data-lang="{{$langConfig}}" class="btn btn-light js_translate_only" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($langConfig)}}" style="padding:0 5px;color:#0275d8;">
                                        <i class="fa fa-language" style="font-size: xx-large;"></i>
                                    </button>
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('global.cancel')</button>
                    <span class="btn btn-success js_add_translation">@lang('global.add')</span>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts.bot')
    <div id="actions">
        <div class="btn-group">
            <a id="Button2" class="btn btn-primary" href="#" data-toggle="modal" data-target="#addTranslate">
                <i class="fa fa-plus"></i>
                <span>@lang('global.add')</span>
            </a>
            @if(evo()->hasPermission('settings'))
                <a id="Button1" href="{!!$url!!}&get=translates&action=synchronize" class="btn btn-success" title="@lang('sLang::global.synchronize_help')">
                    <i class="fa fa-sync-alt"></i>
                    <span>@lang('sLang::global.synchronize')</span>
                </a>
            @endif
        </div>
    </div>
    <script>
        jQuery(document).on("click", ".js_translate", function () {
            var _this = jQuery(this).parents('td');
            var source = _this.data('tid');
            var target = _this.data('lang');

            jQuery.ajax({
                url: '{!!$url!!}&get=translates&action=translate',
                type: 'POST',
                data: 'source=' + source + '&target=' + target,
                success: function (ajax) {
                    _this.find('input').val(ajax);
                }
            });
        });

        jQuery(".sectionTrans").on("blur", "input", function () {
            var _this = $(this).parents('td');
            var source = _this.data('tid');
            var target = _this.data('lang');
            var _value = _this.find('input').val();

            jQuery.ajax({
                url: '{!!$url!!}&get=translates&action=update',
                type: 'POST',
                data: 'source=' + source + '&target=' + target + '&value=' + _value,
            });
        });

        jQuery('.langTable tbody td:first-child').each(function () {
            hgs = Math.ceil($(this).outerHeight());
            if (hgs > 70) {
                jQuery(this).parent().attr('style', 'height: '+(hgs/2-10)+'px;');
            }
        });

        jQuery(document).on("keyup", "#addTranslate [name=\"translate[key]\"]", function () {
            jQuery(document).find("#addTranslate [name=\"translate[{{sLang::langDefault()}}]\"]").val(jQuery(this).val());
        });

        jQuery(document).on("click", ".js_translate_only", function () {
            var _this = jQuery(this);
            var source = '{{sLang::langDefault()}}';
            var target = _this.data('lang');
            var _text = jQuery(document).find("#addTranslate [name=\"translate[{{sLang::langDefault()}}]\"]").val();

            jQuery.ajax({
                url: '{!!$url!!}&get=translates&action=translate-only',
                type: 'POST',
                data: 'text=' + _text + '&source=' + source + '&target=' + target,
                success: function (ajax) {
                    _this.parent().parent().find('input').val(ajax);
                }
            });
        });

        jQuery(document).on("click", ".js_add_translation", function () {
            var _form = jQuery(document).find("#addTranslate form");

            jQuery.ajax({
                url: '{!!$url!!}&get=translates&action=add-new',
                type: 'POST',
                data: _form.serialize(),
                cache: false,
                success: function (ajax) {
                    $('.sectionTrans tbody').prepend(ajax);
                    $('#addTranslate').modal('hide');
                }
            });
        });

        /*jQuery(document).on("keyup", "[name=\"search\"]", function () {
            var _form = jQuery(this);

            jQuery.ajax({
                url: '{!!$url!!}&get=translates&action=search',
                type: 'POST',
                data: _form.serialize(),
                cache: false,
                success: function (ajax) {}
            });
        });*/

        jQuery(document).on("click", ".js_search", function () {
            var _form = jQuery(document).find("[name=\"search\"]");
            window.location.href = window.location.href+'&'+_form.serialize();
        });
    </script>
    <style>
        .langTable {margin-left: 16%; width: 84%;}
        .langTable table {width: {{count(sLang::langConfig())*25+35}}%;}
        .langTable td:first-child {vertical-align: middle; position: absolute; width: 16%; margin-left: -16%;}
        .langTable tbody td:first-child {padding-top: 10px;}
    </style>
@endpush