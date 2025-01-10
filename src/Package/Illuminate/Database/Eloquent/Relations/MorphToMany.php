<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations;

use Gzhegow\Database\Core\Orm;
use Gzhegow\Database\Core\Relation\Traits\HasRelationNameTrait;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Illuminate\Database\Eloquent\Relations\MorphToMany as MorphToManyBase;


class MorphToMany extends MorphToManyBase implements
    RelationInterface
{
    use HasRelationNameTrait;


    public function addConstraints()
    {
        /** @see parent::addConstraints() */

        $this->performJoin();

        if (static::$constraints) {
            $this->addWhereConstraints();
        }
    }

    protected function addWhereConstraints()
    {
        /** @see parent::addWhereConstraints() */

        $query = $this->query;

        $query->where(
            $this->getQualifiedForeignPivotKeyName(),
            '=',
            $this->parent->getAttribute($this->parentKey)
        );

        $query->where(
            $this->table . '.' . $this->morphType,
            $this->morphClass
        );

        return $this;
    }


    public function sync($ids, $detaching = true)
    {
        /**
         * @see parent::sync()
         * @see https://github.com/illuminate/database/blob/11.x/Eloquent/Relations/Concerns/InteractsWithPivotTable.php
         */

        $changes = [
            'attached' => [],
            'detached' => [],
            'updated'  => [],
        ];

        // First we need to attach any of the associated models that are not currently
        // in this joining table. We'll spin through the given IDs, checking to see
        // if they exist in the array of current ones, and if not we will insert.
        $current = $this->getCurrentlyAttachedPivots()
            ->pluck($this->relatedPivotKey)
            ->all()
        ;

        $records = $this->formatRecordsList($this->parseIds($ids));

        // Next, we will take the differences of the currents and given IDs and detach
        // all of the entities that exist in the "current" array but are not in the
        // array of the new IDs given to the method which will complete the sync.
        if ($detaching) {
            $detach = array_diff($current, array_keys($records));

            if (count($detach) > 0) {
                $this->detach($detach, false);

                $changes[ 'detached' ] = $this->castKeys($detach);
            }
        }

        // Now we are finally ready to attach the new records. Note that we'll disable
        // touching until after the entire operation is complete so we don't fire a
        // ton of touch operations until we are totally done syncing the records.
        $changes = array_merge(
            $changes,
            $this->attachNew($records, $current, false)
        );

        // Once we have finished attaching or detaching the records, we will see if we
        // have done any attaching or detaching, and if we have we will touch these
        // relationships if they are configured to touch on any database updates.
        if (false
            || count($changes[ 'attached' ])
            || count($changes[ 'updated' ])
            || count($changes[ 'detached' ])
        ) {
            $this->touchIfTouching();
        }

        return $changes;
    }


    /**
     * @return static
     */
    public function persistForSave(EloquentModel $model, array $pivotAttributes = [], $touch = null)
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistBelongsToManyForSave($this, $model, $pivotAttributes, $touch);

        return $this;
    }

    /**
     * @return static
     */
    public function persistForSaveMany($models, array $pivotAttributes = [])
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistBelongsToManyForSaveMany($this, $models, $pivotAttributes);

        return $this;
    }

    /**
     * @return static
     */
    public function persistForSync($ids, $detaching = null)
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistBelongsToManyForSync($this, $ids, $detaching);

        return $this;
    }
}
