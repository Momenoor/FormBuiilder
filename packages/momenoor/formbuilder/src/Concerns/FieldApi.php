<?php

namespace Momenoor\FormBuilder\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Momenoor\FormBuilder\Form;

trait FieldApi
{
    use HasAttributes;

    public function getName(): string
    {
        return $this->getAttribute('name') ?: $this->name;
    }

    public function setName(string $name): self
    {
        $this->setAttribute('name', $name);
        return $this;
    }

    public function getModel()
    {
        $this->setAttribute('model', $this->hasAttribute('model') ? $this->getAttribute('model') : $this->getForm()->getModel());
        return $this->getAttribute('model');
    }

    public function setModel(mixed $name): self
    {
        if (is_object($name) and is_subclass_of($name, Model::class)) {
            $this->setAttribute('model', $name);
            return $this;
        }

        $this->setAttribute('model', new $name);
        return $this;
    }

    public function getType(): string
    {
        return Arr::get($this->getAttributes(), 'type', $this->type);
    }

    public function setType(string $type): self
    {
        $this->setAttribute('type', $type);
        return $this;
    }

    private function getTemplateAttribute(): string
    {
        return Arr::get($this->attributes, 'template', false);
    }

    public function setTemplate(string $template): self
    {
        $this->setAttribute('template', $template);
        return $this;
    }

    public function getLabel(): string
    {
        return Arr::get($this->getAttributes(), 'label', false);
    }

    public function setLabel(string $label): self
    {
        $this->setAttribute('label', $label);
        return $this;
    }

    public function getValue(): string
    {
        return Arr::get($this->getAttributes(), 'value', false);
    }

    public function setValue(string $value): self
    {
        $this->setAttribute('value', $value);
        return $this;
    }

    public function getPlaceholder(): string
    {
        return Arr::get($this->getAttributes(), 'placeholder', false);
    }

    public function setPlaceholder(string $placeholder): self
    {
        $this->setAttribute('placeholder', $placeholder);
        return $this;
    }

    public function getRequired(): bool
    {
        return Arr::get($this->getAttributes(), 'required', false);
    }

    public function setRequired(bool $required): self
    {
        $this->setAttribute('required', $required);
        return $this;
    }

    public function getDisabled(): bool
    {
        return Arr::get($this->getAttributes(), 'disabled', false);
    }

    public function setDisabled(bool $disabled): self
    {
        $this->setAttribute('disabled', $disabled);
        return $this;
    }

    public function getError(): bool
    {
        return Arr::get($this->getAttributes(), 'error', false);
    }

    public function setError(bool $error): self
    {
        $this->setAttribute('error', $error);
        return $this;
    }

    public function getErrorClass(): string
    {
        return Arr::get($this->getAttributes(), 'errorClass', false);
    }

    public function setErrorClass(string $errorClass): self
    {
        $this->setAttribute('errorClass', $errorClass);
        return $this;
    }

    public function getId(): string
    {
        return Arr::get($this->getAttributes(), 'id', false);
    }

    public function setId(string $id): self
    {
        $this->setAttribute('id', $id);
        return $this;
    }

    public function getClass(): string
    {
        return Arr::get($this->getAttributes(), 'class', false);
    }

    public function setClass(string $class): self
    {
        $this->setAttribute('class', $class);
        return $this;
    }

    public function addClass(string $class): self
    {
        $class = trim($class);
        if ($this->hasAttribute('class')) {
            $this->setAttribute('class', $this->getAttribute('class') . ' ' . $class);
        }
        $this->setAttribute('class', $class);
        return $this;
    }


    protected function getForm(): Form
    {
        return Arr::get($this->attributes, 'form', false);
    }

    private function getTemplate(): string
    {
        return $this->getTemplateAttribute() ?: $this->getForm()->getConfig('field.template_prefix') .'.'. $this->getType();
    }
}
