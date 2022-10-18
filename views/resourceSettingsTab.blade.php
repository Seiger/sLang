<!-- Settings -->
<div class="tab-page" id="tabSettings">
    <h2 class="tab">@lang('global.settings_page_settings')</h2>
    <script type="text/javascript">tpSettings.addTabPage(document.getElementById("tabSettings"));</script>

    <table>
        @php($mx_can_pub = evo()->hasPermission('publish_document') ? '' : 'disabled="disabled" ')
        <tr>
            <td>
                <span class="warning">@lang('global.resource_opt_published')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_published_help')"></i>
            </td>
            <td>
                <input {!!$mx_can_pub!!}name="publishedcheck" type="checkbox" class="checkbox" {!!(isset($content['published']) && $content['published'] == 1) || (!isset($content['published']) && evo()->getConfig('publish_default')) ? "checked" : ''!!} onclick="changestate(document.mutate.published);" />
                <input type="hidden" name="published" value="{{(isset($content['published']) && $content['published'] == 1) || (!isset($content['published']) && evo()->getConfig('publish_default')) ? 1 : 0}}" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_alias')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_alias_help')"></i>
            </td>
            <td>
                <input name="alias" type="text" maxlength="100" value="{{stripslashes(get_by_key($content, 'alias', '', 'is_scalar'))}}" class="inputBox" onchange="documentDirty=true;" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.link_attributes')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.link_attributes_help')"></i>
            </td>
            <td>
                <input name="link_attributes" type="text" maxlength="255" value="{{evo()->getPhpCompat()->htmlspecialchars(stripslashes(get_by_key($content, 'link_attributes', '', 'is_scalar')))}}" class="inputBox" onchange="documentDirty=true;" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.page_data_publishdate')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_publishdate_help')"></i>
            </td>
            <td>
                <input type="text" id="pub_date" {!!$mx_can_pub!!}name="pub_date" class="DatePicker" value="{{((int)get_by_key($content, 'pub_date', 0, 'is_scalar') === 0 || !isset($content['pub_date']) ? '' : evo()->toDateFormat($content['pub_date']))}}" onblur="documentDirty=true;" />
                <a href="javascript:" onclick="document.mutate.pub_date.value=''; return true;" onmouseover="window.status='@lang('global.remove_date')'; return true;" onmouseout="window.status=''; return true;">
                    <i class="{{$_style["icon_calendar_close"]}}" title="@lang('global.remove_date')"></i></a>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><em>{{evo()->getConfig('datetime_format')}} HH:MM:SS</em></td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.page_data_unpublishdate')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_unpublishdate_help')"></i>
            </td>
            <td>
                <input type="text" id="unpub_date" {!!$mx_can_pub!!}name="unpub_date" class="DatePicker" value="{{((int)get_by_key($content, 'unpub_date', 0, 'is_scalar') === 0 || !isset($content['unpub_date']) ? '' : evo()->toDateFormat($content['unpub_date']))}}" onblur="documentDirty=true;" />
                <a href="javascript:" onclick="document.mutate.unpub_date.value=''; return true;" onmouseover="window.status='@lang('global.remove_date')'; return true;" onmouseout="window.status=''; return true;">
                    <i class="{{$_style["icon_calendar_close"]}}" title="@lang('global.remove_date')"></i></a>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><em>{{evo()->getConfig('datetime_format')}} HH:MM:SS</em></td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.page_data_template')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_template_help')"></i>
            </td>
            <td>
                <select id="template" name="template" class="inputBox" onchange="templateWarning();">
                    <option value="0">(blank)</option>
                    @php($templates = \EvolutionCMS\Models\SiteTemplate::query()
                            ->select('site_templates.templatename',
                                'site_templates.selectable',
                                'site_templates.category',
                                'site_templates.id', 'categories.category AS category_name')
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
            </td>
        </tr>
        <tr>
            <td colspan="2"><div class='split'></div></td>
        </tr>
        @if($_SESSION['mgrRole'] == 1 || evo()->getManagerApi()->action != '27' || $_SESSION['mgrInternalKey'] == $content['createdby'] || evo()->hasPermission('change_resourcetype'))
            <tr>
                <td>
                    <span class="warning">@lang('global.resource_type')</span>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_type_message')"></i>
                </td>
                <td>
                    <select name="type" class="inputBox" onchange="documentDirty=true;">
                        <option value="document"{!!($content['type'] === 'document' || evo()->getManagerApi()->action == '85' || evo()->getManagerApi()->action == '4') ? ' selected="selected"' : ''!!}>@lang('global.resource_type_webpage')</option>
                        <option value="reference"{!!($content['type'] === 'reference' || evo()->getManagerApi()->action == '72') ? ' selected="selected"' : ''!!}>@lang('global.resource_type_weblink')</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="warning">@lang('global.page_data_contentType')</span>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_contentType_help')"></i>
                </td>
                <td>
                    @php($custom_content_type = evo()->getConfig('custom_contenttype', 'text/html,text/plain,text/xml'))
                    @php($ct = explode(",", $custom_content_type))
                    <select name="contentType" class="inputBox" onchange="documentDirty=true;">
                        @for($i = 0; $i < count($ct); $i++)
                            <option value="{{$ct[$i]}}"{!! ($content['contentType'] == $ct[$i] ? ' selected="selected"' : '') !!}>{{$ct[$i]}}</option>
                        @endfor
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="warning">@lang('global.resource_opt_contentdispo')</span>
                    <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_contentdispo_help')"></i>
                </td>
                <td>
                    <select name="content_dispo" class="inputBox" size="1" onchange="documentDirty=true;">
                        <option value="0"{!!(empty($content['content_dispo']) ? ' selected="selected"' : '')!!}>@lang('global.inline')</option>
                        <option value="1"{!!(!empty($content['content_dispo']) ? ' selected="selected"' : '')!!}>@lang('global.attachment')</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><div class='split'></div></td>
            </tr>
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
        <tr>
            <td>
                <span class="warning">@lang('global.resource_opt_menu_index')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_menu_index_help')"></i>
            </td>
            <td>
                <input name="menuindex" type="text" maxlength="6" value="{{$content['menuindex']}}" class="inputBox" onchange="documentDirty=true;" />
                <a href="javascript:;" class="btn btn-secondary" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();documentDirty=true;return false;"><i class="{{$_style['icon_angle_left']}}"></i></a>
                <a href="javascript:;" class="btn btn-secondary" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();documentDirty=true;return false;"><i class="{{$_style['icon_angle_right']}}"></i></a>
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_opt_show_menu')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_show_menu_help')"></i>
            </td>
            <td>
                <input name="hidemenucheck" type="checkbox" class="checkbox" {{(empty($content['hidemenu']) ? 'checked="checked"' : '')}} onclick="changestate(document.mutate.hidemenu);" /><input type="hidden" name="hidemenu" class="hidden" value="{{(empty($content['hidemenu']) ? 0 : 1)}}" />
            </td>
        </tr>
        <tr>
            <td valign="top">
                <span class="warning">@lang('global.resource_parent')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_parent_help')"></i>
            </td>
            <td valign="top">
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
                <b><span id="parentName">{{(isset($_REQUEST['pid']) ? entities($_REQUEST['pid']) : $content['parent'])}} ({{entities($parentname)}})</span></b>
                <input type="hidden" name="parent" value="{{(isset($_REQUEST['pid']) ? entities($_REQUEST['pid']) : $content['parent'])}}" onchange="documentDirty=true;" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_opt_folder')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_folder_help')"></i>
            </td>
            <td>
                <input name="isfoldercheck" type="checkbox" class="checkbox" {{((!empty($content['isfolder']) || evo()->getManagerApi()->action == '85') ? "checked" : '')}} onclick="changestate(document.mutate.isfolder);" />
                <input type="hidden" name="isfolder" value="{{((!empty($content['isfolder']) || evo()->getManagerApi()->action == '85') ? 1 : 0)}}" onchange="documentDirty=true;" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_opt_alvisibled')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_alvisibled_help')"></i>
            </td>
            <td>
                <input name="alias_visible_check" type="checkbox" class="checkbox" {{((!isset($content['alias_visible']) || $content['alias_visible'] == 1) ? "checked" : '')}} onclick="changestate(document.mutate.alias_visible);" /><input type="hidden" name="alias_visible" value="{{((!isset($content['alias_visible']) || $content['alias_visible'] == 1) ? 1 : 0)}}" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_opt_richtext')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_richtext_help')"></i>
            </td>
            <td>
                <input name="richtextcheck" type="checkbox" class="checkbox" {{(empty($content['richtext']) && evo()->getManagerApi()->action == '27' ? '' : "checked")}} onclick="changestate(document.mutate.richtext);" />
                <input type="hidden" name="richtext" value="{{(empty($content['richtext']) && evo()->getManagerApi()->action == '27' ? 0 : 1)}}" onchange="documentDirty=true;" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.track_visitors_title')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_trackvisit_help')"></i>
            </td>
            <td>
                <input name="hide_from_treecheck" type="checkbox" class="checkbox" {!! empty($content['hide_from_tree']) ? 'checked="checked"' : '' !!} onclick="changestate(document.mutate.hide_from_tree);" /><input type="hidden" name="hide_from_tree" value="{!! empty($content['hide_from_tree']) ? 0 : 1 !!}" onchange="documentDirty=true;" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.page_data_searchable')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_searchable_help')"></i>
            </td>
            <td>
                <input name="searchablecheck" type="checkbox" class="checkbox" {{(isset($content['searchable']) && $content['searchable'] == 1) || (!isset($content['searchable']) && evo()->getConfig('search_default')) ? "checked" : ''}} onclick="changestate(document.mutate.searchable);" /><input type="hidden" name="searchable" value="{{((isset($content['searchable']) && $content['searchable'] == 1) || (!isset($content['searchable']) && evo()->getConfig('search_default')) ? 1 : 0)}}" onchange="documentDirty=true;" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.page_data_cacheable')</span>
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.page_data_cacheable_help')"></i>
            </td>
            <td>
                <input name="cacheablecheck" type="checkbox" class="checkbox" {{((isset($content['cacheable']) && $content['cacheable'] == 1) || (!isset($content['cacheable']) && evo()->getConfig('cache_default')) ? "checked" : '')}} onclick="changestate(document.mutate.cacheable);" />
                <input type="hidden" name="cacheable" value="{{((isset($content['cacheable']) && $content['cacheable'] == 1) || (!isset($content['cacheable']) && evo()->getConfig('cache_default')) ? 1 : 0)}}" onchange="documentDirty=true;" />
            </td>
        </tr>
        <tr>
            <td>
                <span class="warning">@lang('global.resource_opt_emptycache')</span>
                <input type="hidden" name="syncsite" value="1" />
                <i class="{{$_style["icon_question_circle"]}}" data-tooltip="@lang('global.resource_opt_emptycache_help')"></i>
            </td>
            <td>
                <input name="syncsitecheck" type="checkbox" class="checkbox" checked="checked" onclick="changestate(document.mutate.syncsite);" />
            </td>
        </tr>
    </table>
</div>
<!-- end #tabSettings -->