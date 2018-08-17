<?php

/**
 * $response = new Response_Ajax($data);
 * echo $response->render();
 */

class Response_Ajax
{
    protected $content;
    protected $error;
    protected $uri;
    protected $refresh;
    protected $cache;
    protected $suppressHandler;

    protected $scripts = array();
    protected $styles = array();
    protected $onload = array();

    function __construct($content = null)
    {
        $this->content = $content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    public function seeOther($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    public function refresh()
    {
        $this->refresh = true;
        return $this;
    }

    public function addJs($js)
    {
        $this->scripts = array_merge($this->scripts, (array) $js);
        return $this;
    }

    public function addCss($css)
    {
        $this->styles = array_merge($this->styles, (array) $css);
        return $this;
    }

    public function addOnload($onload)
    {
        if (is_object($onload))
        {
            $onload = $onload->__toString();
        }

        $this->onload = array_merge($this->onload, (array) $onload);
        return $this;
    }

    public function setCache($bool = true)
    {
        $this->cache = $bool;
        return $this;
    }

    public function setSuppressHandler($bool = true)
    {
        $this->suppressHandler = $bool;
        return $this;
    }

    public function getSuppressHandler()
    {
        return $this->suppressHandler;
    }

    public function render()
    {
        if ($this->uri)
        {
            $response = array(
                "redirect" => $this->uri,
             );
        }
        else if ($this->refresh)
        {
            $response = array(
                "refresh" => true,
             );
        }
        else
        {
            $resource = $this->getResourceData($this->styles, $this->scripts);
            if (! empty($this->onload))
            {
                $resource["onload"] = $this->onload;
            }

            $response = array(
                "payload"  => $this->content,
                "error"    => $this->error,
                "resource" => $resource,
                "cache"    => $this->cache,
                "suppressHandler" => $this->suppressHandler,
            );

			$response = array_filter($response);
        }

        if (empty($response))
        {
            return false;
        }
        else
        {
            return self::safeJSONEncode($response);
        }
    }

    public function send()
    {
		if (!headers_sent())
		{
			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
		}

        $output = $this->render();
        if (! $output && !headers_sent())
        {
            header("HTTP/1.0 204 No Content");
        }
        else
        {
            echo $output;
        }
        return $this;
    }

    public static function safeJSONEncode($data)
    {
        return str_replace(array('<', '>', '&'), array('\u003c', '\u003e', '\u0026'), json_encode($data));
    }

    private function getResourceData(array $cssList, array $jsList)
    {
        $cssList = array_filter(array_unique($cssList));
        $jsList = array_filter(array_unique($jsList));

        $cssLinks = array_map(array('Tool_CssJs', "cssURL"), $cssList);
        $dependency = Tool_CssJs::getDependency($jsList);

        $jsLinks = array_map(array('Tool_CssJs', "jsURL"), $dependency["sync"]);

        $resource = array(
            "css" => array_values($cssLinks),
            "js"  => array_values($jsLinks),
        );
        $resource = array_filter($resource);

        if (!empty($dependency["asyncDepends"]) || !empty($dependency["async"]))
        {
            $map = array(
                "depends" => Tool_CssJs::formatResourceDepends($dependency["asyncDepends"]),
                "uris"    => Tool_CssJs::formatResourceMap($dependency["async"]),
            );
            $resource["map"] = $map;
        }

        return $resource;
    }

}
