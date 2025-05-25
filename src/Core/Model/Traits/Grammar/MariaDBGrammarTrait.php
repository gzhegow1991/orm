<?php

namespace Gzhegow\Orm\Core\Model\Traits\Grammar;

use Gzhegow\Orm\Core\Model\Scope\MariaDBGrammarScope;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait MariaDBGrammarTrait
{
    public function initializeMariaDBGrammarTrait()
    {
        static::$globalScopes[ MariaDBGrammarScope::class ] = new MariaDBGrammarScope();
    }
}
