<?php

namespace DBonner\Depot\Presentation;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use DBonner\Depot\Presentation\DecoratorException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait DecoratesEntities
{
    /**
     * @var boolean
     */
    protected $disablePresenters = false;

    /**
     * Return a decorated collection or entity.
     *
     * @return mixed
     */
    public function decorateObject($entity, $decorator = null)
    {
        if ($this->disablePresenters) {
            return $entity;
        }

        $decorated = PresentationDecorator::instance()->decorate($entity, $decorator);

        $this->enablePresenter();

        return $decorated;
    }

    /**
     * Skip the decorator.
     *
     * @return mixed
     */
    public function disablePresenter()
    {
        $this->disablePresenters = true;
        return $this;
    }

    /**
     * Enable the decorator.
     *
     * @return mixed
     */
    public function enablePresenter()
    {
        $this->disablePresenters = false;
        return $this;
    }
}
