<?php namespace Seiger\sLang\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ModulePanel extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $rawTabs = [];

    /** @var array<string, mixed> */
    public array $context = [];
    public string $activeTab = 'translates';

    /**
     * @param array<int, array<string, mixed>> $tabs
     * @param array<string, mixed> $context
     */
    public function mount(array $tabs = [], string $activeTab = 'translates', array $context = []): void
    {
        $this->rawTabs = $tabs;
        $this->context = $context;
        $this->activeTab = $this->normalizeTab($activeTab);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $this->normalizeTab($tab);
    }

    public function render(): View
    {
        $view = view('sLang::livewire.module-panel', [
            'tabs' => $this->navigationTabs(),
            'activeTab' => $this->activeTab,
            'title' => $this->title(),
            'context' => $this->context,
        ]);

        if (!$view instanceof View) {
            throw new \RuntimeException('Unable to render sLang module panel view.');
        }

        return $view;
    }

    protected function normalizeTab(string $tab): string
    {
        $tab = trim($tab);
        $allowed = collect($this->rawTabs)->pluck('key')->filter()->values()->all();

        return in_array($tab, $allowed, true) ? $tab : ($allowed[0] ?? 'translates');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function navigationTabs(): array
    {
        return collect($this->rawTabs)
            ->map(function (array $tab) {
                $key = (string) ($tab['key'] ?? '');
                $tab['active'] = $key === $this->activeTab;
                $tab['type'] = 'wire';
                $tab['method'] = 'switchTab';
                $tab['argument'] = $key;
                unset($tab['href'], $tab['data']);

                return $tab;
            })
            ->values()
            ->all();
    }

    protected function title(): string
    {
        $title = match ($this->activeTab) {
            'settings' => __('global.settings_config'),
            default => __('sLang::global.dictionary'),
        };

        return is_string($title) ? $title : '';
    }
}
