<?php

namespace Cycomi\Model\Base;

class ValidationError
{
    private $_validation_keys;


    public function __construct(array $validation_keys)
    {
        $this->_validation_keys = $validation_keys;
    }
}
