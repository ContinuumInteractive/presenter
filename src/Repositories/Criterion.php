<?php

namespace DBonner\Depot\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface Criterion
{
    /**
     * Apply criteria in a query repository.
     *
     * @param  $model
     * @return mixed
     */
    public function apply(Builder $builder);
}
