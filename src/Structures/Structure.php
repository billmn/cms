<?php

namespace Statamic\Structures;

use Illuminate\Support\Traits\Tappable;
use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

abstract class Structure implements StructureContract
{
    use FluentlyGetsAndSets, Tappable;

    protected $title;
    protected $handle;
    protected $trees;
    protected $collection;
    protected $maxDepth;
    protected $expectsRoot = false;

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        if (func_num_args() === 0) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?: Str::humanize($this->handle());
            })->args(func_get_args());
    }

    public function expectsRoot($expectsRoot = null)
    {
        return $this->fluentlyGetOrSet('expectsRoot')->args(func_get_args());
    }

    public function trees()
    {
        return collect($this->trees);
    }

    public function makeTree($site)
    {
        return (new Tree)
            ->locale($site)
            ->structure($this);
    }

    public function addTree($tree)
    {
        $tree->structure($this);

        $this->trees[$tree->locale()] = $tree;

        return $this;
    }

    public function removeTree($tree)
    {
        unset($this->trees[$tree->locale()]);

        return $this;
    }

    public function existsIn($site)
    {
        return isset($this->trees[$site]);
    }

    public function in($site)
    {
        return $this->trees[$site] ?? null;
    }

    abstract public function collections($collections = null);

    public function maxDepth($maxDepth = null)
    {
        return $this
            ->fluentlyGetOrSet('maxDepth')
            ->setter(function ($maxDepth) {
                return (int) $maxDepth ?: null;
            })->args(func_get_args());
    }

    public function validateTree(array $tree, string $locale): array
    {
        if (! $this->expectsRoot()) {
            return $tree;
        }

        if (! empty($tree) && ! isset($tree[0]['entry'])) {
            throw new \Exception('Root page must be an entry');
        }

        throw_if(isset($tree[0]['children']), new \Exception('Root page cannot have children'));

        return $tree;
    }

    public function route(string $site): ?string
    {
        return null;
    }

    public function showUrl($params = [])
    {
        //
    }

    public function editUrl()
    {
        //
    }

    public function deleteUrl()
    {
        //
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Structure::{$method}(...$parameters);
    }
}
