<?php

namespace Continuum\Presenter;

use ArrayAccess;
use Illuminate\Support\Collection;
use Continuum\Presenter\Presentable;
use Illuminate\Contracts\Pagination\Paginator;

class Presenter
{
    /**
     * @var array
     */
    protected $presentables = [];

    /**
     * @var Continuum\Presenter\Decorator
     */
    protected $decorator;

    /**
     * Register the presentable pairings.
     *
     * @param  Continuum\Presenter\Decorator  $decorator
     * @return void
     */
    public function registerDecorator(DecoratorInterface $decorator)
    {
        $this->decorator = $decorator;
    }

    /**
     * Register the presentable pairings.
     *
     * @param  array  $presentables
     * @return void
     */
    public function registerPresentables(array $presentables = [])
    {
        $this->presentables = $presentables;
    }

    /**
     * Is the item in question an instance of the presentable interface.
     *
     * @param  mixed $item
     * @return boolean
     */
    public function canDecorate($item): bool
    {
        return $item instanceof Presentable;
    }

    /**
     * Is there a registered presenter for the item.
     *
     * @param  mixed $item
     * @return boolean
     */
    public function shouldDecorate($class): bool
    {
        return array_key_exists($class, $this->presentables);
    }

    /**
     * Get the presenter from the presentables.
     *
     * @param  string $class
     * @return string
     */
    public function getPresenter($class): string
    {
        return $this->presentables[$class];
    }

    /**
     * Is this an array or arrayable object.
     *
     * @param  mixed  $item
     * @return boolean
     */
    protected function isArray($item): bool
    {
        return is_array($item) || $item instanceof Collection;
    }

    /**
     * Decorate the item if if is presentable.
     *
     * @param  mixed $item
     * @return mixed
     */
    public function present($item)
    {
        if ($this->isArray($item)) {
            return $this->decorateArray($item);
        }

        if ($this->canDecorate($item)) {
            if ($this->shouldDecorate($class = get_class($item))) {
                $item = $this->decorator->decorate($item, $this->getPresenter($class));
            }
        }

        return $item;
    }

    /**
     * Decorate an array.
     *
     * @param  mixed $item
     * @return mixed
     */
    protected function decorateArray($item)
    {
        foreach ($item as $key => $value) {
            $item[$key] = $this->present($value);
        }

        return $item;
    }
}
