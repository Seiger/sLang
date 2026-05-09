<div
    class="evo-ui-tabs evo-ui-tabs--module"
    x-data="{
        activeTab: $wire.entangle('activeTab').live,
        pendingTab: null,
        showUnsavedPrompt: false,
        isDirty() {
            return window.EvoUI?.form?.isDirty
                ? window.EvoUI.form.isDirty()
                : document.querySelector('[data-evo-form-dirty=&quot;true&quot;]') !== null;
        },
        requestModuleTab(tab) {
            if (this.activeTab === tab) {
                return;
            }

            if (!this.isDirty()) {
                this.activeTab = tab;
                return;
            }

            this.pendingTab = tab;
            this.showUnsavedPrompt = true;
        },
        closeUnsavedPrompt() {
            this.showUnsavedPrompt = false;
            this.pendingTab = null;
        },
        applyPendingNavigation() {
            if (this.pendingTab) {
                this.activeTab = this.pendingTab;
            }

            this.closeUnsavedPrompt();
        },
        discardAndSwitch() {
            this.applyPendingNavigation();
        },
        saveAndSwitch() {
            document.querySelector('[data-evo-form]')?.requestSubmit();
            this.waitForCleanAndSwitch();
        },
        waitForCleanAndSwitch() {
            if (!window.EvoUI?.form?.waitForClean) {
                this.afterSaved();
                return;
            }

            window.EvoUI.form.waitForClean(() => this.applyPendingNavigation());
        },
        afterSaved() {
            if (!this.showUnsavedPrompt || !this.pendingTab) {
                return;
            }

            this.$nextTick(() => this.waitForCleanAndSwitch());
        }
    }"
    x-on:evo-ui:form.saved.window="afterSaved()"
>
    <nav class="evo-ui-nav-tabs evo-ui-tab-labels tabs-lift" aria-label="{{ $title }}">
        <div class="evo-ui-nav-tabs__list" role="tablist">
            @foreach($tabs as $tab)
                @php($key = (string) ($tab['key'] ?? $tab['argument'] ?? ''))
                <button
                    type="button"
                    role="tab"
                    class="tab evo-ui-nav-tab"
                    x-bind:class="{ 'tab-active is-active': activeTab === @js($key) }"
                    x-bind:aria-selected="activeTab === @js($key) ? 'true' : 'false'"
                    x-on:click="requestModuleTab(@js($key))"
                >
                    <span class="evo-ui-nav-tab__label">
                        @if(!empty($tab['icon']))
                            <x-evo::icon :name="$tab['icon']" class="evo-ui-nav-tab__icon" />
                        @endif
                        <span>{!! $tab['label'] ?? '' !!}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </nav>

    <div class="tab-content">
        <section class="evo-ui-surface" data-evo-tab-panel="{{ $activeTab }}">
            @if($activeTab === 'settings')
                <livewire:slang.settings-panel wire:key="slang-settings-panel" />
            @else
                <x-evo::table.livewire
                    preset="slang.translates"
                    :context="$context"
                    wire-key="slang-translates"
                />
            @endif
        </section>
    </div>

    <div
        class="evo-ui-modal-backdrop"
        role="presentation"
        x-cloak
        x-show="showUnsavedPrompt"
        x-on:click.self="closeUnsavedPrompt()"
        x-on:keydown.escape.window="closeUnsavedPrompt()"
    >
        <section class="evo-ui-modal evo-ui-modal--sm" role="dialog" aria-modal="true" aria-labelledby="slang-unsaved-title" x-on:click.stop>
            <header class="evo-ui-modal__header">
                <div class="evo-ui-modal__title" id="slang-unsaved-title">
                    <x-evo::icon name="alert-triangle" />
                    <span>@lang('evo::global.unsaved_changes_title')</span>
                </div>

                <button type="button" class="evo-ui-modal__close" title="@lang('evo::global.action_cancel')" aria-label="@lang('evo::global.action_cancel')" x-on:click="closeUnsavedPrompt()">
                    <x-evo::icon name="x" />
                </button>
            </header>

            <div class="evo-ui-confirm__body">
                <p class="evo-ui-confirm__message">@lang('evo::global.unsaved_changes_message')</p>
            </div>

            <footer class="evo-ui-modal__footer">
                <button type="button" class="evo-ui-btn" x-on:click="closeUnsavedPrompt()">@lang('evo::global.action_cancel')</button>
                <span class="evo-ui-modal__footer-spacer"></span>
                <button type="button" class="evo-ui-btn" x-on:click="discardAndSwitch()">@lang('evo::global.action_discard')</button>
                <button type="button" class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled" x-on:click="saveAndSwitch()">
                    <x-evo::icon name="check" />
                    <span>@lang('evo::global.action_save')</span>
                </button>
            </footer>
        </section>
    </div>
</div>
