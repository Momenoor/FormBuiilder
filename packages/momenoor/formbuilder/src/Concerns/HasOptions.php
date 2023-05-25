<?php

namespace Momenoor\FormBuilder\Concerns;

trait HasOptions
{

    protected array $options = [
        'method' => 'POST',
        'action' => '',
        'enctype' => 'application/x-www-form-urlencoded',
        'accept-charset' => 'utf-8',
        'accept' => 'application/json, text/javascript, */*; q=0.01',
        'multipart' => false,
    ];
    public function addOption($key, $value = null): static
    {
        if ($key) {
            $this->options[$key] = $value;
        }
        return $this;
    }
    public function setOptions($options): static
    {
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
        return $this->options[$name];
    }

    protected function hasOption($option): bool
    {
        return isset($this->options[$option]);
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
}
