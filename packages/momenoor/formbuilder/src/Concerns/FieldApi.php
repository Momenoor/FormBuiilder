<?php

namespace Momenoor\FormBuilder\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Momenoor\FormBuilder\Form;

trait FieldApi
{
    use HasOptions;

    public function getName(): string
    {
        return $this->getOption('name') ?: $this->name;
    }

    public function setName(string $name): self
    {
        $this->addOption('name', $name);
        return $this;
    }

    public function getModel()
    {
        $this->addOption('model', $this->hasOption('model') ? $this->getOption('model') : $this->getForm()->getModel());
        return $this->getOption('model');
    }

    public function setModel(mixed $name): self
    {
        if (is_object($name) and is_subclass_of($name, Model::class)) {
            $this->addOption('model', $name);
            return $this;
        }

        $this->addOption('model', new $name);
        return $this;
    }

    public function getType(): string
    {
        return Arr::get($this->getOptions(), 'type', $this->type);
    }

    public function setType(string $type): self
    {
        $this->addOption('type', $type);
        return $this;
    }

    private function getTemplateAttribute(): string
    {
        return Arr::get($this->options, 'template', false);
    }

    public function setTemplate(string $template): self
    {
        $this->addOption('template', $template);
        return $this;
    }

    public function getLabel(): string
    {
        return Arr::get($this->getOptions(), 'label', false);
    }

    public function setLabel(string $label): self
    {
        $this->addOption('label', $label);
        return $this;
    }

    public function getValue(): string
    {
        return Arr::get($this->getOptions(), 'value', false);
    }

    public function setValue(string $value): self
    {
        $this->addOption('value', $value);
        return $this;
    }

    public function getPlaceholder(): string
    {
        return Arr::get($this->getOptions(), 'placeholder', false);
    }

    public function setPlaceholder(string $placeholder): self
    {
        $this->addOption('placeholder', $placeholder);
        return $this;
    }

    public function getRequired(): bool
    {
        return Arr::get($this->getOptions(), 'required', false);
    }

    public function setRequired(bool $required): self
    {
        $this->addOption('required', $required);
        return $this;
    }

    public function getDisabled(): bool
    {
        return Arr::get($this->getOptions(), 'disabled', false);
    }

    public function setDisabled(bool $disabled): self
    {
        $this->addOption('disabled', $disabled);
        return $this;
    }

    public function getError(): bool
    {
        return Arr::get($this->getOptions(), 'error', false);
    }

    public function setError(bool $error): self
    {
        $this->addOption('error', $error);
        return $this;
    }

    public function getErrorClass(): string
    {
        return Arr::get($this->getOptions(), 'errorClass', false);
    }

    public function setErrorClass(string $errorClass): self
    {
        $this->addOption('errorClass', $errorClass);
        return $this;
    }

    public function getId(): string
    {
        return Arr::get($this->getOptions(), 'id', false);
    }

    public function setId(string $id): self
    {
        $this->addOption('id', $id);
        return $this;
    }

    public function getClass(): string
    {
        return Arr::get($this->getOptions(), 'class', false);
    }

    public function setClass(string $class): self
    {
        $this->addOption('class', $class);
        return $this;
    }

    public function addClass(string $class): self
    {
        $class = trim($class);
        if ($this->hasOption('class')) {
            $this->addOption('class', $this->getOption('class') . ' ' . $class);
        }
        $this->addOption('class', $class);
        return $this;
    }


    protected function getForm(): Form
    {
        return Arr::get($this->options, 'form', false);
    }

    private function getTemplate(): string
    {
        return $this->getTemplateAttribute() ?: $this->getForm()->getConfig('field.template_prefix') .'.'. $this->getType();
    }
}
