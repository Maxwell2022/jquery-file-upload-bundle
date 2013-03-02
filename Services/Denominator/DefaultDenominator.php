<?php

namespace Mylen\JQueryFileUploadBundle\Services\Denominator;

class DefaultDenominator implements IDenominator
{
    /**
     * Return the same name than the uploaded file
     *
     * @param string $uploadedName
     * @return string
     */
    public function getName($uploadedName)
    {
        return $uploadedName;
    }
}