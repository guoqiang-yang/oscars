<?php

/**
 * 弹出图层
 *
 */

class Response_Dialog extends Response_Ajax
{
    private $titie = "";
    private $body = "";
    private $width = 'auto';
    private $height = 'auto';
    private $metaData = array();
    private $handler = "";
    private $maskClassName = "";
    private $dialogClassName = "";

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setSize($width, $height)
    {
        $this->setWidth($width);
        $this->setHeight($height);
        return $this;
    }

    public function getSize()
    {
        return array($this->width, $this->height);
    }

    public function setMetaData(array $metadata)
    {
        $this->metaData = $metadata;
        return $this;
    }

    public function getMetaData()
    {
        return $this->metaData;
    }

    public function setHandler($handlerName)
    {
        $this->handler = $handlerName;

        $this->addJs("js/".$handlerName.".js");

        return $this;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setMaskClassName($className)
    {
        $this->maskClassName = $className;
        return $this;
    }

    public function getMaskClassName()
    {
        return $this->maskClassName;
    }

    public function setDialogClassName($className)
    {
        $this->dialogClassName = $className;
        return $this;
    }

    public function getDialogClassName()
    {
        return $this->dialogClassName;
    }

    public function render()
    {
        $this->setContent($this->renderData());

        return parent::render();
    }

    private function renderData()
    {
        $dialog = array(
            "title"   => $this->getTitle(),
            "body" => $this->getBody(),
            "width"   => $this->getWidth(),
            "height"  => $this->getHeight(),
            "handler" => $this->getHandler(),
            "metaData" => $this->getMetaData(),
            "maskClassName"     => $this->getMaskClassName(),
            "dialogClassName"     => $this->getDialogClassName(),
        );
        return array("dialog" => array_filter($dialog));
    }
}

