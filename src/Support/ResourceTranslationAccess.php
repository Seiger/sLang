<?php namespace Seiger\sLang\Support;

use EvolutionCMS\Models\DocumentGroup;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvar;
use EvolutionCMS\Support\BackendCodePermissions;
use EvolutionCMS\Support\TvBindingGuard;
use Illuminate\Database\Eloquent\Builder;

/**
 * Apply Evolution's resource and TV permissions to the resource translation save event.
 *
 * @since 2.1.0
 */
final class ResourceTranslationAccess
{
    /**
     * Load the saved resource by the ID from the core event if the manager can edit it.
     *
     * The native save handles creation and move permissions. Check the actual group assignments
     * because Evolution can refresh the persisted privacy flag after the save event.
     *
     * @param mixed $eventId The resource ID reported by Evolution.
     * @param mixed $mode The save mode reported by Evolution.
     * @param bool $checkStoredPrivacy Also enforce the flag when checking the original resource.
     *
     * @since 2.1.0
     */
    public static function savedResource(mixed $eventId, mixed $mode, bool $checkStoredPrivacy = false): ?SiteContent
    {
        if ((!is_int($eventId) && !is_string($eventId)) || !in_array($mode, ['new', 'upd'], true)) {
            return null;
        }

        $id = filter_var($eventId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $evo = evo();
        $role = (int) ($_SESSION['mgrRole'] ?? 0);
        if ($id === false || $role <= 0 || (int) $evo->getLoginUserID('mgr') <= 0
            || !$evo->hasPermission('access_permissions', 'mgr')
            || !$evo->hasPermission('save_document', 'mgr')
            || !$evo->hasPermission($mode === 'new' ? 'new_document' : 'edit_document', 'mgr')) {
            return null;
        }

        $resource = SiteContent::withTrashed()->find($id, ['id', 'template', 'privatemgr']);
        if (!$resource || $role === 1 || (int) $evo->getConfig('use_udperms') !== 1) {
            return $resource;
        }

        $groups = DocumentGroup::query()->where('document_groups.document', $id);
        $restricted = ($checkStoredPrivacy && (int) $resource->privatemgr === 1)
            || (clone $groups)
                ->join('membergroup_access', 'document_groups.document_group', '=', 'membergroup_access.documentgroup')
                ->where('membergroup_access.context', 0)
                ->exists();

        return !$restricted || $groups->whereIn('document_group', self::managerGroups())->exists()
            ? $resource
            : null;
    }

    /**
     * Limit an editor TV query to unrestricted TVs and those assigned to the manager's groups.
     *
     * @param Builder<SiteTmplvar> $query
     * @return Builder<SiteTmplvar>
     * @since 2.1.0
     */
    public static function restrictTemplateVariables($query)
    {
        if ((int) ($_SESSION['mgrRole'] ?? 0) === 1) {
            return $query;
        }

        $groups = self::managerGroups();

        return $query->where(static function ($query) use ($groups) {
            $query->whereDoesntHave('tmplvarAccess')
                ->orWhereHas('tmplvarAccess', static fn ($access) => $access->whereIn('documentgroup', $groups));
        });
    }

    /**
     * Apply the core's backend-code permission to translated values where its binding guard is available.
     *
     * @since 2.1.0
     */
    public static function allowsValue(string $value): bool
    {
        return !class_exists(TvBindingGuard::class)
            || !TvBindingGuard::hasCodeBinding($value)
            || (class_exists(BackendCodePermissions::class) && BackendCodePermissions::grantedBy(
                static fn (string $permission): bool => (bool) evo()->hasPermission($permission, 'mgr')
            ));
    }

    /**
     * Use the manager document groups from the current Evolution session.
     *
     * @return list<int>
     * @since 2.1.0
     */
    private static function managerGroups(): array
    {
        return array_values(array_map('intval', (array) ($_SESSION['mgrDocgroups'] ?? [])));
    }
}
