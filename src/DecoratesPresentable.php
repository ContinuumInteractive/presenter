<?php

namespace Continuum\Presenter;

use Continuum\Presenter\Presenter;

trait DecoratesPresentable
{
    /**
     * Decorate the presentable object.
     *
     * @return mixed
     */
    public function present($item)
    {
        return app(Presenter::class)->present($item);
    }
}
