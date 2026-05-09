<?php

return [
    'key' => 'slang.translates',
    'provider' => \Seiger\sLang\Tables\TranslatesTableData::class,
    'wire_target' => 'search,perPage,setSort,switchView,createInlineRow,updateInlineField,runInlineFieldAction,runHeaderAction,openDeleteModal,closeDeleteModal,deleteConfirmed,goToPage,firstPage,previousPage,nextPage,goLastPage',
    'per_page' => 10,
    'per_page_options' => [5, 10, 20, 30, 50, 100],
    'views' => ['table'],
    'default_view' => 'table',
    'default_sort' => 'tid',
    'default_direction' => 'desc',
    'search' => [
        'enabled' => true,
        'state' => 'search',
        'width' => 'sm',
    ],
    'inline' => [
        'create_provider' => 'createInlineRow',
        'save_provider' => 'updateInlineField',
    ],
    'actions' => [
        [
            'key' => 'create',
            'type' => 'wire',
            'method' => 'createInlineRow',
            'icon' => 'plus',
            'label' => 'sLang::global.add_translation',
            'tone' => 'success',
            'icon_only' => true,
        ],
        [
            'key' => 'delete',
            'type' => 'wire',
            'method' => 'openDeleteModal',
            'icon' => 'trash',
            'label' => 'evo::global.action_delete',
            'tone' => 'danger',
            'icon_only' => true,
            'selection' => 'single',
        ],
    ],
    'columns' => [
        [
            'key' => 'key',
            'type' => 'text',
            'label' => 'KEY',
            'class' => 'evo-ui-table__title-column',
            'sortable' => true,
            'sort_field' => 'key',
            'editable' => true,
            'rules' => ['required', 'string', 'max:256'],
        ],
    ],
    'row_actions' => [
        [
            'key' => 'delete',
            'type' => 'wire',
            'method' => 'openDeleteModal',
            'argument' => 'id',
            'icon' => 'trash',
            'label' => 'evo::global.action_delete',
            'tone' => 'danger',
        ],
    ],
];
