<?php

namespace Momenoor\FormBuilder;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Momenoor\FormBuilder\Concerns\FieldApi;
use Momenoor\FormBuilder\Concerns\HasAttributes;
use Momenoor\FormBuilder\Concerns\HasFieldRelationship;
use ReflectionException;

class Field implements Arrayable
{
    use HasAttributes;
    use FieldApi;
    use HasFieldRelationship;

    public function __construct(protected $name, protected $type = null, $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public static function make($name, $type = null, $attributes = [],): Field
    {
        return new self($name, $type, $attributes,);
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function render(): string
    {
        $this->setDependenciesAndOptions();
        return view()->make($this->getTemplate())->with(
            [
                'field' => $this->toArray(),
                'crud' => $this->getForm(),
            ]
        )->render();
    }

    #setDependanciesAndOptions

    /**
     * @throws Exception
     */
    private function setDependenciesAndOptions(): void
    {
        $this->maskSureFieldHasRequiredAttribute();
    }


    /**
     * @throws Exception
     */
    private function maskSureFieldHasRequiredAttribute(): void
    {
        $this->maskSureFieldHasName();
        $this->maskSureFieldHasLabel();
        $this->maskSureFieldHasEntity();
        if ($this->hasAttribute('entity')) {
            $this->maskSureFieldHasRelationshipAttributes();
        }
        $this->makeSureFieldHasType();


    }

    /**
     * @return void
     * @throws Exception
     */
    private function maskSureFieldHasName(): void
    {
        if (!is_string($this->name)) {
            throw new Exception('Field name is not set');
        };

        $this->setAttribute('name', $this->name);
    }

    /**
     * @throws ReflectionException
     */
    private function maskSureFieldHasEntity(): void
    {

        if ($this->hasAttribute('entity')) {
            return;
        }

        // by default, entity is false if we cannot link it with guessing functions to a relation
        $this->setAttribute('entity', false);

        $model = $this->getModel();
        //if the name is dot notation we are sure it's a relationship
        if (str_contains($this->getName(), '.')) {
            $possibleMethodName = Str::of($this->getName())->before('.');
            // check model method for possibility of being a relationship
            $this->setAttribute('entity', $this->modelMethodIsRelationship($model, $possibleMethodName) ? $this->getName() : false);

            return;
        }

        // if there's a method on the model with this name
        if (method_exists($model, $this->getName())) {
            // check model method for possibility of being a relationship
            $this->setAttribute('entity', $this->modelMethodIsRelationship($model, $this->getName()));

            return;
        }

        // if the name ends with _id, and that method exists,
        // we can probably use it as an entity
        if (Str::endsWith($this->getName(), '_id')) {
            $possibleMethodName = Str::replaceLast('_id', '', $this->getName());

            if (method_exists($model, $possibleMethodName)) {
                // check model method for possibility of being a relationship
                $this->setAttribute('entity', $this->modelMethodIsRelationship($model, $possibleMethodName));

                return;
            }
        }

    }

    private function makeSureFieldHasType(): void
    {

        if (empty($this->type)) {
            $this->type = ($this->hasAttribute('relation_type')) ? $this->inferFieldTypeFromRelationType() : $this->inferFieldTypeFromDbColumnType();
        }

        if (is_string($this->type)) {
            $this->setAttribute('type', $this->type);
        }


    }

    private function maskSureFieldHasLabel(): void
    {
        if (!$this->hasAttribute('label')) {
            $name = $this->getName();
            $name = str_replace('_id', '', $name);
            $this->setAttribute('label', mb_ucfirst(str_replace('_', ' ', $name)));
        }

    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return (array)$this->attributes;
    }

}
