<?php

namespace Gzhegow\Orm\Core\Model\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Gzhegow\Orm\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Gzhegow\Orm\Exception\Runtime\BadMethodCallException;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\Eloquent;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait AttributeTrait
{
    public function getAttribute($key)
    {
        /** @see HasAttributes::getAttribute() */

        $value = $this->getModelAttribute($key);

        return $value;
    }

    public function setAttribute($key, $value)
    {
        /** @see HasAttributes::setAttribute() */

        $this->setModelAttribute($key, $value);

        return $this;
    }


    public function getAttributeValue($key)
    {
        /** @see HasAttributes::getAttributeValue() */

        $value = $this->doGetAttributeValue($key);

        return $value;
    }

    private function doGetAttributeValue(string $key)
    {
        $value = $this->getAttributeFromArray($key);

        $value = $this->transformModelValue($key, $value);

        return $value;
    }


    public function isModelAttribute(string $key) : bool
    {
        if ('' === $key) {
            return false;
        }

        if ($this->isRelationAttribute($key)) {
            return false;
        }

        return true;
    }

    public function isModelAttributeValueExists(string $key) : bool
    {
        if (! $this->isModelAttribute($key)) {
            return false;
        }

        if (array_key_exists($key, $this->attributes)) {
            return true;
        }

        return false;
    }

    public function isModelAttributeGetterExists(string $key) : bool
    {
        if (! $this->isModelAttribute($key)) {
            return false;
        }

        if (
            array_key_exists($key, $this->casts)
            || $this->hasGetMutator($key)
            || $this->isClassCastable($key)
        ) {
            return true;
        }

        return false;
    }

    public function isModelAttributeValueOrGetterExists(string $key) : bool
    {
        if (! $this->isModelAttribute($key)) {
            return false;
        }

        if (array_key_exists($key, $this->attributes)) {
            return true;
        }

        if (
            array_key_exists($key, $this->casts)
            || $this->hasGetMutator($key)
            || $this->isClassCastable($key)
        ) {
            return true;
        }

        return false;
    }


    public function getModelAttribute(string $key)
    {
        if (! $this->isModelAttribute($key)) {
            return null;
        }

        if ($this->isModelAttributeValueOrGetterExists($key)) {
            $attributeValue = $this->getAttributeValue($key);

            return $attributeValue;
        }

        return null;
    }

    public function setModelAttribute(string $key, $value)
    {
        if (! $this->isModelAttribute($key)) {
            return $this;
        }

        parent::setAttribute($key, $value);

        return $this;
    }


    public function isRelationAttribute(string $key) : bool
    {
        return false
            || $this->isRelationAttributeEloquent($key)
            || $this->isRelationAttributeApplication($key);
    }

    protected function isRelationAttributeEloquent(string $key) : bool
    {
        if ('' === $key) {
            return false;
        }

        if ('pivot' === $key) {
            if (isset($this->relations[ 'pivot' ])) {
                return true;
            }
        }

        return false;
    }

    protected function isRelationAttributeApplication(string $key) : bool
    {
        if ('' === $key) {
            return false;
        }

        $relationPrefix = Eloquent::getRelationPrefix();

        if ('' !== $relationPrefix) {
            if (0 !== strpos($key, $relationPrefix)) {
                return false;
            }
        }

        if (isset(static::$cacheRelationClasses[ static::class ][ $key ])) {
            return true;
        }

        return false;
    }


    public function isRelationAttributeExists(string $key) : bool
    {
        if (! $this->isRelationAttribute($key)) {
            return false;
        }

        if ($this->relationLoaded($key)) {
            return true;
        }

        if (true !== $this->preventsLazyLoading) {
            return true;
        }

        return false;
    }

    public function getRelationAttribute(string $key)
    {
        if (! $this->isRelationAttribute($key)) {
            return null;
        }

        $value = $this->getRelationValue($key);

        return $value;
    }

    public function setRelationAttribute($key, $value)
    {
        if (! $this->isRelationAttribute($key)) {
            return $this;
        }

        $this->setRelation($key, $value);

        if ($relationship = $this->hasRelationshipOne($key)) {
            if (null === $value) {
                $relationship->dissociate();

            } else {
                $relationship->associate($value);
            }
        }

        return $this;
    }


    public function getRawAttributes() : array
    {
        return $this->attributes;
    }

    public function hasRawAttribute($key, &$result = null) : bool
    {
        $result = null;

        if (array_key_exists($key, $this->attributes)) {
            $result = $this->attributes[ $key ];

            return true;
        }

        return false;
    }

    public function getRawAttribute($key)
    {
        if (! $this->hasRawAttribute($key, $result)) {
            throw new RuntimeException(
                [ 'Missing attribute', $key ]
            );
        }

        return $result;
    }


    public function setRawAttribute($key, $value, $sync = false)
    {
        $this->attributes[ $key ] = $value;

        if ($sync) {
            $this->syncOriginal();
        }

        $this->classCastCache = [];
        $this->attributeCastCache = [];

        return $this;
    }

    public function setRawAttributes(array $attributes, $sync = false)
    {
        /** @see HasAttributes::setRawAttributes() */

        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        $this->classCastCache = [];
        $this->attributeCastCache = [];

        return $this;
    }


    /**
     * @return static
     */
    public function fillRawAttribute($key, $value, array $options = [])
    {
        $sync = $options[ 'sync' ] ?? false;
        $throw = $options[ 'throw' ] ?? true;

        if ($this->isFillable($key)) {
            $this->setRawAttribute($key, $value, $sync);
        }

        if ($throw) {
            throw new RuntimeException(
                [
                    'Attribute is not fillable: ' . $key,
                    $this,
                ]
            );
        }

        return $this;
    }

    /**
     * @return static
     */
    public function fillRawAttributes(array $attributes, array $options = [])
    {
        $sync = $options[ 'sync' ] ?? false;
        $throw = $options[ 'throw' ] ?? true;

        foreach ( $attributes as $attr => $value ) {
            if (! $this->isFillable($attr)) {
                if ($throw) {
                    throw new RuntimeException(
                        [
                            'Attribute is not fillable: ' . $attr,
                            $this,
                        ]
                    );
                }

                unset($attributes[ $attr ]);
            }
        }

        $this->setRawAttributes($attributes, $sync);

        return $this;
    }

    /**
     * @return static
     */
    public function fill(array $attributes)
    {
        /** @see parent::fill() */

        $this->fillRawAttributes($attributes, [ 'throw' => true ]);

        return $this;
    }


    /**
     * @see HasAttributes::hasCast()
     */
    public function hasCast($key, $types = null)
    {
        $casts = $this->getCasts();

        if (! isset($casts[ $key ])) {
            return false;
        }

        if (! $types) {
            return true;
        }

        $type = $this->getCastType($key);

        if (in_array($type, $types, true)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     * @see HasAttributes::getCasts()
     *
     */
    public function getCasts()
    {
        $casts = $this->casts;

        $isIncrementing = $this->getIncrementing();

        if ($isIncrementing) {
            array_unshift($casts, [ $this->getKeyName() => $this->getKeyType() ]);
        }

        return $casts;
    }

    /**
     * @see HasAttributes::castAttribute()
     */
    protected function castAttribute($key, $value)
    {
        $castType = $this->getCastType($key);

        if ($castType == 'custom_datetime') {
            [ $format ] = explode(':', $key, 2);

            $_value = $this->asDateTimeFormat($value, $format);

        } else {
            $_value = parent::castAttribute($key, $value);
        }

        return $_value;
    }

    /**
     * @see HasAttributes::addCastAttributesToArray()
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes)
    {
        foreach ( $this->getCasts() as $key => $cast ) {
            if (false
                || ! array_key_exists($key, $attributes)
                || in_array($key, $mutatedAttributes)
            ) {
                continue;
            }

            $attributes[ $key ] = $this->castAttribute(
                $key,
                $attributes[ $key ]
            );

            if ($attributes[ $key ]
                && (false
                    || $cast === 'date'
                    || $cast === 'datetime'
                )
            ) {
                $attributes[ $key ] = $this->serializeDate($attributes[ $key ]);
            }

            if ($attributes[ $key ]
                && $this->isCustomDateTimeCast($cast)
            ) {
                $attributes[ $key ] = $this->serializeDate($attributes[ $key ]);
            }

            if ($attributes[ $key ] instanceof \DateTimeInterface
                && $this->isClassCastable($key)
            ) {
                $attributes[ $key ] = $this->serializeDate($attributes[ $key ]);
            }

            if ($attributes[ $key ] instanceof Arrayable) {
                $attributes[ $key ] = $attributes[ $key ]->toArray();
            }
        }

        return $attributes;
    }


    public function getHidden()
    {
        /** @see HidesAttributes::getHidden() */

        if (! $this->hiddenLoaded && (! count($this->hidden))) {
            $this->hidden = array_keys($this->relations);
        }

        $this->hiddenLoaded = true;

        return $this->hidden;
    }

    public function getVisible()
    {
        /** @see HidesAttributes::getVisible() */

        if (! $this->visibleLoaded && (! count($this->visible))) {
            $this->visible = array_keys($this->getAttributes());
        }

        $this->visibleLoaded = true;

        return $this->visible;
    }


    public function makeVisible($attributes)
    {
        /** @see HidesAttributes::makeVisible() */

        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $this->visible = array_merge($this->getVisible(), $attributes);

        $this->hidden = array_diff($this->getHidden(), $this->visible);

        return $this;
    }

    public function makeHidden($attributes)
    {
        /** @see HidesAttributes::makeHidden() */

        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $this->hidden = array_merge($this->getHidden(), $attributes);

        $this->visible = array_diff($this->getVisible(), $this->hidden);

        return $this;
    }


    public function makeVisibleIf($condition, $attributes)
    {
        /** @see HidesAttributes::makeVisibleIf() */

        $condition = $condition instanceof \Closure
            ? $condition($this)
            : $condition;

        return value($condition)
            ? $this->makeVisible($attributes)
            : $this;
    }

    public function makeHiddenIf($condition, $attributes)
    {
        /** @see HidesAttributes::makeVisibleIf() */

        $condition = $condition instanceof \Closure
            ? $condition($this)
            : $condition;

        return value($condition)
            ? $this->makeHidden($attributes)
            : $this;
    }


    public function getFillable()
    {
        /** @see GuardsAttributes::getFillable() */

        return [];
    }

    public function isFillable($key)
    {
        /** @see GuardsAttributes::isFillable() */

        if ($this->isGuarded($key)) {
            return false;
        }

        return true;
    }

    public function fillable(array $fillable)
    {
        /** @see GuardsAttributes::fillable() */

        throw new BadMethodCallException('Please, don`t use dynamic guards, define guarded field in model class source code');
        // return $this;
    }

    public function mergeFillable(array $fillable)
    {
        /** @see GuardsAttributes::mergeFillable() */

        throw new BadMethodCallException('Please, don`t use dynamic guards, define guarded field in model class source code');
        // return $this;
    }

    protected function fillableFromArray(array $attributes)
    {
        /** @see GuardsAttributes::fillableFromArray() */

        while ( null !== ($key = key($attributes)) ) {
            if (! $this->isFillable($key)) {
                unset($attributes[ $key ]);
            }

            next($attributes);
        }

        return $attributes;
    }


    protected function isGuardableColumn($key)
    {
        /** @see GuardsAttributes::isGuardableColumn() */

        return false;
    }

    public function getGuarded()
    {
        /** @see GuardsAttributes::getGuarded() */

        $result = []
            + [ 'id' => true ]
            + [ 'uuid' => true ]
            + [ $this->getKeyName() => true ]
            + $this->guarded;

        if (! is_array($result)) {
            throw new RuntimeException(
                [
                    'The `guarded` property should be array',
                    $result,
                ]
            );
        }

        return $result;
    }

    public function isGuarded($key)
    {
        /** @see GuardsAttributes::isGuarded() */

        $guarded = $this->getGuarded();

        if (isset($guarded[ $key ])) {
            return true;
        }

        return false;
    }

    public function totallyGuarded()
    {
        /** @see GuardsAttributes::totallyGuarded() */

        return false;
    }

    public function guard(array $guarded)
    {
        /** @see GuardsAttributes::guard() */

        throw new BadMethodCallException('Please, don`t use dynamic guards, define guarded field in model class source code');
        // return $this;
    }

    public function mergeGuarded(array $guarded)
    {
        /** > gzhegow, method replacement made due to no-use-case and increase performance */
        /** @see GuardsAttributes::mergeGuarded() */

        throw new BadMethodCallException('Please, don`t use dynamic guards, define guarded field in model class source code');
        // return $this;
    }
}
