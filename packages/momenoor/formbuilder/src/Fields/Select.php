<?php

namespace Momenoor\FormBuilder\Fields;


trait Select
{

    public function select(): void
    {
        $this->html = $this->wrapperStart();
        $this->html .= $this->labelStart();
        $this->setAttribute('data-control', 'select2');
        $this->setAttribute('class', 'form-select');
        $this->setAttribute('data-placeholder', $this->getPlaceholder() ?: 'Select...');
        $this->setAttribute('data-allow-clear', $this->getOption('allow-clear') ?: $this->checkIfFieldCanBeNull());
        $this->setAttribute('data-field-attribute',$this->getOption('attribute'));
        $this->setAttribute('data-related-model',$this->getOption('own_model'));
        $this->html .= $this->htmlBuilder->select($this->getName())->attributes($this->getAttributes())->render();
        $this->html .= $this->wrapperEnd();
    }

    public function setDefaults(): array
    {
        return [];
    }


}
