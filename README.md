JQueryFileUploadBundle
======================

Introduction
============

This library provides a symfony2 bundle for the [[BlueImp JQuery file uploader](https://github.com/blueimp/jQuery-File-Upload/)] package. See [[documentation](https://github.com/blueimp/jQuery-File-Upload/blob/master/README.md)]

This bundle is a fairly minimal wrapper because the existing PHP uploader class provided by BlueImp is very good already and does so many excellent things straight out of the box. We provided a way to integrate it into a Symfony 2 project.

This bundle started as a fork of the [symfony2-file-uploader-bundle](https://github.com/punkave/symfony2-file-uploader-bundle) from @punkave. I've made so many changes to the original fork that I just started a new bundle.

Note on Internet Explorer
=========================

Versions of Internet Explorer prior to 10 have no support for multiple file uploads. However IE users will be able to add a single file at a time and will still be able to build a collection of attached files. 

Requirements
============

* Symfony2
* jQuery
* jQuery UI

Installation
============

1. Add these package to your composer.json:
```json
    "require":{
        "mylen/jquery-file-upload-bundle":"*"
    },
    "repositories":[
        {
            "type":"package",
            "package":{
                "version":"dev-master",
                "name":"blueimp/jquery-file-upload",
                "autoload": {
                    "psr-0": {
                        "UploadHandler": "server/php"
                    }
                },
                "source":{
                    "url":"https://github.com/blueimp/jQuery-File-Upload.git",
                    "type":"git",
                    "reference":"master"
                },
                "dist":{
                    "url":"https://github.com/blueimp/jQuery-File-Upload/zipball/master",
                    "type":"zip"
                }
            }
    	}
    ]
```

2. Modify your AppKernel with the following line:
```php
    new Mylen\JQueryFileUploadBundle\JQueryFileUploadBundle(),
```

3. Update composer
```sh
   php composer.phar update
```
4. Add these to your configuration file
```yaml
# app/config/config.yml
imports:
    - { resource: '@JQueryFileUploadBundle/Resources/config/filters.yml' }
    - { resource: '@JQueryFileUploadBundle/Resources/config/assetic.yml' }
```
You are welcome to customize these files, just copy them in your app/config directory. As an exemple, you can restrict authorized file type. You can also bundle the CSS and JS files to your app CSS and JS; then remove the assetic.yml...

5. add routing
```yaml
    # app/config/routing.yml
    _mylen_jfub:
        resource: "@JQueryFileUploadBundle/Controller/"
        type:     annotation
        prefix:   /_jfub
```

6. install web assets
```sh
php app/console assets:install web/
```

7. run assetic dump
```sh
php app/console assetic:dump
```

Configuration Reference
=======================

* [Configuration Reference](https://github.com/Maxwell2022/jquery-file-upload-bundle/blob/PR1-max/Resources/doc/configuration_reference.md)


Usage
=====

You can use our templates like this:
```twig
    {% include "JQueryFileUploadBundle:Default:templates.html.twig" %}
```
or if you want to customize the view:
```twig
{% extends 'JQueryFileUploadBundle::templates.html.twig' %}
{% block js_blueimp_form %}
    <!--  TODO: change path -->
    <form id="fileupload" action="{{ path('default') }}" method="POST" {{ form_enctype(form) }}>
        {{ form_widget(form) }}
        {% include "JQueryFileUploadBundle::form.html.twig" %}
        <button type="submit" class="btn">Save</button>
    </form>
{% endblock js_blueimp_form %}
```

If you want to see how you can integrate these bundle into your app, I urge you to clone the [sandbox](https://github.com/mylen/jquery-file-upload-bundle). The sandbox integrate a configuration for vagrant so you can try it out of the box :o)

Configuration Parameters
========================

See `Resources/config/services.yml` in this bundle. You can easily decide what the parent folder of uploads will be and what file extensions are accepted, as well as what sizes you'd like image files to be automatically scaled to. 

The `from_folder`, `to_folder`, and `folder` options seen above are all appended after `file_uploader.file_base_path` when dealing with files. 

If `file_uploader.file_base_path` is set as follows (the default):

    file_uploader.file_base_path: "%kernel.root_dir%/../web/uploads"

And the `folder` option is set to `attachments/5` when calling `handleFileUpload`, then the uploaded files will arrive in:

    /root/of/your/project/web/uploads/attachments/5/originals

If the only attached file for this posting is `botfly.jpg` and you have configured one or more image sizes for the `file_uploader.sizes` option (by default we provide several useful standard sizes), then you will see:

    /root/of/your/project/web/uploads/photos/5/originals/botfly.jpg
    /root/of/your/project/web/uploads/photos/5/thumbnail/botfly.jpg
    /root/of/your/project/web/uploads/photos/5/medium/botfly.jpg
    /root/of/your/project/web/uploads/photos/5/large/botfly.jpg

So all of these can be readily accessed via the following URLs:

    /uploads/photos/5/originals/botfly.jpg

And so on.

The original names and file extensions of the files uploaded are preserved as much as possible without introducing security risks. 

Notes
=====

The uploader has been styled using Bootstrap conventions. If you have Bootstrap in your project, the uploader should look reasonably pretty out of the box.

The "Choose Files" button allows multiple select as well as drag and drop.