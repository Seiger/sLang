@php global $richtexteditorIds, $richtexteditorOptions; @endphp

@foreach(sLang::langConfig() as $lang)
<!-- General {{$lang}} -->
<div class="tab-page" id="tabGeneral_{{$lang}}">
    <h2 class="tab">@lang('global.settings_general') <span class="badge bg-seigerit">{{$lang}}</span></h2>
    <script>tpSettings.addTabPage(document.getElementById("tabGeneral_{{$lang}}"));</script>
    <table>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_title')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_title_help')"></i>
            </td>
            <td>
                @if($lang == sLang::langDefault())
                    <input name="{{$lang}}_pagetitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_pagetitle', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
                @else
                    <div class="input-group">
                        <input name="{{$lang}}_pagetitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_pagetitle', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                        </button>
                    </div>
                @endif
                <script>document.getElementsByName("{{$lang}}_pagetitle")[0].focus();</script>
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.long_title')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_long_title_help')"></i>
            </td>
            <td>
                @if($lang == sLang::langDefault())
                    <input name="{{$lang}}_longtitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_longtitle', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
                @else
                    <div class="input-group">
                        <input name="{{$lang}}_longtitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_longtitle', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                        </button>
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_description')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_description_help')"></i>
            </td>
            <td>
                @if($lang == sLang::langDefault())
                    <input name="{{$lang}}_description" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_description', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
                @else
                    <div class="input-group">
                        <input name="{{$lang}}_description" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_description', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                        </button>
                    </div>
                @endif
            </td>
        </tr>
        @if($content['type'] == 'reference' || EvolutionCMS()->getManagerApi()->action == '72') {{-- Web Link specific --}}
            <tr>
                <td><span class="warning">@lang('global.weblink')</span>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_weblink_help')"></i>
                </td>
                <td>
                    <i id="llock" class="{{$_style["icon_chain"]}}" onclick="enableLinkSelection(!allowLinkSelection);"></i>
                    <input name="ta" id="ta" type="text" maxlength="255" value="{{(!empty($content['content']) ? entities(stripslashes($content['content']), evo()->getConfig('modx_charset')) : 'http://')}}" class="inputBox" onchange="documentDirty=true;" />
                    <input type="button" value="@lang('global.insert')" onclick="BrowseFileServer('ta')" />
                </td>
            </tr>
        @endif
        <tr>
            <td valign="top">
                <span class="warning">@lang('global.resource_summary')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_summary_help')" spellcheck="true"></i>
            </td>
            <td valign="top">
                @if($lang == sLang::langDefault())
                    <textarea id="{{$lang}}_introtext" name="{{$lang}}_introtext" class="inputBox" rows="3" cols="" onchange="documentDirty=true;">{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_introtext', '', 'is_scalar')))}}</textarea>
                @else
                    <div class="input-group">
                        <textarea id="{{$lang}}_introtext" name="{{$lang}}_introtext" class="inputBox" rows="3" cols="" onchange="documentDirty=true;" style="width: calc(100% - 52px);">{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_introtext', '', 'is_scalar')))}}</textarea>
                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                        </button>
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_opt_menu_title')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_menu_title_help')"></i>
            </td>
            <td>
                @if($lang == sLang::langDefault())
                    <input name="{{$lang}}_menutitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_menutitle', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
                @else
                    <div class="input-group">
                        <input name="{{$lang}}_menutitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_menutitle', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                        </button>
                    </div>
                @endif
            </td>
        </tr>
        @if($content['type'] == 'document' || evo()->getManagerApi()->action == '4')
            <tr>
                <td colspan="2">
                    <hr> <!-- Content -->
                    <div class="clearfix">
                        <span id="content_header">@lang('global.resource_content')</span>
                        @if($lang != sLang::langDefault())
                            <label class="float-right">
                                <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="height: 25px;padding:0 5px;color:#0275d8;">
                                    <i class="fa fa-language" style="font-size:x-large;"></i>
                                </button>
                            </label>
                        @endif
                        <label class="float-right">@lang('global.which_editor_title')
                            <select id="which_editor" class="form-control form-control-sm" size="1" name="which_editor" onchange="changeRTE();">
                                <option value="none">@lang('global.none')</option>
                                    {{-- invoke OnRichTextEditorRegister event --}}
                                    @php($evtOut = evo()->invokeEvent("OnRichTextEditorRegister"))
                                    @if(is_array($evtOut))
                                        @for($i = 0; $i < count($evtOut); $i++)
                                            @php($editor = $evtOut[$i])
                                            <option value="{{$editor}}"{!!(evo()->getConfig('which_editor') == $editor ? ' selected="selected"' : '')!!}>{{$editor}}</option>
                                        @endfor
                                    @endif
                            </select>
                        </label>
                    </div>
                    <div id="content_body">
                        @if((!empty($content['richtext']) || evo()->getManagerApi()->action == '4') && evo()->getConfig('use_editor'))
                            @php($htmlContent = get_by_key($content, $lang.'_content', '', 'is_scalar'))
                            <div class="section-editor clearfix">
                                <textarea id="{{$lang}}_content" name="{{$lang}}_content" onchange="documentDirty=true;">{!!evo()->getPhpCompat()->htmlspecialchars($htmlContent)!!}</textarea>
                            </div>
                            {{-- Richtext-[*content*] --}}
                            @php($richtexteditorIds[evo()->getConfig('which_editor')][] = $lang.'_content')
                            @php($richtexteditorOptions[evo()->getConfig('which_editor')][] = [$lang.'_content' => ''])
                        @else
                            <div><textarea class="phptextarea" id="{{$lang}}_content" name="{{$lang}}_content" rows="20" wrap="soft" onchange="documentDirty=true;">{!!evo()->getPhpCompat()->htmlspecialchars(get_by_key($content, $lang.'_content', ''))!!}</textarea></div>
                        @endif
                    </div>
                </td>
            </tr>
        @endif
        <tr><td colspan="2"><hr></td></tr>
        <tr>
            <td>
                <span class="warning">@lang('sLang::global.seotitle')</span>
            </td>
            <td>
                @if($lang == sLang::langDefault())
                    <input name="{{$lang}}_seotitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seotitle', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
                @else
                    <div class="input-group">
                        <input name="{{$lang}}_seotitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seotitle', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                        </button>
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('sLang::global.seodescription')</span>
            </td>
            <td>
                @if($lang == sLang::langDefault())
                    <input name="{{$lang}}_seodescription" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seodescription', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
                @else
                    <div class="input-group">
                        <input name="{{$lang}}_seodescription" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seodescription', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                        </button>
                    </div>
                @endif
            </td>
        </tr><!-- end .sectionBody -->
    </table>
    {{-- Template Variables --}}
    @if($group_tvs < 3 && $templateVariablesOutput)
        {{$templateVariables}}
    @endif
</div>
<!-- end #tabGeneral -->
@endforeach