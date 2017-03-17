<?php

namespace DBonner\Depot\Presentation;

use ArrayAccess;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use DBonner\Depot\Presentation\Presentable;
use Illuminate\Contracts\Support\Arrayable;
use DBonner\Depot\Presentation\PresenterException;

/**
 * The AbstractPresenter is influenced by https://github.com/jasonlewis/presenter.
 *
 * Unfortunately the package above didn't meet our exact needs so a new fork
 * was required.
 */

abstract class AbstractPresenter implements ArrayAccess, Arrayable
{
    /**
     * The object being decorated.
     *
     * @var object
     */
    protected $object;

    /**
     * Create a new presenter instance.
     *
     * @param object $object
     *
     * @return void
     */
    public function __construct(Presentable $object)
    {
        $this->object = $object;
        $this->bootPresenter();
    }

    /**
     * Boot the presenter.
     *
     * @return void
     */
    protected function bootPresenter()
    {
        if ($this->object instanceof Model) {
            collect($this->object->getRelations())->filter(function ($relation) {
                return ($relation instanceof self);
            })->each(function ($relation, $key) {
                $this->{$key} = $relation;
            });
        }

        // Override this method within your presenter.
    }

    /**
     * Get an attribute from the wrapped object.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getObjectAttribute($key)
    {
        if (is_array($this->object)) {
            return $this->object[$key];
        }

        return $this->object->{$key};
    }

    /**
     * Dynamically call a method on the presenter or get an attribute from
     * the wrapped object.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (method_exists($this, $key)) {
            return $this->{$key}();
        }

        $method = camel_case($key);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        // if (isset($this->))

        return $this->getObjectAttribute($key);
    }

    /**
     * Dynamically check if the attribute is set on the object.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        if (method_exists($this, $key) || method_exists($this, camel_case($key))) {
            return !is_null($this->__get($key));
        } elseif (is_array($this->object)) {
            return isset($this->object[$key]);
        }

        return isset($this->object->{$key});
    }

    /**
     * Dynamically call a method on the wrapped object.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        if (is_object($this->object) && method_exists($this->object, $method)) {
            return call_user_func_array([$this->object, $method], $parameters);
        }

        throw new BadMethodCallException('Method '.$method.' not found on AbstractPresenter.');
    }

    /**
     * Determine if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get an offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set an offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * Unset an offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->{$offset});
        }
    }

    /**
     * Return an array representation of the presenter.
     *
     * @return array
     */
    public function toArray()
    {
        if (method_exists($this, 'presentArray')) {
            $array = $this->presentArray();
        } elseif ($this->object instanceof Arrayable) {
            $array = $this->object->toArray();
        } else {
            $array = collect($this->object->getPresentableKeys())->mapWithKeys(function ($keyName, $key) {
                return [$keyName => $this->object->{$keyName}];
            })->toArray();
        }

        return $array;
    }

    /**
     * Is the decorated entity available.
     *
     * When dealing with relationships in models, we only want to deal with their
     * presented objects within other presenters.
     *
     * @param  string $key
     * @return boolean
     */
    protected function decoratedRelationLoaded($key)
    {
        if ($this->object->relationLoaded($key)) {
            return ($this->object->getRelation($key) instanceof self);
        }

        return false;
    }
}
