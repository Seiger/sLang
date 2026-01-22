<?php namespace Seiger\sLang\Support;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;

class TreeCollection extends EloquentCollection
{
    public const CHILDREN_RELATION_NAME = 'children';

    public function toTree(): static
    {
        return $this->toTreeParent(null);
    }

    public function toTreeParent($parent = null): static
    {
        /** @var array<int|string, Model> $byId */
        $byId = [];
        $tops = [];

        foreach ($this->items as $item) {
            if ($item instanceof Model) {
                $item->setRelation(static::CHILDREN_RELATION_NAME, new static());
                $byId[$item->getKey()] = $item;
            }
        }

        foreach ($this->items as $item) {
            if (!$item instanceof Model) {
                continue;
            }

            $parentId = $item->getAttribute('parent_id');

            if ($parentId !== null && array_key_exists($parentId, $byId)) {
                /** @var Model $parentItem */
                $parentItem = $byId[$parentId];
                $parentItem->getRelation(static::CHILDREN_RELATION_NAME)->push($item);
                continue;
            }

            if ($parent === null || (string)$parentId === (string)$parent) {
                $tops[] = $item;
            }
        }

        return new static($tops);
    }
}
