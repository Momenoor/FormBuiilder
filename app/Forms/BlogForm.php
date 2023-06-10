<?php

namespace App\Forms;

use App\Models\Blog;
use App\Models\Post;
use Momenoor\FormBuilder\Form;

class BlogForm extends Form
{

    public function buildForm(): void
    {

        $this->add(['name'=>'posts','required' => true])->addOption('pivot',true);

        $this->add(['name'=>'user_email','required' => true]);

    }
}
