<?php namespace Seiger\sLang\Support;

use Seiger\sLang\Facades\sLang;

class LanguageBridge
{
    public function enabled(): bool
    {
        return trim((string) evo()->getConfig('s_lang_enable', '1')) !== '0';
    }

    public function default(): string
    {
        return sLang::langDefault();
    }

    public function languages(): array
    {
        return sLang::langConfig();
    }

    public function frontendLanguages(): array
    {
        return sLang::langFront();
    }

    public function options(): array
    {
        $list = sLang::langList();

        return collect($this->languages())
            ->map(fn (string $locale) => [
                'value' => $locale,
                'label' => (string) ($list[$locale]['name'] ?? strtoupper($locale)),
                'short' => (string) ($list[$locale]['short'] ?? strtoupper($locale)),
                'segment' => sLang::langSegment($locale),
                'default' => $locale === $this->default(),
            ])
            ->values()
            ->all();
    }

    public function contentFields(): array
    {
        return sLang::siteContentFields();
    }
}

