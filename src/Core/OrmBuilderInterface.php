<?php

namespace Gzhegow\Orm\Core;


/**
 * @mixin OrmBuilder
 */
interface OrmBuilderInterface
{
    public function make() : OrmInterface;
}
