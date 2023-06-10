<?php

return [
    'app_name' => 'FormBuilder',
    'namespace' => 'Momenoor\FormBuilder',
    'form' => [
        'template_prefix' => 'FormBuilder::',
        'template' => 'form',
        'class' => 'form-horizontal',

    ],
    'field' => [
        'template_prefix' => 'FormBuilder::fields',
        'class' => 'form-control',
        'wrapper' => [
            'class' => ['form-group', 'size' => 'col-12'],
        ]
    ],
    'show_in_card' => true,
    'row_class' => 'row',

];
