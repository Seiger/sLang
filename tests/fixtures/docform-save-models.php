<?php

declare(strict_types=1);

final class SlangDocFormSaveSiteContentStub
{
    private ?int $updateId = null;

    public function __construct(
        public int $id = 0,
        public int $template = 10,
        public int $privatemgr = 0,
    ) {
    }

    public static function withTrashed(): self
    {
        return new self();
    }

    public function find(int $id, array $columns): ?self
    {
        return SlangDocFormSaveStub::$resources[$id] ?? null;
    }

    public function whereKey(int $id): self
    {
        $this->updateId = $id;

        return $this;
    }

    public function toBase(): self
    {
        return $this;
    }

    public function update(array $data): int
    {
        slang_assert($this->updateId !== null, 'A site_content update must be constrained by a resource ID.');
        SlangDocFormSaveStub::$siteContentUpdates[] = ['data' => $data, 'id' => $this->updateId];

        return 1;
    }
}

final class SlangDocFormSaveDocumentGroupsStub
{
    private int $id;
    private ?array $groups = null;

    public static function query(): self
    {
        return new self();
    }

    public function where(string $column, int $id): self
    {
        if ($column === 'document_groups.document') {
            $this->id = $id;
        } else {
            slang_assert($column === 'membergroup_access.context' && $id === 0, 'Use resource groups for the manager context.');
        }

        return $this;
    }

    public function join(string $table, string $left, string $operator, string $right): self
    {
        slang_assert($table === 'membergroup_access', 'Check whether the resource groups restrict manager access.');

        return $this;
    }

    public function whereIn(string $column, array $groups): self
    {
        slang_assert($column === 'document_group', 'Compare the manager groups to the resource groups.');
        $this->groups = $groups;

        return $this;
    }

    public function exists(): bool
    {
        $resourceGroups = SlangDocFormSaveStub::$resourceGroups[$this->id] ?? [];

        return $this->groups === null ? $resourceGroups !== [] : (bool) array_intersect($this->groups, $resourceGroups);
    }
}

final class SlangDocFormSaveTranslatedContentStub
{
    public static function withoutGlobalScope(string $scope): self
    {
        return new self();
    }

    public function updateOrCreate(array $identity, array $fields): void
    {
        SlangDocFormSaveStub::$langContent[] = [
            'id' => $identity['resource'], 'lang' => $identity['lang'], 'fields' => $fields,
        ];
    }
}

final class SlangDocFormSaveTvQueryStub
{
    public function __construct(private array $tvs)
    {
    }

    public static function query(): self
    {
        return new self(SlangDocFormSaveStub::$tvs);
    }

    public function whereKey(int|array $ids): self
    {
        $this->tvs = array_intersect_key($this->tvs, array_flip((array) $ids));

        return $this;
    }

    public function whereHas(string $relation, Closure $filter): self
    {
        slang_assert($relation === 'tmplvarTemplate', 'Check the TV assignment to the saved template.');
        $condition = new class {
            public int $template;

            public function where(string $column, int $template): void
            {
                slang_assert($column === 'templateid', 'Use the saved resource template ID.');
                $this->template = $template;
            }
        };
        $filter($condition);
        $this->tvs = array_filter($this->tvs, static fn (array $tv): bool => in_array($condition->template, $tv['templates'], true));

        return $this;
    }

    public function where(string|Closure $column, ?string $value = null): self
    {
        if (is_string($column)) {
            slang_assert($column === 'name', 'Find a menu TV by its fixed name.');
            $this->tvs = array_filter($this->tvs, static fn (array $tv): bool => ($tv['name'] ?? null) === $value);
            return $this;
        }

        $condition = new SlangDocFormSaveTvGroupsStub();
        $column($condition);
        $this->tvs = array_filter($this->tvs, static fn (array $tv): bool =>
            ($tv['groups'] === [] && $condition->allowsUnrestricted) || array_intersect($tv['groups'], $condition->groups)
        );

        return $this;
    }

    public function first(): ?object
    {
        $id = array_key_first($this->tvs);

        return $id === null ? null : (object) ['id' => $id];
    }

    public function exists(): bool
    {
        return $this->tvs !== [];
    }

    public function pluck(string $column): object
    {
        slang_assert($column === 'site_tmplvars.id', 'Return only IDs of permitted TVs.');

        return new class(array_keys($this->tvs)) {
            public function __construct(private array $ids)
            {
            }

            public function all(): array
            {
                return $this->ids;
            }
        };
    }
}

final class SlangDocFormSaveTvGroupsStub
{
    public bool $allowsUnrestricted = false;
    public array $groups = [];

    public function whereDoesntHave(string $relation): self
    {
        slang_assert($relation === 'tmplvarAccess', 'Allow TVs without their own group restrictions.');
        $this->allowsUnrestricted = true;

        return $this;
    }

    public function orWhereHas(string $relation, Closure $filter): void
    {
        slang_assert($relation === 'tmplvarAccess', 'Use the TV groups rather than the resource groups.');
        $filter($this);
    }

    public function whereIn(string $column, array $groups): void
    {
        slang_assert($column === 'documentgroup', 'Match the TV group to the manager groups.');
        $this->groups = $groups;
    }
}

final class SlangDocFormSaveTranslatedTvStub
{
    public static function updateOrCreate(array $identity, array $data): void
    {
        SlangDocFormSaveStub::$tvTranslations[] = $identity + $data;
    }
}

final class SlangDocFormSaveNativeTvStub
{
    public static function query(): self
    {
        return new self();
    }

    public function updateOrCreate(array $identity, array $data): void
    {
        SlangDocFormSaveStub::$nativeTvWrites[] = $identity + $data;
    }
}

class_alias(SlangDocFormSaveSiteContentStub::class, 'EvolutionCMS\Models\SiteContent');
class_alias(SlangDocFormSaveDocumentGroupsStub::class, 'EvolutionCMS\Models\DocumentGroup');
class_alias(SlangDocFormSaveTvQueryStub::class, 'EvolutionCMS\Models\SiteTmplvar');
class_alias(SlangDocFormSaveNativeTvStub::class, 'EvolutionCMS\Models\SiteTmplvarContentvalue');
class_alias(SlangDocFormSaveTranslatedContentStub::class, 'Seiger\sLang\Models\sLangContent');
class_alias(SlangDocFormSaveTranslatedTvStub::class, 'Seiger\sLang\Models\sLangTmplvarContentvalue');
