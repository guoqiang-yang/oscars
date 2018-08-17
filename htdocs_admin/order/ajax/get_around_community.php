<?php
include_once('../../../global.php');
/*
 * 获取某个坐标周边的10个小区
 */

class App extends App_Admin_Ajax
{
	private $lat;
	private $lng;
	private $remark;

	private $response = array('errno'=>1);
	

	protected function getPara()
	{
		$this->lat = Tool_Input::clean('r', 'lat', TYPE_STR);
		$this->lng = Tool_Input::clean('r', 'lng', TYPE_STR);
	}

	protected function checkPara()
	{
		if (empty($this->lat) || empty($this->lng))
		{
			$this->response['errno'] = 0;
			$this->response['errmsg'] = '参数输入有误';
		}
	}

	protected function main(){
		if ($this->response['errno'])
		{
		    $oc = new Order_Community();
            $this->response['list'] = $oc->getTenAroundCommunitys($this->lat, $this->lng);
		}
	}

	protected function outputPage()
	{
		echo json_encode($this->response);
		exit;
	}
}

$app = new App('pub');
$app->run();
