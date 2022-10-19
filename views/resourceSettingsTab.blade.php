<!-- Settings -->
<div class="tab-page" id="tabSettings">
    <h2 class="tab">@lang('global.settings_page_settings')</h2>
    <script type="text/javascript">tpSettings.addTabPage(document.getElementById("tabSettings"));</script>

    <div class="row form-row">
        @php($mx_can_pub = evo()->hasPermission('publish_document') ? '' : 'disabled="disabled" ')
        <div class="row-col col-lg-6 col-md-6 col-12">
            <div class="row form-row">
                <div class="col-auto col-title">
                    <label for="template" class="warning">@lang('global.page_data_template')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_template_help')"></i>
                </div>
                <div class="col">
                    <select id="template" name="template" class="form-control" onchange="templateWarning();">
                        <option value="0">(blank)</option>
                        @php($templates = \EvolutionCMS\Models\SiteTemplate::query()
                            ->select('site_templates.templatename', 'site_templates.selectable', 'site_templates.category', 'site_templates.id', 'categories.category AS category_name')
                            ->leftJoin('categories','site_templates.category','=','categories.id')
                            ->orderBy('categories.category', 'ASC')
                            ->orderBy('site_templates.templatename', 'ASC')->get())
                        @php($currentCategory = '')
                        @php($closeOptGroup = false)
                        @foreach($templates as $template)
                            @php($row = $template->toArray())
                            @if($row['selectable'] != 1 && $row['id'] != $content['template'])
                                @continue
                            @endif
                            {{-- Skip if not selectable but show if selected! --}}
                            @php($thisCategory = $row['category_name'])
                            @if($thisCategory == null)
                                @php($thisCategory = __("global.no_category"))
                            @endif
                            @if($thisCategory != $currentCategory)
                                @if($closeOptGroup)
                                    </optgroup>
                        @endif
                        <optgroup label="{{$thisCategory}}">
                            @php($closeOptGroup = true)
                            @endif
                            @php($selectedtext = ($row['id'] == $content['template']) ? ' selected="selected"' : '')
                            <option value="{{$row['id']}}" {{$selectedtext}}>{{$row['templatename']}} ({{$row['id']}})</option>
                            @php($currentCategory = $thisCategory)
                            @endforeach
                            @if($thisCategory != '')
                        </optgroup>
                        @endif
                    </select>
                </div>
            </div>
            <div class="row form-row">
                <div class="col-auto col-title">
                    <label for="parent" class="warning">@lang('global.resource_parent')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_parent_help')"></i>
                </div>
                <div class="col">
                    <div>
                        @php($parentlookup = false)
                        @if(isset($_REQUEST['id']))
                            @if($content['parent'] == 0)
                                @php($parentname = evo()->getConfig('site_name'))
                            @else
                                @php($parentlookup = $content['parent'])
                            @endif
                        @elseif(isset($_REQUEST['pid']))
                            @if($_REQUEST['pid'] == 0)
                                @php($parentname = evo()->getConfig('site_name'))
                            @else
                                @php($parentlookup = $_REQUEST['pid'])
                            @endif
                        @elseif(isset($_POST['parent']))
                            @if($_POST['parent'] == 0)
                                @php($parentname = evo()->getConfig('site_name'))
                            @else
                                @php($parentlookup = $_POST['parent'])
                            @endif
                        @else
                            @php($parentname = evo()->getConfig('site_name'))
                            @php($content['parent'] = 0)
                        @endif
                        @if($parentlookup !== false && is_numeric($parentlookup))
                            @php($parentname = SiteContent::withTrashed()->select('pagetitle')->find($parentlookup)->pagetitle)
                            @if(!$parentname)
                                @php(evo()->webAlertAndQuit($_lang["error_no_parent"]))
                            @endif
                        @endif
                        <i id="plock" class="{{$_style["icon_folder"]}}" onclick="enableParentSelection(!allowParentSelection);"></i>
                        <b id="parentName">{{(isset($_REQUEST['pid']) ? entities($_REQUEST['pid']) : $content['parent'])}} ({{entities($parentname)}})</b>
                        <input type="hidden" name="parent" value="{{(isset($_REQUEST['pid']) ? entities($_REQUEST['pid']) : $content['parent'])}}" onchange="documentDirty=true;" />
                    </div>
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title">
                    <label for="publishedcheck" class="warning">@lang('global.resource_opt_published')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_published_help')"></i>
                </div>
                <div class="col">
                    <input {!!$mx_can_pub!!}name="publishedcheck" type="checkbox" class="form-checkbox form-control" {!!(isset($content['published']) && $content['published'] == 1) || (!isset($content['published']) && evo()->getConfig('publish_default')) ? "checked" : ''!!} onchange="documentDirty=true;" onclick="changestate(document.mutate.published);" />
                    <input type="hidden" name="published" value="{{(isset($content['published']) && $content['published'] == 1) || (!isset($content['published']) && evo()->getConfig('publish_default')) ? 1 : 0}}" />
                </div>
            </div>
            {{-- Menu --}}
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title">
                    <label for="menu_main" class="warning">@lang('sLang::global.menu_main')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('sLang::global.menu_main_help')"></i>
                </div>
                <div class="col">
                    <label class="checkbox">
                        <input {!!$mx_can_pub!!}name="menu_maincheck" type="checkbox" class="form-checkbox form-control" {!!(isset($content['menu_main']) && $content['menu_main'] == 1) ? "checked" : ''!!} onchange="documentDirty=true;" onclick="changestate(document.mutate.menu_main);" />
                        <input type="hidden" name="menu_main" value="{{(isset($content['menu_main']) && $content['menu_main'] == 1) ? 1 : 0}}" />
                    </label>
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title">
                    <label for="menu_footer" class="warning">@lang('sLang::global.menu_footer')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('sLang::global.menu_footer_help')"></i>
                </div>
                <div class="col">
                    <label class="checkbox">
                        <input {!!$mx_can_pub!!}name="menu_footercheck" type="checkbox" class="form-checkbox form-control" {!!(isset($content['menu_footer']) && $content['menu_footer'] == 1) ? "checked" : ''!!} onchange="documentDirty=true;" onclick="changestate(document.mutate.menu_footer);" />
                        <input type="hidden" name="menu_footer" value="{{(isset($content['menu_footer']) && $content['menu_footer'] == 1) ? 1 : 0}}" />
                    </label>
                </div>
            </div>
            {{-- Menu --}}
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title-10">
                    <label for="alias_visiblecheck" class="warning">@lang('global.resource_opt_alvisibled')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_alvisibled_help')"></i>
                </div>
                <div class="col">
                    <input name="alias_visible_check" type="checkbox" class="form-checkbox form-control" {{((!isset($content['alias_visible']) || $content['alias_visible'] == 1) ? "checked" : '')}} onchange="documentDirty=true;" onclick="changestate(document.mutate.alias_visible);" />
                    <input type="hidden" name="alias_visible" value="{{((!isset($content['alias_visible']) || $content['alias_visible'] == 1) ? 1 : 0)}}" />
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title-10">
                    <label for="searchablecheck" class="warning">@lang('global.page_data_searchable')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_searchable_help')"></i>
                </div>
                <div class="col">
                    <input name="searchablecheck" type="checkbox" class="form-checkbox form-control" {{(isset($content['searchable']) && $content['searchable'] == 1) || (!isset($content['searchable']) && evo()->getConfig('search_default')) ? "checked" : ''}} onclick="changestate(document.mutate.searchable);" />
                    <input type="hidden" name="searchable" value="{{((isset($content['searchable']) && $content['searchable'] == 1) || (!isset($content['searchable']) && evo()->getConfig('search_default')) ? 1 : 0)}}" onchange="documentDirty=true;" />
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title-10">
                    <label for="richtextcheck" class="warning">@lang('global.resource_opt_richtext')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_richtext_help')"></i>
                </div>
                <div class="col">
                    <input name="richtextcheck" type="checkbox" class="form-checkbox form-control" {{(empty($content['richtext']) && evo()->getManagerApi()->action == '27' ? '' : "checked")}} onclick="changestate(document.mutate.richtext);" />
                    <input type="hidden" name="richtext" value="{{(empty($content['richtext']) && evo()->getManagerApi()->action == '27' ? 0 : 1)}}" onchange="documentDirty=true;" />
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title-10">
                    <label for="isfoldercheck" class="warning">@lang('global.resource_opt_folder')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_folder_help')"></i>
                </div>
                <div class="col">
                    <input name="isfoldercheck" type="checkbox" class="form-checkbox form-control" {{((!empty($content['isfolder']) || evo()->getManagerApi()->action == '85') ? "checked" : '')}} onclick="changestate(document.mutate.isfolder);" />
                    <input type="hidden" name="isfolder" value="{{((!empty($content['isfolder']) || evo()->getManagerApi()->action == '85') ? 1 : 0)}}" onchange="documentDirty=true;" />
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title-10">
                    <label for="hide_from_treecheck" class="warning">@lang('global.track_visitors_title')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_trackvisit_help')"></i>
                </div>
                <div class="col">
                    <input name="hide_from_treecheck" type="checkbox" class="form-checkbox form-control" {!! empty($content['hide_from_tree']) ? 'checked="checked"' : '' !!} onclick="changestate(document.mutate.hide_from_tree);" />
                    <input type="hidden" name="hide_from_tree" value="{!! empty($content['hide_from_tree']) ? 0 : 1 !!}" onchange="documentDirty=true;" />
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title">
                    <label for="cacheablecheck" class="warning">@lang('global.page_data_cacheable')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_cacheable_help')"></i>
                </div>
                <div class="col">
                    <input name="cacheablecheck" type="checkbox" class="form-checkbox form-control" {{((isset($content['cacheable']) && $content['cacheable'] == 1) || (!isset($content['cacheable']) && evo()->getConfig('cache_default')) ? "checked" : '')}} onclick="changestate(document.mutate.cacheable);" />
                    <input type="hidden" name="cacheable" value="{{((isset($content['cacheable']) && $content['cacheable'] == 1) || (!isset($content['cacheable']) && evo()->getConfig('cache_default')) ? 1 : 0)}}" onchange="documentDirty=true;" />
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title">
                    <label for="syncsitecheck" class="warning">@lang('global.resource_opt_emptycache')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_emptycache_help')"></i>
                </div>
                <div class="col">
                    <input id="syncsitecheck" name="syncsitecheck" type="checkbox" class="form-checkbox form-control" {{((isset($content['syncsitecheck']) && $content['syncsitecheck'] == 1) || (!isset($content['syncsitecheck']) && evo()->getConfig('cache_default')) ? "checked" : '')}} onclick="changestate(document.mutate.syncsitecheck);" />
                    <input id="syncsite" type="hidden" name="syncsite" value="{{((isset($content['syncsite']) && $content['syncsite'] == 1) || (!isset($content['syncsite']) && evo()->getConfig('cache_default')) ? 1 : 0)}}" onchange="documentDirty=true;" />
                </div>
            </div>
        </div>
        <div class="row-col col-lg-6 col-md-6 col-12">
            <div class="row form-row">
                <div class="col-auto col-title">
                    <label for="alias" class="warning">@lang('global.resource_alias')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_alias_help')"></i>
                </div>
                <div class="col">
                    <input name="alias" type="text" maxlength="255" value="{{stripslashes(get_by_key($content, 'alias', '', 'is_scalar'))}}" class="form-control" onchange="documentDirty=true;" />
                </div>
            </div>
            <div class="row form-row">
                <div class="col-auto col-title-10">
                    <label for="link_attributes" class="warning">@lang('global.link_attributes')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.link_attributes_help')"></i>
                </div>
                <div class="col">
                    <input name="link_attributes" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, 'link_attributes', '', 'is_scalar')))}}" class="form-control" onchange="documentDirty=true;" />
                </div>
            </div>
            <div class="row form-row">
                <div class="col-auto col-title-10">
                    <label for="menuindex" class="warning">@lang('global.resource_opt_menu_index')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_menu_index_help')"></i>
                </div>
                <div class="input-group col">
                    <div class="input-group-prepend">
                        <span class="btn btn-secondary" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();documentDirty=true;return false;" style="cursor: pointer;"><i class="fa fa-angle-left"></i></span>
                        <span class="btn btn-secondary" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();documentDirty=true;return false;" style="cursor: pointer;"><i class="fa fa-angle-right"></i></span>
                    </div>
                    <input name="menuindex" type="text" maxlength="6" value="{{$content['menuindex']}}" class="form-control" onchange="documentDirty=true;" />
                </div>
            </div>
            <div class="row form-row form-row-checkbox">
                <div class="col-auto col-title-10">
                    <label for="hidemenucheck" class="warning">@lang('global.resource_opt_show_menu')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_show_menu_help')"></i>
                </div>
                <div class="col">
                    <input name="hidemenucheck" type="checkbox" class="form-checkbox form-control" {{(empty($content['hidemenu']) ? 'checked="checked"' : '')}} onclick="changestate(document.mutate.hidemenu);" />
                    <input type="hidden" name="hidemenu" class="hidden" value="{{(empty($content['hidemenu']) ? 0 : 1)}}" />
                </div>
            </div>
            <div class="row form-row form-row-date">
                <div class="col-auto col-title-10">
                    <label for="pub_date" class="warning">@lang('global.page_data_publishdate')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_publishdate_help')"></i>
                </div>
                <div class="col">
                    <input id="pub_date" type="text" {!!$mx_can_pub!!}name="pub_date" class="form-control DatePicker unstyled" value="{{((int)get_by_key($content, 'pub_date', 0, 'is_scalar') === 0 || !isset($content['pub_date']) ? '' : evo()->toDateFormat($content['pub_date']))}}" onblur="documentDirty=true;" placeholder="{{evo()->getConfig('datetime_format')}} HH:MM:SS" autocomplete="off"/>
                    <span class="input-group-append">
                        <a class="btn text-danger" href="javascript:;" onclick="document.mutate.pub_date.value=''; documentDirty=true; return true;">
                            <i class="{{$_style["icon_calendar_close"]}}" title="@lang('global.remove_date')"></i>
                        </a>
                    </span>
                </div>
            </div>
            <div class="row form-row form-row-date">
                <div class="col-auto col-title-10">
                    <label for="unpub_date" class="warning">@lang('global.page_data_unpublishdate')</label>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_unpublishdate_help')"></i>
                </div>
                <div class="col">
                    <input type="text" id="unpub_date" {!!$mx_can_pub!!}name="unpub_date" class="form-control DatePicker unstyled" value="{{((int)get_by_key($content, 'unpub_date', 0, 'is_scalar') === 0 || !isset($content['unpub_date']) ? '' : evo()->toDateFormat($content['unpub_date']))}}" onblur="documentDirty=true;" placeholder="{{evo()->getConfig('datetime_format')}} HH:MM:SS" autocomplete="off" />
                    <span class="input-group-append">
                        <a class="btn text-danger" href="javascript:;" onclick="document.mutate.unpub_date.value=''; documentDirty=true; return true;">
                            <i class="{{$_style["icon_calendar_close"]}}" title="@lang('global.remove_date')"></i>
                        </a>
                    </span>
                </div>
            </div>
            @if($_SESSION['mgrRole'] == 1 || evo()->getManagerApi()->action != '27' || $_SESSION['mgrInternalKey'] == $content['createdby'] || evo()->hasPermission('change_resourcetype'))
                <div class="row form-row">
                    <div class="col-auto col-title">
                        <label for="type" class="warning">@lang('global.resource_type')</label>
                        <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_type_message')"></i>
                    </div>
                    <div class="col">
                        <select id="type" class="form-control" name="type" onchange="documentDirty=true;">
                            <option value="document"{!!($content['type'] === 'document' || evo()->getManagerApi()->action == '85' || evo()->getManagerApi()->action == '4') ? ' selected="selected"' : ''!!}>@lang('global.resource_type_webpage')</option>
                            <option value="reference"{!!($content['type'] === 'reference' || evo()->getManagerApi()->action == '72') ? ' selected="selected"' : ''!!}>@lang('global.resource_type_weblink')</option>
                        </select>
                    </div>
                </div>
                <div class="row form-row">
                    <div class="col-auto col-title">
                        <label for="contentType" class="warning">@lang('global.page_data_contentType')</label>
                        <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_contentType_help')"></i>
                    </div>
                    <div class="col">
                        @php($custom_content_type = evo()->getConfig('custom_contenttype', 'text/html,text/plain,text/xml'))
                        @php($ct = explode(",", $custom_content_type))
                        <select id="contentType" class="form-control" name="contentType" onchange="documentDirty=true;">
                            @for($i = 0; $i < count($ct); $i++)
                                <option value="{{$ct[$i]}}"{!! ($content['contentType'] == $ct[$i] ? ' selected="selected"' : '') !!}>{{$ct[$i]}}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="row form-row">
                    <div class="col-auto col-title-10">
                        <label for="content_dispo" class="warning">@lang('global.resource_opt_contentdispo')</label>
                        <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_contentdispo_help')"></i>
                    </div>
                    <div class="col">
                        <select id="content_dispo" class="form-control" name="content_dispo" onchange="documentDirty=true;">
                            <option value="0"{!!(empty($content['content_dispo']) ? ' selected="selected"' : '')!!}>@lang('global.inline')</option>
                            <option value="1"{!!(!empty($content['content_dispo']) ? ' selected="selected"' : '')!!}>@lang('global.attachment')</option>
                        </select>
                    </div>
                </div>
            @else
                @if($content['type'] != 'reference' && evo()->getManagerApi()->action != '72')
                    {{-- Non-admin managers creating or editing a document resource --}}
                    <input type="hidden" name="contentType" value="{{(isset($content['contentType']) ? $content['contentType'] : "text/html")}}" />
                    <input type="hidden" name="type" value="document" />
                    <input type="hidden" name="content_dispo" value="{{(isset($content['content_dispo']) ? $content['content_dispo'] : '0')}}" />
                @else
                    {{-- Non-admin managers creating or editing a reference (weblink) resource --}}
                    <input type="hidden" name="type" value="reference" />
                    <input type="hidden" name="contentType" value="text/html" />
                @endif
            @endif
        </div>
    </div>
</div>
<!-- end #tabSettings -->