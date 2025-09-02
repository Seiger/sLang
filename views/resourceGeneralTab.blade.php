@php global $richtexteditorIds, $richtexteditorOptions; @endphp

@foreach(sLang::langConfig() as $lang)
    <!-- General {{$lang}} -->
    <div class="tab-page" id="tabGeneral_{{$lang}}">
        <h2 class="tab">@lang('global.settings_general') <span class="badge bg-seigerit">{{$lang}}</span></h2>
        <script>tpSettings.addTabPage(document.getElementById("tabGeneral_{{$lang}}"));</script>
        <div class="row form-row">
            <div class="row-col col-lg-12 col-12">
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'pagetitle', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                        <div class="col-auto col-title-11">
                            <label for="{{$lang}}_pagetitle" class="warning">@lang('global.resource_title')</label>
                            <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_title_help')"></i>
                        </div>
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <input name="{{$lang}}_pagetitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_pagetitle', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" />
                            @else
                                <div class="input-group">
                                    <input name="{{$lang}}_pagetitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_pagetitle', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                                    <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                                        <i class="fa fa-language" style="font-size:xx-large;"></i>
                                    </button>
                                </div>
                            @endif
                            <script>document.getElementsByName("{{$lang}}_pagetitle")[0].focus();</script>
                        </div>
                    </div>
                @endif
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'longtitle', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                        <div class="col-auto col-title-11">
                            <label for="{{$lang}}_longtitle" class="warning">@lang('global.long_title')</label>
                            <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_long_title_help')"></i>
                        </div>
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <input name="{{$lang}}_longtitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_longtitle', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" />
                            @else
                                <div class="input-group">
                                    <input name="{{$lang}}_longtitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_longtitle', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                                    <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                                        <i class="fa fa-language" style="font-size:xx-large;"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'description', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                        <div class="col-auto col-title-11">
                            <label for="{{$lang}}_description" class="warning">@lang('global.resource_description')</label>
                            <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_description_help')"></i>
                        </div>
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <input name="{{$lang}}_description" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_description', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" />
                            @else
                                <div class="input-group">
                                    <input name="{{$lang}}_description" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_description', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                                    <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                                        <i class="fa fa-language" style="font-size:xx-large;"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @if($content['type'] == 'reference' || evo()->getManagerApi()->action == '72') {{-- Web Link specific --}}
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'ta', 'content' => $content]))
                @if(is_array($evtField)){!! implode('', $evtField) !!}@else
                    <div class="row form-row">
                        <div class="col-auto col-title-11">
                            <label for="ta" class="warning">@lang('global.weblink')</label>
                            <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_weblink_help')"></i>
                        </div>
                        <div class="col">
                            <i id="llock" class="{{$_style["icon_chain"]}}" onclick="enableLinkSelection(!allowLinkSelection);"></i>
                            <input name="ta" id="ta" type="text" maxlength="255" value="{{(!empty($content['content']) ? entities(stripslashes($content['content']), evo()->getConfig('modx_charset')) : 'http://')}}" class="form-control" onchange="documentDirty=true;" />
                            <input type="button" value="@lang('global.insert')" onclick="BrowseFileServer('ta')" />
                        </div>
                    </div>
                @endif
                @endif
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'introtext', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                        <div class="col-auto col-title-11">
                            <label for="{{$lang}}_introtext" class="warning">@lang('global.resource_summary')</label>
                            <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_summary_help')"></i>
                        </div>
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <textarea id="{{$lang}}_introtext" name="{{$lang}}_introtext" class="form-control" rows="3" cols="" onchange="documentDirty=true;">{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_introtext', '', 'is_scalar')))}}</textarea>
                            @else
                                <div class="input-group">
                                    <textarea id="{{$lang}}_introtext" name="{{$lang}}_introtext" class="form-control" rows="3" cols="" onchange="documentDirty=true;" style="width: calc(100% - 52px);">{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_introtext', '', 'is_scalar')))}}</textarea>
                                    <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                                        <i class="fa fa-language" style="font-size:xx-large;"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'menutitle', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                        <div class="col-auto col-title-11">
                            <label for="{{$lang}}_menutitle" class="warning">@lang('global.resource_opt_menu_title')</label>
                            <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_menu_title_help')"></i>
                        </div>
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <input name="{{$lang}}_menutitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_menutitle', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" />
                            @else
                                <div class="input-group">
                                    <input name="{{$lang}}_menutitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_menutitle', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                                    <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                                        <i class="fa fa-language" style="font-size:xx-large;"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @if($content['type'] == 'document' || evo()->getManagerApi()->action == '4')
            @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'content', 'content' => $content]))
            @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                <table>
                    <tr>
                        <td colspan="2" class="col">
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
                    <tr><td><hr></td></tr>
                </table>
            @endif
        @endif
        {{-- @deprecated --}}
        @if(!evo()->getConfig('check_sSeo', false))
            @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'seotitle', 'content' => $content]))
            @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                <div class="row form-row">
                    <div class="row-col col-lg-12 col-12">
                        <div class="row form-row">
                            <div class="col-auto col-title-11">
                                <label for="{{$lang}}_seotitle" class="warning">@lang('sLang::global.seotitle')</label>
                                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('sLang::global.seotitle_help')"></i>
                            </div>
                            <div class="col">
                                @if($lang == sLang::langDefault())
                                    <input name="{{$lang}}_seotitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seotitle', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" />
                                @else
                                    <div class="input-group">
                                        <input name="{{$lang}}_seotitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seotitle', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'seodescription', 'content' => $content]))
            @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                <div class="row form-row">
                    <div class="row-col col-lg-12 col-12">
                        <div class="row form-row">
                            <div class="col-auto col-title-11">
                                <label for="{{$lang}}_seodescription" class="warning">@lang('sLang::global.seodescription')</label>
                                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('sLang::global.seodescription_help')"></i>
                            </div>
                            <div class="col">
                                @if($lang == sLang::langDefault())
                                    <input name="{{$lang}}_seodescription" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seodescription', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" />
                                @else
                                    <div class="input-group">
                                        <input name="{{$lang}}_seodescription" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seodescription', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" spellcheck="true" style="width: calc(100% - 52px);" />
                                        <button data-lang="{{$lang}}" class="btn btn-light js_translate" type="button" title="@lang('sLang::global.auto_translate') {{strtoupper(sLang::langDefault())}} => {{strtoupper($lang)}}" style="padding:0 5px;color:#0275d8;">
                                            <i class="fa fa-language" style="font-size:xx-large;"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif<!-- end .sectionBody -->
        {{-- Template Variables --}}
        @if($group_tvs < 3 && isset($templateVariablesLng[$lang])){!!$templateVariablesLng[$lang]!!}<div class="split my-3"></div>@endif
        {{-- Seo Fields --}}
        @php($seoFields = evo()->invokeEvent('OnRenderSeoFields', ['type' => 'document', 'lang' => $lang, 'id' => ($content['id'] ?? 0)]))
        @if(is_array($seoFields)){!!implode('', $seoFields)!!}<div class="split my-3"></div>@endif
    </div>
    <!-- end #tabGeneral -->
@endforeach
