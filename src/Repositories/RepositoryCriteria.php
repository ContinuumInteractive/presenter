<?php

namespace DBonner\Depot\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use DBonner\Depot\Repositories\Criterion;
use Illuminate\Database\Eloquent\Builder;

class RepositoryCriteria extends Collection
{
    /**
     * Boot the Criteria Collection.
     *
     * @param array $items
     * @param Illuminate\Http\Request $request
     */
    public function __construct(Request $request = null, $items = [])
    {
        parent::__construct($items);
        $this->bootCriteria($request);
    }

    /**
     * Apply the current collection to the query.
     *
     * @param  Illuminate\Database\Eloquent\QueryBuilder $query
     * @return Illuminate\Database\Eloquent\QueryBuilder
     */
    public function apply(Builder $query)
    {
        $this->each(function ($c) use ($query) {
            if (is_string($c)) {
                resolve($c)->apply($query);
            } elseif ($c instanceof Criterion) {
                $c->apply($query);
            } elseif (is_callable($c)) {
                $c($query);
            }
        });

        return $query;
    }

    /**
     * Boot the criteria.
     *
     * @param  Illuminate\Http\Request|null $request
     * @return void
     */
    public function bootCriteria(Request $request = null)
    {
        //
    }
}
