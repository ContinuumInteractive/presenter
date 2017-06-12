<?php

namespace Continuum\Presenter;

use ArrayAccess;
use BadMethodCallException;
use Continuum\Presenter\Presentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class BasePresenter implements ArrayAccess, Arrayable, Jsonable
{
    /**
     * The object being decorated.
     *
     * @var Continuum\Presenter\Presentable
     */
    protected $object;

    /**
     * Return the raw presentable object.
     *
     * @param Continuum\Presenter\Presentable $object
     * @return Continuum\Presenter\BasePresenter
     */
    public function setPresentableObject(Presentable $object)
    {
        $this->object = $object;
        $this->bootPresenter();

        return $this;
    }

    /**
     * Boot the presenter.
     *
     * @return void
     */
    protected function bootPresenter()
    {
        //
    }

    /**
     * Get an attribute from the wrapped object.
     *
     * @param string $key
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

        if (method_exists($this, $method = camel_case($key))) {
            return $this->{$method}();
        }

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
        if (property_exists($this, $method) && is_object($this->{$method}) && count($parameters) === 1) {
            return $this->{$method}->{$parameters[0]};
        }

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
    public function toArray(): array
    {
        return ($this->object instanceof Arrayable) ? $this->object->toArray() : [];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
