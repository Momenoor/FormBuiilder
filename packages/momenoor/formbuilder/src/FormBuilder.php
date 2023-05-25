<?php

namespace Momenoor\FormBuilder;

use Illuminate\Contracts\Container\BindingResolutionException;

class FormBuilder
{


    public function __construct(public $form = Form::class)
    {
    }

    public function setDependenciesAndOptions($options): void
    {
        $this->form->prepareOptions($options);
    }

    /**
     * @throws BindingResolutionException
     */
    public function make($form, $options = [], $data = [])
    {
        if (is_string($form)) {
            $form = app()->make($form);
        }
        $form
            ->setOptions($options)
            ->setData($data)
            ->buildForm();

        return $this->form = $form;
    }

    /**
     * @throws BindingResolutionException
     */
    public function plain($options = [], $data = [])
    {
        return $this->make(app()->make($this->form), $options, $data);
    }
}
