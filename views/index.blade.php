@extends('manager::template.page')

@section('content')
    <div class="notifier"><div class="notifier-txt"></div></div>
    <h1><i id="main-logo" class="@lang('sLang::global.slang_icon')" data-tooltip="@lang('sLang::global.description')"></i> @lang('sLang::global.slang_desc') <i class="fa fa-question-circle" data-tooltip="@lang('sLang::global.example_usage')"></i></h1>
    <div class="sectionBody">
        <div class="tab-pane" id="resourcesPane">
            <script>tpResources = new WebFXTabPane(document.getElementById('resourcesPane'), false);</script>

            <div class="tab-page translatesTab" id="translatesTab">
                <h2 class="tab"><a href="{!!$url!!}&get=translates"><span><i class="fa fa-language"></i> @lang('sLang::global.dictionary')</span></a></h2>
                <script>tpResources.addTabPage(document.getElementById('translatesTab'));</script>
                @if($get == 'translates')
                    @include('sLang::translatesTab')
                @endif
            </div>

            <div class="tab-page settingsTab" id="settingsTab">
                <h2 class="tab"><a href="{!!$url!!}&get=settings"><span><i class="fa fa-cogs"></i> @lang('sLang::global.settings')</span></a></h2>
                <script>tpResources.addTabPage(document.getElementById('settingsTab'));</script>
                @if($get == 'settings')
                    @include('sLang::settingsTab')
                @endif
            </div>

            <script>tpResources.setSelectedTab('{{$get}}Tab');</script>
        </div>
    </div>
@endsection

@push('scripts.top')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@push('scripts.bot')
    <script src="media/script/jquery.quicksearch.js"></script>
    <script src="media/script/jquery.nucontextmenu.js"></script>
    <script src="media/script/bootstrap/js/bootstrap.min.js"></script>
    <script src="actions/resources/functions.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        // Form Validation and Saving
        function saveForm(selector) {
            var errors    = 0;
            var messages  = "";
            var validates = $(selector+" [data-validate]");
            validates.each(function(k, v) {
                var rule = $(v).attr("data-validate").split(":");
                switch (rule[0]) {
                    case "textNoEmpty": // Not an empty field
                        if ($(v).val().length < 1) {
                            messages = messages + $(v).parent().find(".error-text").text() + "<br/>";
                            $(v).parent().removeClass("is-valid").addClass("is-invalid");
                            errors = errors + 1;
                        } else {
                            $(v).parent().removeClass("is-invalid").addClass("is-valid");
                        }
                        break;
                    case "textMustContainDefault": // Must contain the value of the default language
                        var _default = $(v).parents('tbody').find('[name^="s_lang_default"]').val();
                        _index = $(v).val().indexOf(_default);
                        if (_index >= $(v).val().length || _index < 0 || isNaN(_index)) {
                            messages = messages + $(v).parent().find(".error-text").text() + "<br/>";
                            $(v).parent().removeClass("is-valid").addClass("is-invalid");
                            errors = errors + 1;
                        } else {
                            $(v).parent().removeClass("is-invalid").addClass("is-valid");
                        }
                        break;
                    case "textMustContainSiteLang": // Must contain site language list values
                        var _default = $(v).parents('tbody').find('[name^="s_lang_default"]').val();
                        var _config = $(v).parents('tbody').find('[name^="s_lang_config"]').val();
                        var _valid = 1;
                        _index = $(v).val().indexOf(_default);
                        $(v).val().forEach(function (val) {
                            if (_config.indexOf(val) < 0) {
                                return _valid = 0;
                            }
                        });
                        if (_index >= $(v).val().length || _index < 0 || isNaN(_index) || _valid < 1) {
                            messages = messages + $(v).parent().find(".error-text").text() + "<br/>";
                            $(v).parent().removeClass("is-valid").addClass("is-invalid");
                            errors = errors + 1;
                        } else {
                            $(v).parent().removeClass("is-invalid").addClass("is-valid");
                        }
                        break;
                }
            });
            if (errors == 0) {
                $(selector).submit();
            } else {
                $('.notifier').addClass("notifier-error");
                $('.notifier').fadeIn(500);
                $('.notifier').find('.notifier-txt').html(messages);
                setTimeout(function() {
                    $('.notifier').fadeOut(5000);
                },2000);
                setTimeout(function() {
                    $('.notifier').removeClass("notifier-error");
                },5000);
            }
        }
    </script>
    <style>
        #copyright{position:fixed;bottom:0;right:0;background-color:#0057b8;padding:3px 7px;border-radius:5px;}
        #copyright img{width:9em;}
        #main-logo{color:#0B78FF;}
        .badge.bg-seigerit{background-color:#0057b8;color:#ffd700;font-size:85%;}
        .fade:not(.show){opacity:initial;}
        .is-invalid .select2-selection, .needs-validation ~ span > .select2-dropdown{border-color:red !important;}
        .is-valid .select2-selection, .needs-validation ~ span > .select2-dropdown{border-color:green !important;}
        .notifier{position:fixed;display:none;top:0;left:0;width:100%;height:100vh;overflow-y:auto;z-index:9999;background:rgba(255,255,255,0.8);}
        .notifier-txt{position:absolute;width:100%;text-align:center;top:50%;left:50%;background:#fff;padding:30px;font-size:18px;-webkit-transform:translateY(-50%) translateX(-50%);-moz-transform:translateY(-50%) translateX(-50%);-ms-transform:translateY(-50%) translateX(-50%);-o-transform:translateY(-50%) translateX(-50%);transform:translateY(-50%) translateX(-50%);}
        .notifier-error{color:red;}
        .notifier-success{color:green;}
        .modal{top:50px;font-weight:bold;}
        .modal-backdrop {background-color:rgba(0, 0, 0, 0.5);}
        .modal-header{margin-top: 1rem;}
    </style>
    <div id="copyright"><a href="https://seigerit.com/" target="_blank"><img src="{{evo()->getConfig('site_url', '/')}}assets/site/seigerit-blue.svg"/></a></div>
@endpush