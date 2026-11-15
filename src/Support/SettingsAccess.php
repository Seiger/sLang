<?php namespace Seiger\sLang\Support;

/**
 * Apply Evolution's settings permission to global sLang settings and dictionary maintenance.
 *
 * @since 2.1.0
 */
final class SettingsAccess
{
    /**
     * Check whether the manager can administer site-wide sLang data.
     *
     * @since 2.1.0
     */
    public static function allows(): bool
    {
        return ManagerAccess::allows('settings');
    }

    /**
     * Require permission before reading settings or changing settings or dictionary data.
     *
     * @since 2.1.0
     */
    public static function authorize(): void
    {
        ManagerAccess::authorize('settings');
    }
}
