<?php

class Routing_Router
{
    protected $routes;

    protected $programBasePath;

    protected $notFound;

    protected $error;

    public function __construct()
    {
        $this->routes = array();
    }

    public function getRequestURI()
    {
        $requestURI = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "/";
        $urlInfo = parse_url($requestURI);
        return $urlInfo["path"];
    }

    public function getRequestMethod()
    {
        return isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "GET";
    }

    public function loadRules($rules)
    {
        foreach ($rules as $rule)
        {
            $this->map($rule[0], $rule[1], $rule[2]);
        }
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function getMatchedRouteByURI($uri)
    {
        foreach ($this->routes as $route)
        {
            if ($route->matches($uri))
            {
                return $route;
            }
        }
        return false;
    }

    public function getMatchedRoute()
    {
        foreach ($this->routes as $route)
        {
            if ($route->matches($this->getRequestURI())
                && $route->supportsHttpMethod($this->getRequestMethod()))
            {
                return $route;
            }
        }
        return false;
    }

    public function map($pattern, $program, $methods = array("GET", "POST", "DELETE", "POST"))
    {
        $route = new Routing_Route($pattern, $program);
        $route->setHttpMethods((array) $methods);
        $route->setRouter($this);
        $this->routes[] = $route;
        return $route;
    }

    public function doRoute($uri = null)
    {
        $uri = $uri || $this->getRequestURI();

        $route = $this->getMatchedRoute();
        if ($route)
        {
            return $route->dispatch();
        }
        else
        {
            return false;
        }
    }

    public function setProgramBasePath($path)
    {
        $this->programBasePath = $path;
    }

    public function getProgramBasePath()
    {
        return $this->programBasePath;
    }

    public function setNotFoundProgram($program)
    {
        $this->notFoundProgram = $program;
    }

    public function loadProgram($program)
    {
        $file = $this->getProgramBasePath()."/".$program;
        if (is_file($file))
        {
            chdir(dirname($file));
            $_SERVER['_ROUTER_SCRIPT_NAME'] = "/".$program;
            require($file);
            return true;
        }
        return false;
    }

    public function notFound()
    {
        if ($this->notFoundProgram)
        {
            $this->loadProgram($this->notFoundProgram);
        }
        else
        {
            header("HTTP/1.0 404 NOT Found");
        }
    }

    public function setErrorProgram($program)
    {
        $this->error = $program;
    }

    public function redirectToURIWithSlash()
    {
        header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
        header("Location: ".$this->getRequestURI()."/");
        exit;
    }
}
