@php global $richtexteditorIds, $richtexteditorOptions; @endphp

@foreach(sLang::langConfig() as $lang)
    @php($isDefaultLang = $lang == sLang::langDefault())
    <!-- General {{$lang}} -->
    <div class="tab-page slang-resource-tab-page" id="tabGeneral_{{$lang}}">
        <h2 class="tab">@lang('global.settings_general') <span class="badge bg-seigerit slang-lang-badge">{{$lang}}</span></h2>
        <script>tpSettings.addTabPage(document.getElementById("tabGeneral_{{$lang}}"));</script>
        <div class="row form-row">
            <div class="row-col col-lg-12 col-12">
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'pagetitle', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                        @include('sLang::partials.resource-field-label', ['for' => $lang . '_pagetitle', 'label' => __('global.resource_title'), 'help' => __('global.resource_title_help')])
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <input name="{{$lang}}_pagetitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_pagetitle', '', 'is_scalar')))}}" class="form-control" data-slang-dirty="1" spellcheck="true" />
                            @else
                                <div class="input-group">
                                    <input name="{{$lang}}_pagetitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_pagetitle', '', 'is_scalar')))}}" class="form-control slang-resource-input" data-slang-dirty="1" spellcheck="true" />
                                    @include('sLang::partials.translate-button', ['lang' => $lang])
                                </div>
                            @endif
                            <script>document.getElementsByName("{{$lang}}_pagetitle")[0].focus();</script>
                        </div>
                    </div>
                @endif
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'longtitle', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                        @include('sLang::partials.resource-field-label', ['for' => $lang . '_longtitle', 'label' => __('global.long_title'), 'help' => __('global.resource_long_title_help')])
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <input name="{{$lang}}_longtitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_longtitle', '', 'is_scalar')))}}" class="form-control" data-slang-dirty="1" spellcheck="true" />
                            @else
                                <div class="input-group">
                                    <input name="{{$lang}}_longtitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_longtitle', '', 'is_scalar')))}}" class="form-control slang-resource-input" data-slang-dirty="1" spellcheck="true" />
                                    @include('sLang::partials.translate-button', ['lang' => $lang])
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'description', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                        @include('sLang::partials.resource-field-label', ['for' => $lang . '_description', 'label' => __('global.resource_description'), 'help' => __('global.resource_description_help')])
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <input name="{{$lang}}_description" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_description', '', 'is_scalar')))}}" class="form-control" data-slang-dirty="1" spellcheck="true" />
                            @else
                                <div class="input-group">
                                    <input name="{{$lang}}_description" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_description', '', 'is_scalar')))}}" class="form-control slang-resource-input" data-slang-dirty="1" spellcheck="true" />
                                    @include('sLang::partials.translate-button', ['lang' => $lang])
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @if($content['type'] == 'reference' || evo()->getManagerApi()->action == '72') {{-- Web Link specific --}}
                    @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'ta', 'content' => $content]))
                    @if(is_array($evtField)){!! implode('', $evtField) !!}@else
                        <div class="row form-row">
                            @include('sLang::partials.resource-field-label', ['for' => $lang . '_content', 'label' => __('global.weblink'), 'help' => __('global.resource_weblink_help')])
                            <div class="col">
                                <button type="button" id="llock_{{$lang}}" class="evo-ui-btn evo-ui-btn--icon" data-slang-resource-action="select-link" title="@lang('global.resource_weblink_help')" aria-label="@lang('global.resource_weblink_help')">
                                    <i class="{{$_style["icon_chain"]}}"></i>
                                </button>
                                <input name="{{$lang}}_content" id="{{$lang}}_content" type="text" maxlength="255" value="{{($value = get_by_key($content, $lang.'_content', '', 'is_scalar')) !== '' ? entities(stripslashes($value), evo()->getConfig('modx_charset')) : 'http://'}}" class="form-control" data-slang-dirty="1" />
                                <button type="button" class="evo-ui-btn" data-slang-resource-action="browse-file" data-slang-target="{{$lang}}_content">@lang('global.insert')</button>
                            </div>
                        </div>
                    @endif
                @endif
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'introtext', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                            @include('sLang::partials.resource-field-label', ['for' => $lang . '_introtext', 'label' => __('global.resource_summary'), 'help' => __('global.resource_summary_help')])
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <textarea id="{{$lang}}_introtext" name="{{$lang}}_introtext" class="form-control" rows="3" cols="" data-slang-dirty="1">{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_introtext', '', 'is_scalar')))}}</textarea>
                            @else
                                <div class="input-group">
                                    <textarea id="{{$lang}}_introtext" name="{{$lang}}_introtext" class="form-control slang-resource-input" rows="3" cols="" data-slang-dirty="1">{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_introtext', '', 'is_scalar')))}}</textarea>
                                    @include('sLang::partials.translate-button', ['lang' => $lang])
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @php($evtField = evo()->invokeEvent('sLangDocFormFieldRender', ['lang' => $lang, 'name' => 'menutitle', 'content' => $content]))
                @if(is_array($evtField)){!!implode('', $evtField)!!}@else
                    <div class="row form-row">
                            @include('sLang::partials.resource-field-label', ['for' => $lang . '_menutitle', 'label' => __('global.resource_opt_menu_title'), 'help' => __('global.resource_opt_menu_title_help')])
                        <div class="col">
                            @if($lang == sLang::langDefault())
                                <input name="{{$lang}}_menutitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_menutitle', '', 'is_scalar')))}}" class="form-control" data-slang-dirty="1" spellcheck="true" />
                            @else
                                <div class="input-group">
                                    <input name="{{$lang}}_menutitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_menutitle', '', 'is_scalar')))}}" class="form-control slang-resource-input" data-slang-dirty="1" spellcheck="true" />
                                    @include('sLang::partials.translate-button', ['lang' => $lang])
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
                                <span id="{{$isDefaultLang ? 'content_header' : 'content_header_'.$lang}}">@lang('global.resource_content')</span>
                                @if($lang != sLang::langDefault())
                                    <label class="float-right">
                                        @include('sLang::partials.translate-button', ['lang' => $lang])
                                    </label>
                                @endif
                                @if($isDefaultLang)
                                    <label class="float-right">@lang('global.which_editor_title')
                                        <select id="which_editor" class="form-control form-control-sm" size="1" name="which_editor" data-slang-resource-action="change-rte">
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
                                @endif
                            </div>
                            <div id="{{$isDefaultLang ? 'content_body' : 'content_body_'.$lang}}">
                                @if((!empty($content['richtext']) || evo()->getManagerApi()->action == '4') && evo()->getConfig('use_editor') && evo()->getConfig('which_editor') !== 'none')
                                    @php($htmlContent = get_by_key($content, $lang.'_content', '', 'is_scalar'))
                                    <div class="section-editor clearfix">
                                        <textarea id="{{$lang}}_content" name="{{$lang}}_content" data-slang-dirty="1">{!!evo()->getPhpCompat()->htmlspecialchars($htmlContent)!!}</textarea>
                                    </div>
                                    {{-- Richtext-[*content*] --}}
                                    @php($richtexteditorIds[evo()->getConfig('which_editor')][] = $lang.'_content')
                                    @php($richtexteditorOptions[evo()->getConfig('which_editor')][] = [$lang.'_content' => ''])
                                @else
                                    @php($plainContent = get_by_key($content, $lang.'_content', ''))
                                    @if($isDefaultLang)
                                        <div>
                                            <textarea
                                                class="phptextarea"
                                                id="ta"
                                                name="ta"
                                                rows="20"
                                                wrap="soft" data-slang-dirty="1"
                                                data-slang-default-content="1"
                                                data-slang-codemirror-target="1"
                                                data-slang-editor-key="ta"
                                            >{!!evo()->getPhpCompat()->htmlspecialchars($plainContent)!!}</textarea>
                                            <input
                                                type="hidden"
                                                id="{{$lang}}_content_proxy"
                                                name="{{$lang}}_content"
                                                value="{!!evo()->getPhpCompat()->htmlspecialchars($plainContent)!!}"
                                                data-slang-default-content-proxy="1"
                                            />
                                        </div>
                                    @else
                                        <div><textarea class="phptextarea" id="{{$lang}}_content" name="{{$lang}}_content" rows="20" wrap="soft" data-slang-dirty="1" data-slang-codemirror-target="1" data-slang-editor-key="{{$lang}}_content">{!!evo()->getPhpCompat()->htmlspecialchars($plainContent)!!}</textarea></div>
                                    @endif
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
                            @include('sLang::partials.resource-field-label', ['for' => $lang . '_seotitle', 'label' => __('sLang::global.seotitle'), 'help' => __('sLang::global.seotitle_help')])
                            <div class="col">
                                @if($lang == sLang::langDefault())
                                    <input name="{{$lang}}_seotitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seotitle', '', 'is_scalar')))}}" class="form-control" data-slang-dirty="1" spellcheck="true" />
                                @else
                                    <div class="input-group">
                                        <input name="{{$lang}}_seotitle" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seotitle', '', 'is_scalar')))}}" class="form-control slang-resource-input" data-slang-dirty="1" spellcheck="true" />
                                        @include('sLang::partials.translate-button', ['lang' => $lang])
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
                            @include('sLang::partials.resource-field-label', ['for' => $lang . '_seodescription', 'label' => __('sLang::global.seodescription'), 'help' => __('sLang::global.seodescription_help')])
                            <div class="col">
                                @if($lang == sLang::langDefault())
                                    <input name="{{$lang}}_seodescription" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seodescription', '', 'is_scalar')))}}" class="form-control" data-slang-dirty="1" spellcheck="true" />
                                @else
                                    <div class="input-group">
                                        <input name="{{$lang}}_seodescription" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, $lang.'_seodescription', '', 'is_scalar')))}}" class="form-control slang-resource-input" data-slang-dirty="1" spellcheck="true" />
                                        @include('sLang::partials.translate-button', ['lang' => $lang])
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
