<?php namespace Seiger\sLang\Support;

use EvolutionCMS\Models\UserAttribute;
use EvolutionCMS\Models\UserRole;
use EvolutionCMS\Support\ModuleAccess;

/**
 * Check the manager's session, current role and access to the sLang file module.
 *
 * @since 2.1.0
 */
final class ManagerAccess
{
    /**
     * Require module access and any additional Evolution permissions named by the caller.
     *
     * Read the current role so a stale session cannot retain a revoked permission.
     * Permission names must come from server code.
     *
     * @since 2.1.0
     */
    public static function allows(string ...$permissions): bool
    {
        $evo = evo();
        $userId = (int) $evo->getLoginUserID('mgr');
        if ($userId <= 0) {
            return false;
        }

        $permissions = ['access_permissions', 'exec_module', ...$permissions];
        foreach ($permissions as $permission) {
            if (!$evo->hasPermission($permission, 'mgr')) {
                return false;
            }
        }

        $role = UserRole::query()
            ->whereIn('id', UserAttribute::query()->select('role')->where('internalKey', $userId))
            ->first(['id', ...$permissions]);

        if (!$role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$role->{$permission}) {
                return false;
            }
        }

        if (!class_exists(ModuleAccess::class)) {
            return true;
        }

        // Match the registered file because the module title and ID depend on the manager language.
        $moduleFile = dirname(__DIR__, 2) . '/modules/sLangModule.php';
        foreach ($evo->modulesFromFile as $module) {
            if (($module['file'] ?? null) === $moduleFile) {
                return ModuleAccess::canRunFileModule((int) $role->getKey(), $module);
            }
        }

        return false;
    }

    /**
     * Stop the request when module access or a permission for the action is missing.
     *
     * @since 2.1.0
     */
    public static function authorize(string ...$permissions): void
    {
        if (!self::allows(...$permissions)) {
            abort(403);
        }
    }
}
