<?php

declare(strict_types=1);

namespace Seiger\sLang\Support;

use Seiger\sLang\Livewire\ModulePanel;
use Seiger\sLang\Livewire\SettingsPanel;

final class SettingsManagerStub
{
    public int $userId = 0;
    public array $permissions = [];
    public array $modulesFromFile = [];

    public function getLoginUserID(string $context): int
    {
        slang_assert($context === 'mgr', 'Settings must use the manager session.');

        return $this->userId;
    }

    public function hasPermission(string $permission, string $context): int
    {
        slang_assert($context === 'mgr', 'Settings must use manager permissions.');

        return (int) in_array($permission, $this->permissions, true);
    }
}

final class SettingsRolePermissionsStub
{
    public static array $roleIds = [];
    public static array $grants = [];
    private bool $readsPrimary = false;
    private array $joins = [];
    private array $permissions = [];

    public static function query(): self
    {
        return new self();
    }

    public function useWritePdo(): self
    {
        $this->readsPrimary = true;
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[$table] = [$first, $operator, $second];
        return $this;
    }

    public function where(string $column, int $userId): self
    {
        slang_assert($column === 'users.id' && $userId === evo()->userId, 'Use the signed-in manager ID.');
        return $this;
    }

    public function whereIn(string $column, array $permissions): self
    {
        slang_assert($column === 'role_permissions.permission', 'Filter grants by permission name.');
        $this->permissions = $permissions;
        return $this;
    }

    public function get(array $columns): array
    {
        slang_assert($this->readsPrimary, 'Read grants from the primary connection.');
        slang_assert($this->joins === [
            'user_roles' => ['user_roles.id', '=', 'role_permissions.role_id'],
            'user_attributes' => ['user_attributes.role', '=', 'user_roles.id'],
            'users' => ['users.id', '=', 'user_attributes.internalKey'],
        ], 'Match permissions against the current user and role.');
        slang_assert($columns === ['role_permissions.role_id', 'role_permissions.permission'], 'Read role IDs and permission names.');

        $rows = [];
        foreach (self::$roleIds as $roleId) {
            foreach (array_intersect(self::$grants[$roleId] ?? [], $this->permissions) as $permission) {
                $rows[] = (object) ['role_id' => $roleId, 'permission' => $permission];
            }
        }
        return $rows;
    }
}

class SettingsComponentStub {}

final class SettingsModuleAccessStub
{
    public static function canRunFileModule(int $roleId, array $module): bool
    {
        return in_array($roleId, $module['properties']['roles'], true);
    }
}

function evo(): SettingsManagerStub
{
    static $manager;

    return $manager ??= new SettingsManagerStub();
}

function __(string $key): string
{
    slang_assert($key === 'global.access_permission_denied', 'Use the localized Evolution permission denial.');
    return 'Access permission denied.';
}

final class SettingsDeniedResponseStub
{
    public const HTTP_FORBIDDEN = 403;

    public function __construct(string $body, private int $status, array $headers)
    {
        slang_assert($body === 'Access permission denied.', 'Return the localized denial.');
        slang_assert($headers === ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store'], 'Send a non-cacheable plain-text denial.');
    }

    public function sendHeaders(): void {}

    public function sendContent(): never
    {
        throw new \RuntimeException('Denied', $this->status);
    }
}

function assertSettingsDenied(SettingsPanel $panel): void
{
    foreach (['boot', 'save', 'cleanupObsoleteTranslations'] as $method) {
        try {
            $panel->{$method}();
        } catch (\RuntimeException $exception) {
            slang_assert($exception->getCode() === 403, "Expected 403 from {$method}.");
            continue;
        }

        throw new \RuntimeException("Expected settings permission denial before {$method}.");
    }
}

class_alias(SettingsRolePermissionsStub::class, 'EvolutionCMS\Models\RolePermissions');
class_alias(SettingsDeniedResponseStub::class, 'Symfony\Component\HttpFoundation\Response');
class_alias(SettingsComponentStub::class, 'Livewire\Component');

require slang_path('src/Support/ManagerAccess.php');
require slang_path('src/Support/SettingsAccess.php');
require slang_path('src/Livewire/SettingsPanel.php');
require slang_path('src/Livewire/ModulePanel.php');

slang_group('settings-access', function (): void {
    slang_test('settings require a manager session, module access and current settings permission', function (): void {
        $panel = new SettingsPanel();
        $manager = evo();
        $manager->permissions = ['access_permissions', 'exec_module', 'settings'];
        $_SESSION['mgrValidated'] = false;
        assertSettingsDenied($panel);

        $manager->userId = 42;
        SettingsRolePermissionsStub::$roleIds = [5];
        SettingsRolePermissionsStub::$grants = [5 => $manager->permissions, 6 => $manager->permissions];
        assertSettingsDenied($panel);

        $_SESSION['mgrValidated'] = true;
        SettingsRolePermissionsStub::$roleIds = [];
        assertSettingsDenied($panel);
        SettingsRolePermissionsStub::$roleIds = [5];

        foreach ($manager->permissions as $permission) {
            $permissions = $manager->permissions;
            $manager->permissions = array_diff($permissions, [$permission]);
            assertSettingsDenied($panel);
            $manager->permissions = $permissions;
        }

        $panel->boot();
        $modulePanel = new ModulePanel();
        $modulePanel->mount([['key' => 'translates'], ['key' => 'settings']], 'settings');
        slang_assert($modulePanel->activeTab === 'settings', 'The authorized manager can select settings.');

        foreach ($manager->permissions as $permission) {
            SettingsRolePermissionsStub::$grants[5] = array_diff($manager->permissions, [$permission]);
            assertSettingsDenied($panel);
            $modulePanel->switchTab('settings');
            slang_assert($modulePanel->activeTab === 'translates', 'Settings should be unavailable after revocation.');
            SettingsRolePermissionsStub::$grants[5] = $manager->permissions;
        }

        SettingsRolePermissionsStub::$roleIds = [5, 6];
        SettingsRolePermissionsStub::$grants = [5 => ['access_permissions', 'exec_module'], 6 => ['settings']];
        assertSettingsDenied($panel);
        SettingsRolePermissionsStub::$roleIds = [5];
        SettingsRolePermissionsStub::$grants = [5 => $manager->permissions];

        class_alias(SettingsModuleAccessStub::class, 'EvolutionCMS\Support\ModuleAccess');
        $moduleFile = dirname(__DIR__) . '/modules/sLangModule.php';
        $manager->modulesFromFile = [
            'module' => ['file' => $moduleFile, 'properties' => ['roles' => [6]]],
        ];
        assertSettingsDenied($panel);

        $manager->modulesFromFile['module']['properties']['roles'] = [5];
        $panel->boot();

        $manager->modulesFromFile = [];
        assertSettingsDenied($panel);
    });
});
