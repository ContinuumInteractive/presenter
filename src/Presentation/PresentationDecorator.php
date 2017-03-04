<?php

namespace DBonner\Depot\Presentation;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\Paginator;

class PresentationDecorator
{
    /**
     * Array of registered decorator bindings.
     *
     * @var array
     */
    protected static $bindings = [];

    /**
     * Register a class and presenter with the decorator.
     *
     * @param string $class
     * @param string $presenter
     *
     * @return void
     */
    public static function setPresenter($class, $presenter)
    {
        static::$bindings[$class] = $presenter;
    }

    /**
     * Set the bindings on the decorator.
     *
     * @param array $bindings
     *
     * @return void
     */
    public static function registerPresenters(array $bindings)
    {
        static::$bindings = $bindings;
    }

    /**
     * Set the bindings on the decorator.
     *
     * @param array $bindings
     * @return void
     */
    public function getPresenter($binding)
    {
        if (!$this->hasPresenter($binding)) {
            return null;
        }

        return static::$bindings[$binding];
    }

    /**
     * Set the bindings on the decorator.
     *
     * @param array $bindings
     *
     * @return void
     */
    public static function hasPresenter($binding)
    {
        return (isset(static::$bindings[$binding]));
    }

    /**
     * Decorate the object or objects.
     *
     * @return mixed
     */
    public function decorate($object, $presenter = null)
    {
        if (is_array($object)) {
            $object = new Collection($object);
        }

        if ($object instanceof Collection || $object instanceof Paginator) {
            return $this->decorateCollection($object, $presenter);
        }

        if (!$presenter = $presenter ?: $this->getPresenter(get_class($object))) {
            return $object;
        }

        return $this->decorateObject($object, $presenter);
    }

    /**
     * Present a collection
     *
     * @return Illuminate\Support\Collection
     */
    protected function decorateCollection($collection, $presenter)
    {
        $collection->each(function ($item, $key) use ($presenter, $collection) {
            $collection->offsetSet($key, $this->decorate($item, $presenter));
        });

        return $collection;
    }

    /**
     * Decorate an Eloquent models relations.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    protected function decorateModelRelations(Model $model)
    {
        collect($model->getRelations())->each(function ($relation, $key) use ($model) {
            $model->setRelation($key, $this->decorate($relation));
        });

        return $model;
    }

    /**
     * Decorate a single object.
     *
     * @param  App\Utilities\Presentation\Presentable $object
     * @param  mixed                               $presenter
     * @return App\Utilities\Presentation\AbstractPresenter
     */
    protected function decorateObject(Presentable $object, $presenter)
    {
        if ($object instanceof Model) {
            $object = $this->decorateModelRelations($object);
        }

        return new $presenter($object);
    }

    /**
     * Attempt to decorate an object.
     *
     * @param mixed $object
     * @return mixed
     */
    public static function instance()
    {
        return (new static);
    }
}
