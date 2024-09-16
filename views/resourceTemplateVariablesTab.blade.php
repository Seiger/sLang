@if($templateVariablesTab)
    <!-- Template Variables -->
    @if (!empty($templateVariablesTab['default']))
        <div class="tab-page" id="templateDefaultVariables">
            <h2 class="tab">@lang('global.settings_templvars')</h2>
            <script>tpSettings.addTabPage(document.getElementById("templateDefaultVariables"));</script>

            <div class="row form-row">
                <div class="row-col col-lg-12 col-12">
                    {!!$templateVariablesTab['default']!!}
                </div>
            </div>
        </div>
    @endif
    @foreach(sLang::langConfig() as $lang)
        @if(!empty($templateVariablesTab[$lang]))
            <div class="tab-page" id="templateVariables_{{$lang}}">
                <h2 class="tab">@lang('global.settings_templvars') <span class="badge bg-seigerit">{{$lang}}</span></h2>
                <script>tpSettings.addTabPage(document.getElementById("templateVariables_{{$lang}}"));</script>

                <div class="row form-row">
                    <div class="row-col col-lg-12 col-12">
                        {!!$templateVariablesTab[$lang]!!}
                    </div>
                </div>
            </div>
            @if(!empty($templateVariablesDefaultValue))
                @foreach($templateVariablesDefaultValue as $tvID => $value)
                    <input name="tv{{$tvID}}" type="hidden" value="{{$value}}">
                @endforeach
            @endif
        @endif
    @endforeach
@endif