<?php

namespace Momenoor\FormBuilder\Fields;


trait Email
{
    public function email(): void
    {
        $this->html = $this->wrapperStart();
        $this->html .= $this->labelStart();
        $this->html.= $this->htmlBuilder->email($this->getName())->attributes($this->getAttributes())->render();
        $this->html .= $this->wrapperEnd();
    }




}
