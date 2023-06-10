<?php

namespace Momenoor\FormBuilder\Fields;

use Momenoor\FormBuilder\Fields\Select;
use Momenoor\FormBuilder\Fields\Text;
use Momenoor\FormBuilder\Fields\Email;

trait Renderer
{
    use Select, Text, Email;

    public function wrapperStart()
    {
        if (!$this->has('wrapper_start')) {
            return false;
        }
        return $this->htmlBuilder->div()->attributes($this->getOption('wrapper_start'))->open();
    }

    public function wrapperEnd()
    {
        return $this->htmlBuilder->div()->close();
    }

    public function labelStart()
    {
        if ($this->has('label')) {
            return $this->htmlBuilder->label()->attribute('for', $this->getName())->class('form-label')->text($this->getLabel())->render();
        }
        return null;
    }


}
