<style>
    .evo-ui .slang-documentation-link {
        align-items: center;
        background: var(--evo-ui-bg);
        border: 1px solid var(--evo-ui-border);
        border-radius: 999px;
        bottom: 16px;
        box-shadow: 0 4px 16px rgb(31 41 55 / 12%);
        box-sizing: border-box;
        color: var(--evo-ui-primary);
        display: flex;
        height: 40px;
        overflow: hidden;
        position: fixed;
        right: 16px;
        text-decoration: none;
        transition: width .18s ease;
        width: 40px;
        z-index: 1100;
    }

    .evo-ui .slang-documentation-link:is(:hover, :focus-visible) {
        color: var(--evo-ui-primary);
        text-decoration: none;
        width: calc(40px + 5rem);
    }

    .evo-ui .slang-documentation-link:focus-visible {
        outline: 2px solid var(--evo-ui-primary);
        outline-offset: 2px;
    }

    .slang-documentation-link__icon {
        flex: 0 0 20px;
        height: 20px;
        margin: 0 9px;
        width: 20px;
    }

    .slang-documentation-link__label {
        align-items: center;
        display: flex;
        font-size: .875rem;
        font-weight: 700;
        gap: .5rem;
        opacity: 0;
        transition: opacity .12s ease;
        white-space: nowrap;
    }

    .slang-documentation-link:is(:hover, :focus-visible) .slang-documentation-link__label {
        opacity: 1;
    }

    .slang-documentation-link__external {
        height: 16px;
        width: 16px;
    }

    @media (prefers-reduced-motion: reduce) {
        .evo-ui .slang-documentation-link,
        .slang-documentation-link__label {
            transition: none;
        }
    }
</style>

<a
    class="slang-documentation-link"
    href="https://seiger.github.io/sLang/"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="{{ __('sLang::global.documentation') }}"
>
    <x-evo::icon name="world" class="slang-documentation-link__icon" />
    <span class="slang-documentation-link__label">
        sLang
        <x-evo::icon name="external-link" class="slang-documentation-link__external" />
    </span>
</a>
