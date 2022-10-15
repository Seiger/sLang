<?php namespace Seiger\sLang\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Seiger\sLang\Models\sLangTranslate;
use Seiger\sLang\sLang;

class sLangController
{
    public $url;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->url = $this->moduleUrl();
    }

    /**
     * List of DB translations
     *
     * @return array
     */
    public function dictionary()
    {
        if (request()->has('search')) {
            $where[] = '`key` LIKE \'%'.request()->search.'%\'';
            foreach (sLang::langConfig() as $item) {
                $where[] = '`'.$item.'` LIKE \'%'.request()->search.'%\'';
            }
            $translates = sLangTranslate::whereRaw(implode(' OR ', $where))->orderByDesc('tid')->paginate(17);
            $translates->withPath($this->url.'&search='.request()->search);
        } else {
            $translates = sLangTranslate::orderByDesc('tid')->paginate(17);
            $translates->withPath($this->url);
        }

        return $translates;
    }

    /**
     * Show tab page with sOffer files
     *
     * @return View
     */
    public function index(): View
    {
        return $this->view('index');
    }

    /**
     * Module url
     *
     * @return string
     */
    protected function moduleUrl(): string
    {
        return 'index.php?a=112&id=' . md5(__('sLang::global.slang'));
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'A title is required',
            'body.required' => 'A message is required',
        ];
    }

    /**
     * Display render
     *
     * @param string $tpl
     * @param array $data
     * @return bool
     */
    public function view(string $tpl, array $data = [])
    {
        return \View::make('sLang::'.$tpl, $data);
    }
}