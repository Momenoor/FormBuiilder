<?php

namespace Momenoor\FormBuilder\Fields;

interface BaseField{

        public  function setDefaults() : array;

        public  function render() : null;

}
