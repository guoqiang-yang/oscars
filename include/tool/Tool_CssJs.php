<?php

/**
 *  1.拼接css/js相关html
 *  2.分析css/js版本号
 *  3.分析js依赖关系
 */

class Tool_CssJs
{

	static $cssjsHost;

	public static function setCssJsHost($host)
	{
		self::$cssjsHost = $host;
	}

	private static function getCssJsHost()
	{
		return empty(self::$cssjsHost) ? Conf_Base::getCssJsHost() : self::$cssjsHost;
	}

    /**
     * 为引入的css文件生成可以插入html的代码段
     */
    public static function getCssHtml(array $list)
    {
        $html = "";
        if(count($list) > 0)
        {
            $html = implode("\n", array_map(array('self', "cssParser"), $list));
        }
        return $html;
    }

    /**
     * 为引入的js文件生成可以插入html的代码段
     * @param $deferred 确定文件是否可以延迟加载（可以延迟加载的放在尾部，其余的放在头部）
     */
    public static function getJsHtml(array $list, $deferred = false)
    {
        $html = "";
        if(count($list) > 0)
        {
            $html .= implode("\n", array_map(array('self', "deferredJsParser"), $list));
            $html .= "\n";
        }
        return $html;
    }

    /*
     * 为指定的js文件生成延迟加载的html代码
     */
    public static function getAsyncJsHtml(array $list)
    {
        $list = array_unique($list);

        $dependsConfig = Conf_JsDepend::$depends;

        // 异步依赖关系分析
        $asyncDependsData = array(
            "loaded" => array(),            // 已经分析过的异步依赖关系的js list
            "toload" => $list,
            "depends" => array(),
        );
        self::getCyclicAsyncDependency($asyncDependsData, $dependsConfig);

        // Haibei BasePath 的配置暂时放在这里
        $pathHtml = 'K.Resource.setBasePath("http://'.self::getCssJsHost().'/");';

        $dependsHtml = self::getResourceDependsHtml($asyncDependsData["depends"]);
        $mapHtml = self::getResourceMapHtml($asyncDependsData["loaded"]);

        return implode("\n", array_filter(array("<script>", $pathHtml, $dependsHtml, $mapHtml, "</script>")));
    }

    private static function getResourceDependsHtml(array $depends)
    {
        $arr = self::formatResourceDepends($depends);
        return "K.Resource.addResourceDepends(".json_encode($arr).");";
    }

    private static function getResourceMapHtml(array $map)
    {
        $arr = self::formatResourceMap($map);
        return "K.Resource.addResourceMap(".json_encode($arr).");";
    }

    public static function formatResourceDepends(array $depends)
    {
        $arr = array();
        foreach ($depends as $name => $depend)
        {
            $arr[self::formatResourceName($name)] = array_map(array('self', "formatResourceName"), $depend);
        }
        return $arr;
    }

    public static function formatResourceMap(array $map)
    {
        $map = self::version($map);
        $arr = array();
        foreach ($map as $name => $uri)
        {
            $arr[self::formatResourceName($name)] = $uri;
        }
        return $arr;
    }

    /**
     * 海贝的模块名，不包含 js/ 字符
     */
    private static function formatResourceName($name)
    {
        if (strpos($name, "js/") == 0)
        {
            $name = substr($name, 3);
        }
        return $name;
    }

    /**
     * 将js或css文件列表中的每一个文件添加版本号
     */
    private static function version($list, $type = "js")
    {
        // 分析过版本号的文件列表
        $versionedList = array();

        // 逐个文件标记版本号
        $postfix = $type === "js" ? ".js" : ".css";
        $versionConfig = $type === "js" ? Conf_JsVersion::$versions : Conf_CssVersion::$versions;
        foreach($list as $item)
        {
            $version = 0;
            if(isset($versionConfig[$item]))
            {
                $version = $versionConfig[$item];
            }
            $part = explode($postfix, $item);
            if(count($part) === 2 && $part[1] === '') { // 只给.js结尾的文件加版本号
                $name = $part[0];
                $versionCode = sprintf("%04d", intval($version)).substr(md5(SYS_CODE.intval($version).$name), 0, 5);
                $versionedList[$item] = $name."-".$versionCode.$postfix;
            } else {
                $versionedList[$item] = $item;
            }
        }
        return $versionedList;
    }

    /**
     * 分析引入的js文件的依赖关系
     */
    public static function getDependency($list)
    {
        $dependsConfig = Conf_JsDepend::$depends;

        // 同步依赖关系分析
        $dependsData = array(
            "loaded" => array(),            // 已经分析过依赖关系的js list
            "toload" => $list,              // 待分析的js list
            "async" => array(),             // 分析获得的初步异步加载关系
        );
        self::getCyclicDependency($dependsData, $dependsConfig);

        // 异步依赖关系分析
        $asyncDependsData = array(
            "loaded" => array(),            // 已经分析过的异步依赖关系的js list
            "toload" => $dependsData["async"],  // 待分析的js list
            "depends" => array(),           // 分析获得的异步依赖关系
        );
        self::getCyclicAsyncDependency($asyncDependsData, $dependsConfig);

        return array(
            "sync" => $dependsData["loaded"],   // js同步依赖的js列表
            "async" => $asyncDependsData["loaded"], // js异步依赖的所有js列表
            "asyncDepends" => $asyncDependsData["depends"], // js异步依赖的js再同步依赖的所有js列表
        );
    }

    /*
     * 同步依赖关系分析方法
     */
    private static function getCyclicDependency(array &$depends, array $dependencyConfig)
    {
        while($item = array_pop($depends["toload"]))
        {
            if(!in_array($item, $depends["loaded"])) // 分析还未被分析的js，防止死循环
            {
                if(isset($dependencyConfig[$item]))
                {
                    $configDepends = $dependencyConfig[$item];

                    // 获取同步js依赖
                    if(isset($configDepends["sync"]))
                    {
                        $depends["toload"] = array_merge($configDepends["sync"], $depends["toload"]);
                    }

                    // 获取异步依赖
                    if(isset($configDepends["async"]))
                    {
                        $depends["async"] = array_merge($depends["async"], $configDepends["async"]);
                    }
                }
                $depends["loaded"][] = $item;
            }
        }
    }

    /**
     * 异步依赖关系分析方法
     */
    private static function getCyclicAsyncDependency(array &$depends, array $dependencyConfig)
    {
        while($item = array_pop($depends["toload"]))
        {
            if(!in_array($item, $depends["loaded"])) // 分析还未被分析的js，防止死循环
            {
                if(isset($dependencyConfig[$item]))
                {
                    $configDepends = $dependencyConfig[$item];

                    // 获取同步依赖
                    if(isset($configDepends["sync"]))
                    {
                        $depends["toload"] = array_merge($configDepends["sync"], $depends["toload"]);

                        // 同步依赖关系，需要将depends单独记录，Haibei框架需要这样的依赖关系来加载异步js
                        $depends["depends"][$item] = $configDepends["sync"];
                    }

                    // 获取异步依赖
                    if(isset($configDepends["async"]))
                    {
                        $depends["toload"] = array_merge($configDepends["async"], $depends["toload"]);
                    }
                }
                $depends["loaded"][] = $item;
            }
        }
    }

    /**
     * 单个css文件转成链接
     *
     */
    public static function cssURL($css)
    {
        $css = current(self::version(array($css), "css"));
        $http = '//';
        $cssHost = $http . self::getCssJsHost() . "/";
        $find = false;
        if (strpos($css, "http://") !== false && !empty($_SERVER['HTTPS']))
        {
            $css = str_replace('http://', 'https://', $css);
            $find = true;
        }
        $host = $find ? "" : $cssHost;

        return $host . $css;
    }

    /**
     * 单个css文件生成html代码解析器
     */
    private static function cssParser($css)
    {
        return '<link href="'.self::cssURL($css).'" rel="stylesheet" />';
    }

    /**
     * 单个js转成链接
     *
     */
    public static function jsURL($js)
    {
        if (strpos($js, "http://") === 0 || strpos($js, 'https://') === 0)
        {
            if (strpos($js, "http://") !== false && !empty($_SERVER['HTTPS']))
            {
                $js = str_replace('http://', 'https://', $js);
            }
            return $js;
        }
        else
        {
            $js = current(self::version(array($js), "js"));
            $http = '//';
            $host = $http . self::getCssJsHost() . "/";
        }

        return $host . $js;
    }

    /**
     * 单个头部js文件生成html代码解析器
     */
    private static function jsParser($js)
    {
        return '<script src="'.self::jsURL($js).'"></script>';
    }

    /**
     * 单个尾部js文件生成js加载代码解析器
     */
    private static function deferredJsParser($js)
    {
        //return "K.Resource.loadJS(\"".self::jsURL($js)."\");";
        return '<script src="'.self::jsURL($js).'" type="text/javascript"></script>';
    }
}
