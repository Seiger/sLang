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

    /**
     * @return array<int, string>
     */
    public function languages(): array
    {
        return sLang::langConfig();
    }

    /**
     * @return array<int, string>
     */
    public function frontendLanguages(): array
    {
        return sLang::langFront();
    }

    /**
     * @return array<int, array{value: string, label: string, short: string, segment: string, default: bool}>
     */
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

    /**
     * @return array<int, string>
     */
    public function contentFields(): array
    {
        return sLang::siteContentFields();
    }
}
