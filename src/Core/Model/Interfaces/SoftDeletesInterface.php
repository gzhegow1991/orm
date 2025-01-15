<?php

namespace Gzhegow\Orm\Core\Model\Interfaces;

interface SoftDeletesInterface
{
    /**
     * @return void
     */
    public static function bootSoftDeletes();

    /**
     * @return void
     */
    public function initializeSoftDeletes();


    /**
     * @return bool|null
     */
    public function forceDelete();

    /**
     * @return bool|null
     */
    public function restore();


    /**
     * @return bool
     */
    public function trashed();


    /**
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function softDeleted($callback);

    /**
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function restoring($callback);

    /**
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function restored($callback);

    /**
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function forceDeleted($callback);


    /**
     * @return bool
     */
    public function isForceDeleting();


    /**
     * @return string
     */
    public function getDeletedAtColumn();

    /**
     * @return string
     */
    public function getQualifiedDeletedAtColumn();
}
