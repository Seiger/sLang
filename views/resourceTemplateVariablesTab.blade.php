@if($templateVariablesTab)
    <!-- Template Variables -->
    <div class="tab-page" id="templateVariables">
        <h2 class="tab">@lang('global.settings_templvars')</h2>
        <script>tpSettings.addTabPage(document.getElementById("templateVariables"));</script>

        <div class="row form-row">
            <div class="row-col col-lg-12 col-12">
                {!! $templateVariablesTab !!}
            </div>
        </div>
    </div>
@endif