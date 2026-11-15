<?php namespace Seiger\sLang\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Seiger\sLang\Support\SettingsAccess;

/**
 * Render the dictionary and settings tabs available to the current manager.
 */
class ModulePanel extends Component
{
    /** @var array<int, array<string, mixed>> */
    #[Locked]
    public array $rawTabs = [];

    /** @var array<string, mixed> */
    #[Locked]
    public array $context = [];
    public string $activeTab = 'translates';

    /**
     * Store the module's navigation and select an available tab on initial load.
     *
     * @param array<int, array<string, mixed>> $tabs
     * @param array<string, mixed> $context
     */
    public function mount(array $tabs = [], string $activeTab = 'translates', array $context = []): void
    {
        $this->rawTabs = $tabs;
        $this->context = $context;
        $this->activeTab = $this->normalizeTab($activeTab, $this->availableTabs());
    }

    /**
     * Select a tab from the navigation allowed for the current manager.
     */
    public function switchTab(string $tab): void
    {
        $this->activeTab = $this->normalizeTab($tab, $this->availableTabs());
    }

    /**
     * Recheck navigation before rendering because the active tab is bound to client state.
     */
    public function render(): View
    {
        $tabs = $this->availableTabs();
        $this->activeTab = $this->normalizeTab($this->activeTab, $tabs);

        $view = view('sLang::livewire.module-panel', [
            'tabs' => $this->navigationTabs($tabs),
            'activeTab' => $this->activeTab,
            'title' => $this->title(),
            'context' => $this->context,
        ]);

        if (!$view instanceof View) {
            throw new \RuntimeException('Unable to render sLang module panel view.');
        }

        return $view;
    }

    /**
     * Fall back to the first available tab when the requested tab is unavailable.
     *
     * @param array<int, array<string, mixed>> $tabs
     */
    protected function normalizeTab(string $tab, array $tabs): string
    {
        $tab = trim($tab);
        $allowed = array_values(array_filter(array_column($tabs, 'key')));

        return in_array($tab, $allowed, true) ? $tab : ($allowed[0] ?? 'translates');
    }

    /**
     * Keep the settings tab available only while the manager has permission to use it.
     *
     * @return array<int, array<string, mixed>>
     * @since 2.1.0
     */
    protected function availableTabs(): array
    {
        if (SettingsAccess::allows()) {
            return $this->rawTabs;
        }

        return array_filter($this->rawTabs, static fn (array $tab): bool => ($tab['key'] ?? '') !== 'settings');
    }

    /**
     * Format available tabs as Livewire navigation actions.
     *
     * @param array<int, array<string, mixed>> $tabs
     * @return array<int, array<string, mixed>>
     */
    protected function navigationTabs(array $tabs): array
    {
        return collect($tabs)
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
