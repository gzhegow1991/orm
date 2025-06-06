<?php

namespace Gzhegow\Orm\Core;

use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Orm\Core\Persistence\EloquentPersistence;
use Illuminate\Container\Container as IlluminateContainer;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\Eloquent;
use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Illuminate\Database\Schema\Builder as IlluminateSchemaBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\EloquentInterface;
use Illuminate\Contracts\Events\Dispatcher as IlluminateEventDispatcher;


class OrmBuilder implements OrmBuilderInterface
{
    /**
     * @var OrmFactoryInterface
     */
    protected $factory;

    /**
     * @var IlluminateContainer
     */
    protected $illuminateContainer;
    /**
     * @var IlluminateEventDispatcher
     */
    protected $illuminateEventDispatcher;

    /**
     * @var EloquentInterface
     */
    protected $eloquent;
    /**
     * @var EloquentPersistenceInterface
     */
    protected $persistence;

    /**
     * @var int
     */
    protected $defaultStringLength;

    /**
     * @var \Closure
     */
    protected $fnLog;

    /**
     * @var \Closure
     */
    protected $fnInit;
    /**
     * @var \Closure
     */
    protected $fnBoot;


    public function __construct(OrmFactoryInterface $factory)
    {
        $this->factory = $factory;
    }


    /**
     * @return static
     */
    public function setIlluminateContainer(?IlluminateContainer $container)
    {
        $this->illuminateContainer = $container;

        return $this;
    }

    /**
     * @return static
     */
    public function setIlluminateEventDispatcher(?IlluminateEventDispatcher $dispatcher)
    {
        $this->illuminateEventDispatcher = $dispatcher;

        return $this;
    }


    /**
     * @return static
     */
    public function setEloquent(?EloquentInterface $eloquent)
    {
        $this->eloquent = $eloquent;

        return $this;
    }

    /**
     * @return static
     */
    public function setPersistence(?EloquentPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;

        return $this;
    }


    /**
     * @return static
     */
    public function defaultStringLength(?int $length)
    {
        $this->defaultStringLength = $length;

        return $this;
    }


    /**
     * @return static
     */
    public function fnInit(\Closure $fn)
    {
        $this->fnInit = $fn;

        return $this;
    }

    /**
     * @return static
     */
    public function fnBoot(\Closure $fn)
    {
        $this->fnBoot = $fn;

        return $this;
    }

    /**
     * @return static
     */
    public function fnLog(\Closure $fn)
    {
        $this->fnLog = $fn;

        return $this;
    }


    public function make() : OrmInterface
    {
        $hasIlluminateEventDispatcher = (null !== $this->illuminateEventDispatcher);
        $hasFnLog = (null !== $this->fnLog);

        if (null === $this->eloquent) {
            $illuminateContainer = $this->illuminateContainer ?? null;

            $this->eloquent = new Eloquent($illuminateContainer);
        }

        if (null === $this->persistence) {
            $this->persistence = new EloquentPersistence($this->eloquent);
        }

        if (false
            || $hasIlluminateEventDispatcher
            || $hasFnLog
        ) {
            if (! $hasIlluminateEventDispatcher) {
                $commands = [
                    'composer require illuminate/events',
                ];

                if (! class_exists($eventsDispatcherClass = '\Illuminate\Events\Dispatcher')) {
                    throw new ComposerException([
                        ''
                        . 'Please, run following commands: '
                        . '[ ' . implode(' ][ ', $commands) . ' ]',
                    ]);
                }

                $this->illuminateEventDispatcher = new $eventsDispatcherClass();
            }

            $this->eloquent->setEventDispatcher($this->illuminateEventDispatcher);
        }

        if (null !== $this->fnInit) {
            call_user_func_array($this->fnInit, [ $this->eloquent ]);
        }

        if (null !== $this->defaultStringLength) {
            IlluminateSchemaBuilder::$defaultStringLength = $this->defaultStringLength;
        }

        $this->eloquent->bootEloquent();

        if (null !== $this->fnBoot) {
            call_user_func_array($this->fnBoot, [ $this->eloquent ]);
        }

        if ($hasFnLog) {
            $connection = $this->eloquent->getConnection();
            $connection->enableQueryLog();
            $connection->listen($this->fnLog);
        }

        $facade = new OrmFacade(
            $this->factory,
            $this->eloquent,
            $this->persistence
        );

        return $facade;
    }
}
