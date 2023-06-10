<?php

namespace Momenoor\FormBuilder\Concerns;

use Illuminate\Support\Arr;

trait HasOptions
{
    use HasAttributes;

    protected array $options = [];

    public function addOption($key, $value = null): static
    {
        if ($key) {
            $this->options[$key] = $value;
        }
        if (in_array($key, $this->allowedAttributes)) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    public function setOptions($options): static
    {
        Arr::map($options, function ($value, $key) {
            if (in_array($key, $this->allowedAttributes)) {
                $this->setAttribute($key, $value);
            }
        });
        $this->options = $this->mergeOption($options, $this->options);
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getPreparedOptions($options = []): array
    {
        return $this->prepareOptions($options);
    }


    protected function mergeOption($targetOptions = [], $sourceOptions = []): array
    {
        return array_replace_recursive($this->options, $sourceOptions, $targetOptions);
    }

    public function getOption($name)
    {
        return Arr::get($this->options, $name, false);
    }

    protected function hasOption($option): bool
    {
        return Arr::get($this->options, $option, false) !== false;
    }


    /**
     * @param array $options
     * @return array
     */
    protected function prepareOptions(array $options = []): array
    {
        $options = $this->mergeOption($options, $this->options);

        $preparedOptions = [];
        foreach ($options as $key => $value) {
            if ($value !== false && $value !== null) {
                if (is_array($value)) {
                    $preparedOptions[$key] = implode(' ', $value);
                } else {
                    $preparedOptions[$key] = $value;
                }
            }
        }
        return $preparedOptions;
    }

    public function has($key): bool
    {
        return $this->hasOption($key) || $this->hasAttribute($key);
    }

}
