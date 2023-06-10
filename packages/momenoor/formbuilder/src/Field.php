<?php

namespace Momenoor\FormBuilder;

use AllowDynamicProperties;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;
use Momenoor\FormBuilder\Concerns\FieldApi;
use Momenoor\FormBuilder\Concerns\HasOptions;
use Momenoor\FormBuilder\Concerns\HasFieldRelationship;
use Momenoor\FormBuilder\Concerns\HasIdentifier;
use ReflectionException;
use Momenoor\FormBuilder\Fields\Renderer;
use Spatie\Html\Html;

#[AllowDynamicProperties] class Field implements Arrayable
{
    use HasOptions;
    use FieldApi;
    use HasFieldRelationship;
    use HasIdentifier;
    use Renderer;

    protected mixed $htmlBuilder;
    protected array $allowedAttributes = [
        'name', 'id', 'type',
        'class', 'disabled', 'required',
        'placeholder', 'value', 'for'
    ];

    #[NoReturn] public function __construct(protected $name, protected $type = null, $attributes = [])
    {
        $this->htmlBuilder = app(Html::class);
        $this->setOptions($attributes);
    }

    public static function make($name, $type = null, $attributes = []): Field
    {
        return new self($name, $type, $attributes);
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function render()
    {
        $this->setDependenciesAndOptions();
        if (method_exists($this, $this->type)) {
            $this->{$this->type}();
        }
        return view()->make('FormBuilder::field')->with(
            [
                'field' => $this,
                'html' => $this->html,
                'model' => $this->getModel(),
                'options' => $this->getOptions(),
                'attributes' => $this->getAttributes(),
                'form' => $this->getForm(),
            ]
        );
    }

    #setDependanciesAndOptions

    /**
     * @throws Exception
     */
    private function setDependenciesAndOptions(): void
    {
        $this->maskSureFieldHasName();
        $this->maskSureFieldHasLabel();
        $this->maskSureFieldHasEntity();
        if ($this->hasOption('entity')) {
            $this->maskSureFieldHasRelationshipAttributes();
        }
        $this->makeSureFieldHasType();
        $this->makeSureFieldHasClass();
        $this->setRealName();

    }

    private function setRealName(): Field
    {
        $name = $this->getName();
        if ($this->has('relation_type')) {
            $name = Str::singular($name);
            $name = $name . '_id';
        }
        if (Arr::get($this->getForm()->getColumns(), $name, false)) {
            return $this->addOption('real_name', $name);
        }

        return $this->addOption('real_name', $this->name);
    }
    /**
     * @throws Exception
     */

    /**
     * @return void
     * @throws Exception
     */
    private function maskSureFieldHasName(): void
    {
        if (!is_string($this->name)) {
            throw new Exception('Field name is not set');
        };

        $this->addOption('name', $this->name);
    }

    /**
     * @throws ReflectionException
     */
    private function maskSureFieldHasEntity(): void
    {

        if ($this->hasOption('entity')) {
            return;
        }

        // by default, an entity is false if we cannot link it with guessing functions to a relation
        $this->addOption('entity', false);

        $model = $this->getModel();
        //if the name is dot notation we are sure it's a relationship
        if (str_contains($this->getName(), '.')) {
            $possibleMethodName = Str::of($this->getName())->before('.');
            // check model method for possibility of being a relationship
            $this->addOption('entity', $this->modelMethodIsRelationship($model, $possibleMethodName) ? $this->getName() : false);

            return;
        }

        // if there's a method on the model with this name
        if (method_exists($model, $this->getName())) {
            // check model method for possibility of being a relationship
            $this->addOption('entity', $this->modelMethodIsRelationship($model, $this->getName()));

            return;
        }

        // if the name ends with _id, and that method exists,
        // we can probably use it as an entity
        if (Str::endsWith($this->getName(), '_id')) {
            $possibleMethodName = Str::replaceLast('_id', '', $this->getName());

            if (method_exists($model, $possibleMethodName)) {
                // check model method for possibility of being a relationship
                $this->addOption('entity', $this->modelMethodIsRelationship($model, $possibleMethodName));

                return;
            }
        }

    }

    private function makeSureFieldHasType(): void
    {

        if (empty($this->type)) {
            $this->type = ($this->hasOption('relation_type')) ? $this->inferFieldTypeFromRelationType() : $this->inferFieldTypeFromDbColumnType();
        }

        if (is_string($this->type)) {
            $this->addOption('type', $this->type);
        }

    }

    private function maskSureFieldHasLabel(): void
    {
        if (!$this->hasOption('label')) {
            $name = $this->getName();
            $name = str_replace('_id', '', $name);
            $this->addOption('label', mb_ucfirst(str_replace('_', ' ', $name)));
        }

    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return (array)$this->attributes;
    }

    private function getHtml(): \Spatie\Html\Html
    {
        return html();
    }

    private function makeSureFieldHasClass(): void
    {
        $this->setAttribute('class', $this->hasOption('class') ? $this->getOption('class') : $this->getForm()->getConfig('field.class'));
    }


}
