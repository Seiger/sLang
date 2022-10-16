<form action="{{$url}}&get=settings" method="POST" id="editForm">
    <table class="table table-hover table-condensed">
        <tbody>
        <tr>
            <th width="160px;"><b>@lang('sLang::global.lang_def')</b></th>
            <td>
                <select style="width:100%" name="s_lang_default" class="form-control select2" data-placeholder="@lang('sLang::global.select_lang')" data-validate="textNoEmpty">
                    <option value=""></option>
                    @foreach (sLang::langList() as $id => $title)
                        <option value="{{$id}}"@if ($id == sLang::langDefault()) selected @endif()>{{$title['name']}} (@lang('sLang::global.lang_'.$id))</option>
                    @endforeach()
                </select>
                <div class="error-text" style="display:none;">@lang('sLang::global.field') <b>"@lang('sLang::global.lang_def')"</b> @lang('sLang::global.do_not_empty')</div>
            </td>
            <td width="200px;">
                <input type="hidden" name="s_lang_default_show" value="0">
                <label><input type="checkbox" name="s_lang_default_show" value="1"@if (1 == evo()->getConfig('s_lang_default_show')) checked @endif()>&emsp;<b>@lang('sLang::global.use_url')</b></label>
            </td>
        </tr>
        <tr>
            <th><b>@lang('sLang::global.languages')</b></th>
            <td colspan="2">
                <select style="width:100%" name="s_lang_config[]" class="form-control select2" data-placeholder="@lang('sLang::global.select_lang')" multiple data-validate="textMustContainDefault">
                    <option value=""></option>
                    @foreach (sLang::langList() as $id => $title)
                        <option value="{{$id}}"@if (in_array($id, sLang::langConfig())) selected @endif()>{{$title['name']}} (@lang('sLang::global.lang_'.$id))</option>
                    @endforeach()
                </select>
                <div class="error-text" style="display:none;">@lang('sLang::global.field') <b>"@lang('sLang::global.languages')"</b> @lang('sLang::global.do_not_empty') @lang('sLang::global.must_contain')</div>
            </td>
        </tr>
        <tr>
            <th><b>@lang('sLang::global.lang_front')</b></th>
            <td colspan="2">
                <select style="width:100%" name="s_lang_front[]" class="form-control select2" data-placeholder="@lang('sLang::global.select_lang')" multiple data-validate="textMustContainSiteLang">
                    <option value=""></option>
                    @foreach (sLang::langList() as $id => $title)
                        <option value="{{$id}}"@if (in_array($id, sLang::langFront())) selected @endif()>{{$title['name']}} (@lang('sLang::global.lang_'.$id))</option>
                    @endforeach()
                </select>
                <div class="error-text" style="display:none;">@lang('sLang::global.field') <b>"@lang('sLang::global.lang_front')"</b> @lang('sLang::global.do_not_empty') @lang('sLang::global.must_contain')</div>
            </td>
        </tr>
        </tbody>
    </table>
</form>

@push('scripts.bot')
    <div id="actions">
        <div class="btn-group">
            <a href="javascript:;" class="btn btn-success" onclick="documentDirty=false;saveForm('#editForm');" title="@lang('global.save_all_changes')">
                <i class="fa fa-save"></i>&emsp;<span>@lang('global.save')</span>
            </a>
        </div>
    </div>
@endpush