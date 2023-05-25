<?php

namespace Momenoor\FormBuilder\Concerns;

use Illuminate\Support\Arr;
use Momenoor\FormBuilder\Form;

trait HasAttributes
{

    protected array $attributes = [];

    public function getAttribute($key): mixed
    {
        return Arr::get($this->attributes, $key, false);
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setAttribute($key, $value, $default = false): static
    {
        if (!empty($key) && is_string($key)) {
            Arr::set($this->attributes, $key, $value ?: $default);
        }
        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function hasAttribute($key): bool
    {
        return Arr::get($this->attributes, $key, false) !== false;
    }

    public function removeAttribute($name): static
    {
        Arr::forget($this->attributes, $name);
        return $this;
    }

    protected function prepareAttributes(): null|Form
    {

        if (!$this->attributes) {
            return null;
        }

        foreach ($this->attributes as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            $this->addOption($key, $value);
        }

        return $this;
    }
}
