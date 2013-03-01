JQueryFileUploadBundle Configuration Reference
==============================================

All available configuration options are listed below with their default values.

``` yaml
# app/config/config.yml
file_uploader:
    file_base_path: ""
    web_base_path: "/uploads"
    allowed_extensions:
        - gif #image/gif
        - png #image/png
        - jpg #image/jpeg
        - jpeg #image/jpeg
        - pdf #application/pdf
        - mp3 #audio/mpeg
        - xls #application/vnd.ms-excel
        - ppt #application/vnd.ms-powerpoint
        - doc #application/msword
        - pptx #application/vnd.openxmlformats-officedocument.presentationml.presentation
        - sldx #application/vnd.openxmlformats-officedocument.presentationml.slide
        - ppsx #application/vnd.openxmlformats-officedocument.presentationml.slideshow
        - potx #application/vnd.openxmlformats-officedocument.presentationml.template
        - xlsx #application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
        - xltx #application/vnd.openxmlformats-officedocument.spreadsheetml.template
        - docx #application/vnd.openxmlformats-officedocument.wordprocessingml.document
        - dotx #application/vnd.openxmlformats-officedocument.wordprocessingml.template
        - txt #text/plain
        - rtf #text/rtf
        - xml #text/xml

    # Folder where originals are uploaded. This is the only folder populated for
    # uploads that are not images
    originals:
        folder: originals

    # Scaled versions of images. These image sizes are pretty great for
    # 1140 grid / responsive / bootstrap projects, but you can override as you see fit
    #
    # Width and height here are maximums to be enforced, NOT an aspect ratio to be enforced.
    # UploadHandler renders the smallest size that doesn't violate one of the limits.
    #
    # If an original is too small it is simply copied for that particular size. In short,
    # BlueImp did a good job here.
    #
    # You need not specify any sizes if you don't want FileUploader to scale images for you
    sizes:
        thumbnail:
            folder: thumbnails
            max_width: 80
            max_height: 80
        small:
            folder: small
            max_width: 320
            max_height: 480
        medium:
            folder: medium
            max_width: 640
            max_height: 960
        large:
            folder: large
            max_width: 1140
            max_height: 1140