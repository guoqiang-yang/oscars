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

	function __construct($lgmode='pri', $tmplpath=ADMIN_TEMPLATE_PATH, $cssjs=ADMIN_HOST)
	{
		parent::__construct($lgmode, $tmplpath);
		Tool_CssJs::setCssJsHost($cssjs);
		$this->setCssJs();
	}

    public function run()
    {
        if (0 && ENV == 'test')
        {
            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS + XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
            global $HC_SQL_EXTIMES;
            parent::run();
            $xhprofData = xhprof_disable();
            require '/usr/share/xhprof/xhprof_lib/utils/xhprof_lib.php';
            require '/usr/share/xhprof/xhprof_lib/utils/xhprof_runs.php';

            $xhprofRuns = new XHProfRuns_Default();
            $runId = $xhprofRuns->save_run($xhprofData, 'xhprof_test');

            echo '<div style="width: 100%; text-align: center; font-size: 18px;"><a target="_blank" href="http://x.test.haocaisong.cn/index.php?run=' . $runId . '&source=xhprof_test">-----------性能分析-----------</a></div>';

            uasort($HC_SQL_EXTIMES, 'sortSql');
            echo "\n\n<!--";
            foreach ($HC_SQL_EXTIMES as $item)
            {
                $extime = round($item['extime'] * 1000, 2);
                echo "[{$extime} ms] => {$item['sql']};\n";
            }
            echo "-->\n\n";
        }
        else
        {
            parent::run();
        }
    }

	protected function checkAuth($permission = '')
	{
		parent::checkAuth();

		if ($this->lgmode == 'pri' && empty($this->_uid))
		{
			header('Location: http://'.Conf_Base::getAdminHost().'/user/login.php');
			exit;
		}

        if ($_SERVER['SCRIPT_NAME'] != '/user/chgpwd.php' && $this->_user['is_simple_pwd'] == 1)
        {
            header('Location: /user/chgpwd.php');
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

        //访问次数限制
        Safe_Api::checkAdminVisitLimit($this->_uid);

        //城市
        $setStaffCityId = count($this->_user['city_wid_map'])==1?
                $this->_user['city_id']: $_COOKIE['city_id'];
        City_Api::setCity($setStaffCityId);
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
		$this->title = empty($this->title) ? '好材-运营系统' : $this->title;
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
		if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_LM_NEW) || in_array($this->_uid, Conf_Admin::$NEW_ORDER_REMIND_SUIDS))
		{
			$this->smarty->assign('max_time', Order_Api::getLatestSureTime($this->_user['wid']));
		}
		else
		{
			$this->smarty->assign('max_time', -1);
		}

        $canChangeCity = true;
        if (!empty($this->_user['cities']))
        {
            $citys = explode(',', $this->_user['cities']);
            if (count($citys) == 1)
            {
                $canChangeCity = false;
            }
        }

        $curCityInfo = City_Api::getCity();

        if ($canChangeCity)
        {
            $allCity = Conf_City::$CITY;
            
            $cityIds = array();
            if (!empty($this->_user['city_wid_map']))
            {
                $cityIds = array_keys($this->_user['city_wid_map']);
            }
            $cities = array();
            foreach ($cityIds as &$cityId)
            {
                $cities[$cityId] = $allCity[$cityId];
            }
            $cities = array_diff($cities, array($curCityInfo['city_id'] => $curCityInfo['city_name']));
            $this->smarty->assign('city_list', $cities);
        }
        $this->smarty->assign('cur_city', $curCityInfo['city_name']);
        $this->smarty->assign('can_change_city', $canChangeCity);
        
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

	protected function setCommonPara()
	{
		parent::setCommonPara();
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

function sortSql($a, $b)
{
    if ($a['extime'] == $b['extime'])
    {
        return 0;
    }

    return $a['extime'] > $b['extime'] ? -1 : 1;
}