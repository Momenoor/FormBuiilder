<?php

namespace Momenoor\FormBuilder\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Momenoor\FormBuilder\Field;
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
        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    public function setAttribute($key, $value, $default = false): static
    {
        if (!empty($key) && is_string($key) && (in_array($key, $this->allowedAttributes) OR Str::startsWith($key, 'data-'))) {
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

    protected function prepareAttributes(): null|Form|Field
    {
        $attributes = [];
        if (!$this->attributes) {
            return null;
        }

        foreach ($this->attributes as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            if (in_array($key, $this->allowedAttributes)) {
                $attributes[$key] = $value;
            }
        }

        $this->setAttributes($attributes);
        return $this;
    }
}
