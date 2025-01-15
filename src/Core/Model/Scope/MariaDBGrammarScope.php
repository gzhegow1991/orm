<?php

namespace Gzhegow\Orm\Core\Model\Scope;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model as EloquentModelBase;
use Illuminate\Database\Eloquent\Builder as EloquentModelQueryBuilderBase;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @mixin EloquentModelQueryBuilder
 */
class MariaDBGrammarScope implements Scope
{
    public function apply(
        EloquentModelQueryBuilderBase $builder,
        EloquentModelBase $model
    )
    {
        $queryStd = $this->getQuery();

        if ($queryStd->columns && ! $queryStd->orders) {
            // > gzhegow, MariaDB
            // > отдает всегда одно и то же, т.е. сортирует под капотом
            // > но всегда разное в зависимости от числа полей в SELECT
            // > select * from `w3j_user` limit 1; // = {id: 1}
            // > select `id` from `w3j_user` limit 1; // = {id: 6}
            // > select `id`, `uuid` from `w3j_user` limit 1; // = {id: 3}

            // > gzhegow, MariaDB & PostgreSQL
            $queryStd->orderBy($model->getKeyName(), 'ASC');
        }
    }
}
