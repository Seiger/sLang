<div class="row form-row">
    <div class="row-col col-lg-12 col-12">
        <div class="row form-row">
            <div class="col-auto col-title-auto">
                <label for="{{$row['name']}}" class="warning">
                    {{$row['caption']}}
                    @if(evo()->hasPermission('edit_template'))<br/><small class="text-muted">[*{{$row['name']}}*]</small>@endif
                    @if(substr($tvPBV, 0, 8) == '@INHERIT')<br/><small class="comment inherited">({{$_lang['tmplvars_inherited']}})</small>@endif
                </label>
                @if(!empty($row['description']))<i class="{{$_style["icon_question_circle"]}}" data-tooltip="{{$row['description']}}"></i>@endif
            </div>
            <div class="col">
                {!! renderFormElement(
                    $row['type'],
                    $row['id'],
                    $row['default_text'],
                    $row['elements'],
                    $tvPBV,
                    '',
                    $row,
                    $tvsArray,
                    $content
                ) !!}
            </div>
        </div>
    </div>
</div>