<?php

namespace Mylen\JQueryFileUploadBundle\Services\Validator;

class NoValidator implements IValidator
{
    public function isValid($file)
    {
        return ture;
    }
}