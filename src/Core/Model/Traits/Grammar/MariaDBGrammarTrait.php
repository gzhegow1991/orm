<?php

namespace Gzhegow\Database\Core\Model\Traits\Grammar;

use Gzhegow\Database\Core\Model\Scope\MariaDBGrammarScope;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


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
