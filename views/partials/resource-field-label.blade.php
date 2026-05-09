@props([
    'for',
    'label',
    'help' => null,
    'width' => 'col-title-11',
])

<div class="col-auto {{ $width }}">
    <label for="{{ $for }}" class="warning">{{ $label }}</label>
    @if($help)
        <i class="{{ $_style['icon_question_circle'] }}" data-tooltip="{{ $help }}"></i>
    @endif
</div>
