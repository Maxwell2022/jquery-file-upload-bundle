<?php

namespace Mylen\JQueryFileUploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Mylen\JQueryFileUploadBundle\Services\IFileUploaderService;
//use Mylen\JQueryFileUploadBundle\Entity\Document;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     *
     * @throws \Exception
     * @return \Mylen\JQueryFileUploadBundle\Services\IResponseContainer
     */
    protected function handleRequest()
    {
        /** @var FileUploader */
        $uploader = $this->get('mylen.file_uploader');
        return $uploader->handleFileUpload();
    }

    /**
     *
     * @Route("/upload", name="jfub_files_put")
     * @Method({"PATCH", "POST", "PUT"})
     */
    public function putAction()
    {
        $upload = $this->handleRequest();
        $upload->post();
        return new Response($upload->getBody(), $upload->getType(), $upload->getHeader());
    }

    /**
     *
     * @Route("/upload", name="jfub_files_head")
     * @Method("HEAD")
     */
    public function headAction()
    {
        $uploader = $this->handleRequest();
        $uploader->head();
        return new Response($uploader->getBody(), $uploader->getType(), $uploader->getHeader());
    }

    /**
     *
     * @Route("/upload", name="jfub_files_get")
     * @Method("GET")
     */
    public function getAction()
    {
        $upload = $this->handleRequest();
        $upload->get();
        return new Response($upload->getBody(), $upload->getType(), $upload->getHeader());
    }

    /**
     *
     * @Route("/upload", name="jfub_files_delete")
     * @Method("DELETE")
     */
    public function deleteAction()
    {
        $upload = $this->handleRequest();
        $upload->delete();
        return new Response($upload->getBody(), $upload->getType(), $upload->getHeader());
    }
}
