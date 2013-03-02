<?php

namespace Mylen\JQueryFileUploadBundle\Services\Validator;

interface IValidator
{
    public function isValid($file);
}