<?php

namespace Continuum\Presenter;

use Continuum\Presenter\Presenter;
use Continuum\Presenter\BasePresenter;
use Illuminate\Database\Eloquent\Model;
use Continuum\Presenter\DecoratorInterface;
use Illuminate\Contracts\Container\Container;

class Decorator implements DecoratorInterface
{
    /**
     * @var Continuum\Presenter\Presenter
     */
    protected $presenter;

    /**
     * @param mixed $object
     */
    public function __construct(Presenter $presenter, Container $container)
    {
        $this->container = $container;
        $this->presenter = $presenter;
    }

    /**
     * Decorate the item.
     *
     * @param  mixed  $item
     * @param  string $presenter
     * @return Continuum\Presenter\BasePresenter
     */
    public function decorate($item, string $presenter): BasePresenter
    {
        if (is_object($item)) {
            $item = clone $item;
        }

        if ($item instanceof Model) {
            collect($item->getRelations())->each(function ($relation, $key) use ($item) {
                $item->setRelation($key, $this->presenter->present($relation));
            });
        }

        return $this->container->make($presenter)->setPresentableObject($item);
    }
}
