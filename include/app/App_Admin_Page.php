<?php

/**
 * 管理运营后台 - 普通网页程序基类
 */

class App_Admin_Page extends App_Admin_Web
{
	protected $headTmpl = 'head/head_page.html';
	protected $tailTmpl = 'tail/tail_page.html';
	protected $title = "";
	protected $page = "";

	protected $csslist = array();
	protected $headjslist = array('js/base.js', 'js/env.js');
	protected $footjslist = array();
    
    protected $currCityId = array();
    
	function __construct($lgmode='pri', $tmplpath=ADMIN_TEMPLATE_PATH, $cssjs=ADMIN_HOST)
	{
		parent::__construct($lgmode, $tmplpath);
        
		Tool_CssJs::setCssJsHost($cssjs);
		$this->setCssJs();
	}

	protected function checkAuth($permission = '')
	{
		parent::checkAuth();

		if ($this->lgmode == 'pri' && empty($this->_uid))
		{
			header('Location: http://'.Conf_Base::getAdminHost().'/user/login.php');
			exit;
		}

		if ($this->lgmode == 'pri')
		{
            $forbidden = parent::checkPermission($permission);
            if ($forbidden)
            {
                header('Location: /common/forbidden.php');
                exit;
            }
		}

        //选择的城市
        $cityCookieKey = Conf_City::getKey4Cookie('sa');
        $this->currCityId = Tool_Input::clean('c', $cityCookieKey, TYPE_INT);
        if (empty($this->currCityId))
        {
            $this->currCityId = $this->_user['_city_ids'][0];
            setcookie($cityCookieKey, $this->currCityId, 86400, '/', Conf_Base::getAdminHost());
        }
	}

	protected function setTitle($title)
	{
		$this->title = $title;
	}

	protected function setCssJs()
	{
		$this->csslist = array(
			//'css/index.css',
		);

		$this->headjslist = $this->headjslist;

		$this->footjslist = array();
	}

	protected function addCss($cssList)
	{
		$this->csslist = array_merge($this->csslist , $cssList);
	}

	protected function addHeadJs($jsList)
	{
		if (is_string($jsList))
		{
			$jsList = array($jsList);
		}
		$this->headjslist = array_merge($this->headjslist , $jsList);
	}

	protected function addFootJs($jsList)
	{
		if (is_string($jsList))
		{
			$jsList = array($jsList);
		}
		$this->footjslist = array_merge($this->footjslist , $jsList);
	}

	protected function removeJs()
	{
		$this->headjslist = array();
		$this->setCssJs();
	}

	protected function setHeadTmpl($tmpl)
	{
		$this->headTmpl = $tmpl;
	}

	protected function setTailTmpl($tmpl)
	{
		$this->tailTmpl = $tmpl;
	}

	protected function outputHttp()
	{
		if (!headers_sent())
		{
			header("Content-Type: text/html; charset=".SYS_CHARSET);
			if ($this->_uid > 0)
			{
				header("Cache-Control: no-cache; private");
			}
			else
			{
				header("Cache-Control: no-cache");
			}
			header("Pragma: no-cache");
	   	}
	}

	protected function outputHead()
	{
		$this->title = empty($this->title) ? TITLE_SA : $this->title;
        
		if (defined('TITLE_PREFIX') && TITLE_PREFIX)
		{
			$this->title = TITLE_PREFIX . $this->title;
		}

		list($module, $page) = $this->getCurrentPage();

		$this->smarty->assign('curPage', $page);
		$this->smarty->assign('curModule', $module);
		$this->smarty->assign('modules', Conf_Admin_Page::getMODULES($this->_uid, $this->_user));
		$this->smarty->assign('title', $this->title);
		$this->smarty->assign('cssHtml', Tool_CssJs::getCssHtml($this->csslist));
		$this->smarty->assign('jsHtml', Tool_CssJs::getJsHtml($this->headjslist));
		
        
        $this->smarty->assign('cur_city', Conf_City::getByCityId($this->currCityId, 'cn'));
        
		$this->smarty->display($this->headTmpl);
	}

	protected function outputTail()
	{
		$jsHtml = Tool_CssJs::getJsHtml($this->footjslist, true);
		$this->smarty->assign('jsHtml', $jsHtml);

		$jsEnv = array("wwwHost" => ADMIN_HOST);
		$this->smarty->assign("jsEnv", $jsEnv);
		$this->smarty->display($this->tailTmpl);
        
        // 线上调试使用
        $isDebug = Tool_Input::clean('r', 'debug', TYPE_UINT);
        if ($isDebug == 1)
        {
            print_r($this);
        }
	}

	protected function showError($ex)
	{
		echo "<!-- \n";
		var_export($ex);
		echo "-->\n";

		$GLOBALS['t_exception'] = $ex;
		$this->delegateTo("common" . DS .  "500.php");
		Tool_Log::debug('@admin_page', Tool_Log::genSimpleLog4Exception($ex));
		exit;
	}

	protected function getCurrentPage()
	{
		$res = parse_url($_SERVER['REQUEST_URI']);
		$module = trim(dirname($res['path']), "\/");
		$page = basename($res['path'], '.php');

		if (! empty($this->page))
		{
			$page = $this->page;
		}

		return array($module, $page);
	}

}
