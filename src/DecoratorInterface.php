<?php

namespace Continuum\Presenter;

interface DecoratorInterface
{
    /**
     * Decorate the item.
     *
     * @param  mixed  $item
     * @param  string $presenter
     * @return mixed
     */
    public function decorate($item, string $presenter);
}
