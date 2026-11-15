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

final class SettingsRoleQueryStub
{
    public static ?object $role = null;

    public static function query(): self
    {
        return new self();
    }

    public function select(string $column): self
    {
        slang_assert($column === 'role', 'Use the role currently assigned to the manager.');

        return $this;
    }

    public function where(string $column, int $id): self
    {
        slang_assert($column === 'internalKey' && $id === evo()->userId, 'Use the signed-in manager ID.');

        return $this;
    }

    public function whereIn(string $column, self $query): self
    {
        slang_assert($column === 'id', 'Look up the assigned role by ID.');

        return $this;
    }

    public function first(array $columns): ?object
    {
        return self::$role;
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

function abort(int $status): never
{
    throw new \RuntimeException('Denied', $status);
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

class_alias(SettingsRoleQueryStub::class, 'EvolutionCMS\Models\UserAttribute');
class_alias(SettingsRoleQueryStub::class, 'EvolutionCMS\Models\UserRole');
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
        assertSettingsDenied($panel);

        $manager->userId = 42;
        $role = new class {
            public int $access_permissions = 1;
            public int $exec_module = 1;
            public int $settings = 1;

            public function getKey(): int
            {
                return 5;
            }
        };
        SettingsRoleQueryStub::$role = $role;

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
            $role->{$permission} = 0;
            assertSettingsDenied($panel);
            $modulePanel->switchTab('settings');
            slang_assert($modulePanel->activeTab === 'translates', 'Settings should be unavailable after revocation.');
            $role->{$permission} = 1;
        }

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
