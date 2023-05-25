<?php

namespace Momenoor\FormBuilder\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionException;

trait HasFieldRelationship
{
    /**
     * Checks the properties of the provided method to better verify if it could be a relation.
     * Case the method is not public, is not a relation.
     * Case the return type is Attribute, or extends Attribute is not a relation method.
     * If the return type extends, the Relation class is for sure a relation;
     * Otherwise we just assume it's a relation.
     *
     * DEV NOTE: In future versions we will return `false` when no return type is set and make the return type mandatory for relationships.
     *           This function should be refactored to only check if $returnType is a subclass of Illuminate\Database\Eloquent\Relations\Relation.
     *
     * @param $model
     * @param $method
     * @return bool|string
     * @throws ReflectionException
     */
    private function modelMethodIsRelationship($model, $method): bool|string
    {
        $methodReflection = new \ReflectionMethod($model, $method);

        // relationship methods function does not have parameters
        if ($methodReflection->getNumberOfParameters() > 0) {
            return false;
        }

        // relationships are always public methods.
        if (!$methodReflection->isPublic()) {
            return false;
        }

        $returnType = $methodReflection->getReturnType();

        if ($returnType) {
            $returnType = $returnType->getName();

            if (is_a($returnType, 'Illuminate\Database\Eloquent\Casts\Attribute', true)) {
                return false;
            }

            if (is_a($returnType, 'Illuminate\Database\Eloquent\Relations\Relation', true)) {
                return $method;
            }
        }

        return $method;
    }

    private function maskSureFieldHasRelationshipAttributes(): void
    {

        $this->makeSureFieldHasRelationType();
        $this->makeSureFieldHasModel();
        $this->makeSureFieldHasAttribute();
        $this->makeSureFieldHasMultiple();
        $this->makeSureFieldHasPivot();
        $this->makeSureFieldHasType();

    }

    private function makeSureFieldHasRelationType(): void
    {
        if (!$this->hasAttribute('relation_type')) {
            $this->setAttribute('relation_type', $this->inferRelationTypeFromRelationship());
        }
    }

    private function makeSureFieldHasModel(): void
    {
        if (!$this->hasAttribute('own_model')) {
            $this->setAttribute('own_model', new ($this->inferFieldModelFromRelationship()));
        }

    }

    private function inferFieldModelFromRelationship(): string
    {
        $relation = $this->getRelationInstance();

        return get_class($relation->getRelated());
    }

    private function inferRelationTypeFromRelationship()
    {
        $relation = $this->getRelationInstance();

        return Arr::last(explode('\\', get_class($relation)));
    }

    private function getRelationInstance()
    {
        $entity = $this->getOnlyRelationEntity();
        $possible_method = Str::before($entity, '.');
        $model = $this->getModel();

        if (method_exists($model, $possible_method)) {
            $parts = explode('.', $entity);
            $relation = '';
            // here we are going to iterate through all relation parts to check
            foreach ($parts as $i => $part) {
                $relation = $model->$part();
                $model = $relation->getRelated();
            }

            return $relation;
        }

        abort(500, 'Looks like field <code>' . $this->getName() . '</code> is not properly defined. The <code>' . $this->getAttribute('entity') . '()</code> relationship doesn\'t seem to exist on the <code>' . get_class($model) . '</code> model.');
    }

    private function getOnlyRelationEntity()
    {
        $entity = $this->hasAttribute('own_entity') ? $this->getAttribute('own_entity') . '.' . $this->getAttribute('entity') : $this->getAttribute('entity');
        $model = $this->getRelationModel($entity, -1);
        $lastSegmentAfterDot = Str::of($this->getAttribute('entity'))->afterLast('.');

        if (!method_exists($model, $lastSegmentAfterDot)) {
            return (string)Str::of($this->getAttribute('entity'))->beforeLast('.');
        }

        return $this->getAttribute('entity');
    }

    private function getRelationModel($relationString, $length = null, $model = null): string
    {
        $relationArray = explode('.', $relationString);

        if (empty($length)) {
            $length = count($relationArray);
        }

        if (empty($model)) {
            $model = $this->getModel();
        }

        $result = array_reduce(array_splice($relationArray, 0, $length), function ($obj, $method) {
            try {
                $result = $obj->$method();

                return $result->getRelated();
            } catch (Exception $e) {
                return $obj;
            }
        }, $model);

        return get_class($result);
    }

    private function makeSureFieldHasAttribute(): void
    {
        if ($this->hasAttribute('entity')) {
            // if the user set up the attribute in relation string, we are not going to infer that attribute from model
            // instead we get the defined attribute by the user.
            if ($this->isAttributeInRelationString()) {
                if (!$this->hasAttribute('attribute')) {
                    $this->setAttribute('attribute', Str::afterLast($this->getAttribute('entity'), '.'));
                }
            }
        }
        // if there's a model defined, but no attribute
        // guess an attribute using the identifiableAttribute functionality in CrudTrait
        if (($this->hasAttribute('own_model')) && !$this->hasAttribute('attribute') && method_exists($this->getAttribute('own_model'), 'identifiableAttribute')) {
            $this->setAttribute('attribute', app($this->getAttribute('own_model'))->identifiableAttribute());
        }

    }

    private function isAttributeInRelationString(): bool
    {
        $entity = $this->getAttribute('entity');
        if (!str_contains($entity, '.')) {
            return false;
        }

        $parts = explode('.', $entity);

        $model = $this->getModel();

        // here we are going to iterate through all relation parts to check
        // if the attribute is present in the relation string.
        foreach ($parts as $i => $part) {
            try {
                $model = $model->$part()->getRelated();
            } catch (\Exception $e) {
                // return true if the last part of a relation string is not a method on the model,
                // so it's probably the attribute that we should show
                return true;
            }
        }

        return false;
    }

    private function makeSureFieldHasMultiple(): void
    {
        if ($this->hasAttribute('relation_type') and !$this->hasAttribute('multiple')) {
            $this->setAttribute('multiple', $this->guessIfFieldHasMultipleFromRelationType());
        }
    }

    private function guessIfFieldHasMultipleFromRelationType(): bool
    {
        return match ($this->getAttribute('relation_type')) {
            'BelongsToMany', 'HasMany', 'HasManyThrough', 'HasOneOrMany', 'MorphMany', 'MorphOneOrMany', 'MorphToMany' => true,
            default => false,
        };
    }

    private function makeSureFieldHasPivot(): void
    {
        if ($this->hasAttribute('relation_type') and !$this->hasAttribute('pivot')) {
            $this->setAttribute('pivot', $this->guessIfFieldHasPivotFromRelationType());
        }
    }

    private function guessIfFieldHasPivotFromRelationType(): bool
    {
        return match ($this->getAttribute('relation_type')) {
            'BelongsToMany', 'HasManyThrough', 'MorphToMany' => true,
            default => false,
        };
    }

    private function inferFieldTypeFromRelationType(): string
    {
        switch ($this->getAttribute('relation_type')) {
            case 'HasOne':
            case 'MorphOne':
                // if the related attribute was given, through dot notation
                // then we show a text field for it
                if (str_contains($this->getAttribute('entity'), '.')) {
                    return 'text';
                }

                // TODO: if relationship has `isOneOfMany` on it, load a readonly select; this covers:
                // - has One Of Many - hasOne(Order::class)->latestOfMany()
                // - morph One Of Many - morphOne(Image::class)->latestOfMany()
                $model = $this->getModel();
                $relationship = $model->{$this->getAttribute('entity')}();
                if ($relationship->isOneOfMany()) {
                    abort(500, "<strong>The relationship field type does not cover 'One of Many' relationships.</strong><br> Those relationship are only meant to be 'read', not 'created' or 'updated'. Please change your <code>{$this->getAttribute('name')}</code> field to use the 1-n relationship towards <code>{$this->getAttribute('model')}</code>, the one that does NOT have latestOfMany() or oldestOfMany(). See <a target='_blank' href='https://backpackforlaravel.com/docs/crud-fields#has-one-of-many-1-1-relationship-out-of-1-n-relationship'>the docs</a> for more information.");
                }


                // -----
                // The dev is trying to create a field for the ENTIRE hasOne/morphOne relationship
                // -----
                // if "subfields" is not defined, tell the dev to define it (+ link to docs)
                if (!is_array($this->getAttribute('subFields'))) {
                    abort(500, "<strong>Please define <code>subfields</code> on your <code>{$this->getAttribute('model')}</code> field.</strong><br>That way, you can allow the admin to edit the attributes on that related entry (through the hasOne relationship).<br>See <a target='_blank' href='https://backpackforlaravel.com/docs/crud-fields#crud-how-to#hasone-1-1-relationship'>the docs</a> for more information.");
                }
                // if "subfields" is defined, load a repeatable field with one entry (and 1 entry max)
                return 'relationship.entry';

            case 'BelongsTo':
            case 'BelongsToMany':
            case 'MorphToMany':
                // if there are pivot fields, we show the repeatable field
                if (is_array($this->getAttribute('subfields'))) {
                    return 'relationship.entries';
                }

                if (!$this->hasAttribute('inline_create')) {
                    if ($this->hasAttribute('ajax')) {
                        return 'relationship.entry';
                    }
                    return 'relationship.select';
                }

                // the field is being inserted in an inline creation modal case $inlineCreate is set.
                if (!$this->hasAttribute('inline_form')) {
                    return 'relationship.fetch_or_create';
                }

                if ($this->hasAttribute('ajax')) {
                    return 'relationship.fetch';
                }
                return 'relationship.select';

            case 'HasMany':
            case 'MorphMany':
                // when set, field value will default to what a developer defines
                $field['fallback_id'] = $field['fallback_id'] ?? false;
                // when true, backpack ensures that the connecting entry is deleted when unselected from relation
                $field['force_delete'] = $field['force_delete'] ?? false;

                // if there are pivot fields, we show the repeatable field
                if (is_array($this->getAttribute('subfields'))) {
                    return 'relationship.entries';
                } else {
                    // we show a regular/ajax select
                    if ($this->hasAttribute('ajax')) {
                        return 'relationship.fetch';
                    }
                    return 'relationship.select';
                }
                break;
            case 'HasOneThrough':
            case 'HasManyThrough':
                abort(500, "The relationship field does not support {$this->getAttribute('relation_type')} at the moment. This is a 'readonly' relationship type. When we do add support for it, it the field only SHOW the related entries, NOT allow you to select/edit them.");
                // TODO: load a readonly select for that chained relationship, and remove the abort above
                break;
            case 'MorphTo':
                // the fields for morphTo are automatically included and are backpack default ones
                // no need to load nothing for this field type.
                return 'relationship.morphTo';
                break;
            case 'MorphedByMany':
                abort(500, "The relationship field does not support {$this->getAttribute('relation_type')} at the moment, nobody asked for it yet. If you do, please let us know here - https://github.com/Laravel-Backpack/CRUD/issues");
            // TODO: complex interface that allows you to select entries from multiple models
            default:
                abort(500, "Unknown relationship type used with the 'relationship' field. Please let the Backpack team know of this new Laravel relationship, so they add support for it.");
                break;
        }
    }

    protected function inferFieldTypeFromDbColumnType(): string
    {
        $name = $this->getAttribute('name');

        if (Str::contains($name, 'password')) {
            return 'password';
        }

        if (Str::contains($name, 'email')) {
            return 'email';
        }

        if (is_array($name)) {
            return 'text'; // not because it's right, but because we don't know what it is
        }

        $this->setAttribute('db_column_type', $this->getForm()->getDbColumnType($name));

        return match ($this->getAttribute('db_column_type')) {
            'int', 'integer', 'smallint', 'mediumint', 'longint' => 'number',
            'boolean' => 'boolean',
            'tinyint' => 'active',
            'text', 'mediumtext', 'longtext' => 'textarea',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'time' => 'time',
            'json' => 'table',
            default => 'text',
        };
    }
}
