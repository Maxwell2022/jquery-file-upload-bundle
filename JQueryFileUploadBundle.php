<?php

namespace Mylen\JQueryFileUploadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mylen\JQueryFileUploadBundle\DependencyInjection\FileUploaderExtension;

class JQueryFileUploadBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // register extensions that do not follow the conventions manually
        $container->registerExtension(new FileUploaderExtension());
    }
}
