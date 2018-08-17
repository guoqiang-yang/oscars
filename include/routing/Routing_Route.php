<?php

class Routing_Route
{
    protected $pattern;

    protected $program;

    protected $params = array();

    protected $methods = array();

    protected $router;

    public function __construct($pattern, $program)
    {
        $this->setPattern($pattern);
        $this->setProgram($program);
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setPattern($pattern)
    {
        $this->pattern = str_replace(')', ')?', (string)$pattern);
    }

    public function getProgram()
    {
        return $this->program;
    }

    public function setProgram($program)
    {
        $info = parse_url($program);
        if (isset($info["query"]))
        {
            $this->program = $info["path"];
            parse_str($info["query"], $this->params);
        }
        $this->program = $info["path"];
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setHttpMethods($methods)
    {
        $this->methods = $methods;
    }

    public function getHttpMethods()
    {
        return $this->methods;
    }

    public function supportsHttpMethod($method)
    {
        return in_array($method, $this->methods);
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter(Routing_Router $router )
    {
        $this->router = $router;
    }

    public function matches($resourceURI)
    {
        $paramNames = array();
        preg_match_all('@:([\w]+)@', $this->pattern, $paramNames, PREG_PATTERN_ORDER);

        $paramNames = $paramNames[0];

        $patternAsRegex = preg_replace_callback('@:[\w]+@', array($this, 'convertPatternToRegex'), $this->pattern);

        if (substr($this->pattern, -1) === '/')
        {
            $patternAsRegex = $patternAsRegex . '?';
        }
        $patternAsRegex = '@^' . $patternAsRegex . '$@';

        $paramValues = array();
        if (preg_match($patternAsRegex, $resourceURI, $paramValues))
        {
            array_shift($paramValues);
            foreach ($paramNames as $ii => $name)
            {
                $name = substr($name, 1);
                if (isset($paramValues[$name]) )
                {
                    $this->params[$name] = urldecode($paramValues[$name]);
                }
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function convertPatternToRegex($paramNames)
    {
        $key = str_replace(':', '', $paramNames[0]);
        return '(?P<' . $key . '>[a-zA-Z0-9_\-\.\!\~\*\\\'\(\)\:\@\&\=\$\+,%]+)';
    }

    public function dispatch()
    {
        if (substr($this->pattern, -1) === '/' && substr($this->router->getRequestURI(), -1) !== '/')
        {
            $this->router->redirectToURIWithSlash();
        }

        if ($this->getProgram())
        {
            if ($this->getParams())
            {
                $_REQUEST = array_merge($_REQUEST, $this->params);
            }
            return $this->router->loadProgram($this->program);
        }

        return false;
    }

}
