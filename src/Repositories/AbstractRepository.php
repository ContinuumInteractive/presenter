<?php

namespace DBonner\Depot\Repositories;

use Illuminate\Database\Eloquent\Builder;
use DBonner\Depot\Presentation\DecoratesEntities;
use DBonner\Depot\Repositories\RepositoryCriteria;

class AbstractRepository
{
    use DecoratesEntities;

    /**
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $criteria = [];

    /**
     * @var integer
     */
    protected $count = 15;

    /**
     * Search a model by Criteria and paginate
     *
     * @param  App\Utilities\Repositories\RepositoryCriteria|null $criteria
     * @return mixed
     */
    protected function paginateQuery(Builder $query, RepositoryCriteria $criteria = null, $count = null)
    {
        if ($criteria) {
            $query = $this->applyCriteria($query, $criteria);
        }

        return $query->paginate($count ?: $this->count);
    }

    /**
     * Run a criteria collection against the query.
     *
     * @param  Illuminate\Database\Query\Builder             $builder
     * @param  App\Utilities\Repositories\RepositoryCriteria $criteria
     * @return Illuminate\Database\Eloquent\QueryBuilder
     */
    protected function applyCriteria(Builder $query, RepositoryCriteria $criteria)
    {
        return $criteria->apply($query);
    }

    /**
     * Return the current query instance.
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        return $this->model->query();
    }
}
