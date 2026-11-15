<?php namespace Seiger\sLang\Support;

use EvolutionCMS\Models\RolePermissions;
use EvolutionCMS\Support\ModuleAccess;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check the manager's session, current role and access to the sLang file module.
 *
 * @since 2.0.0
 */
final class ManagerAccess
{
    /**
     * Require module access and any additional Evolution permissions named by the caller.
     *
     * Read the current role from the primary connection so revocations take effect on the next request.
     * Permission names must come from server code.
     *
     * @since 2.0.0
     */
    public static function allows(string ...$permissions): bool
    {
        if (empty($_SESSION['mgrValidated'])) {
            return false;
        }

        $core = evo();
        $userId = (int) $core->getLoginUserID('mgr');
        if ($userId <= 0) {
            return false;
        }

        $permissions = ['access_permissions', 'exec_module', ...$permissions];
        foreach ($permissions as $permission) {
            if (!$core->hasPermission($permission, 'mgr')) {
                return false;
            }
        }

        $grants = RolePermissions::query()
            ->useWritePdo()
            ->join('user_roles', 'user_roles.id', '=', 'role_permissions.role_id')
            ->join('user_attributes', 'user_attributes.role', '=', 'user_roles.id')
            ->join('users', 'users.id', '=', 'user_attributes.internalKey')
            ->where('users.id', $userId)
            ->whereIn('role_permissions.permission', $permissions)
            ->get(['role_permissions.role_id', 'role_permissions.permission']);

        $permissionsByRole = [];
        foreach ($grants as $grant) {
            $permissionsByRole[(int) $grant->role_id][] = $grant->permission;
        }

        $roleId = array_key_first($permissionsByRole);
        if (count($permissionsByRole) !== 1 || array_diff($permissions, $permissionsByRole[$roleId])) {
            return false;
        }

        if (!class_exists(ModuleAccess::class)) {
            return true;
        }

        // Match the registered file because the module title and ID depend on the manager language.
        $moduleFile = dirname(__DIR__, 2) . '/modules/sLangModule.php';
        foreach ($core->modulesFromFile as $module) {
            if (($module['file'] ?? null) === $moduleFile) {
                return ModuleAccess::canRunFileModule($roleId, $module);
            }
        }

        return false;
    }

    /**
     * Stop the request when module access or a permission for the action is missing.
     * Send the denial directly because Evolution's exception handler can turn it into a 500.
     *
     * @since 2.0.0
     */
    public static function authorize(string ...$permissions): void
    {
        if (self::allows(...$permissions)) {
            return;
        }

        $message = __('global.access_permission_denied');
        $response = new Response(is_string($message) ? $message : 'Access permission denied.', Response::HTTP_FORBIDDEN, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
        $response->sendHeaders();
        $response->sendContent();

        if (function_exists('evo_exit')) {
            evo_exit();
        }
        exit;
    }
}
