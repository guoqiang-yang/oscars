<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $plan;
	private $pid;
	private $execType;

	protected function getPara()
	{
		$this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
		$this->execType = Tool_Input::clean('r', 'exec_type', TYPE_STR);

		if ($this->pid && $this->execType == 'del')
        {
            $this->plan = array(
                'status' => Conf_Base::STATUS_DELETED,
            );
        }
        else
        {
            $this->plan = array(
                'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
                'method' => Tool_Input::clean('r', 'method', TYPE_UINT),
                'plan_type' => Tool_Input::clean('r', 'plan_type', TYPE_UINT),
                'attribute' => Tool_Input::clean('r', 'attribute', TYPE_UINT),
                'times' => Tool_Input::clean('r', 'times', TYPE_UINT),
                'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
                'is_random' => Tool_Input::clean('r', 'is_random', TYPE_UINT),
            );

            if ($this->execType == 'add')
            {
                $this->plan['suid'] = $this->_uid;
            }

            if ($this->plan['type'] == Conf_Stock::STOCKTAKING_TYPE_BY_LOCATION) {
                $this->plan['start_location'] = Tool_Input::clean('r', 'start_location', TYPE_STR);
                $this->plan['end_location'] = Tool_Input::clean('r', 'end_location', TYPE_STR);
            } else if ($this->plan['type'] == Conf_Stock::STOCKTAKING_TYPE_BY_BRAND) {
                $str = str_replace('，', ',', Tool_Input::clean('r', 'brand_list', TYPE_STR));
                $this->plan['brand_id'] = trim(trim($str, ','), '，');
            }

            if ($this->plan['is_random'] == 1) {
                $this->plan['random_num'] = Tool_Input::clean('r', 'random_num', TYPE_UINT);
            }
        }

	}

	protected function checkAuth()
	{
	    if ($_REQUEST['exec_type'] == 'add')
        {
            parent::checkAuth('hc_add_inventory_plan');
        }
        else if ($_REQUEST['exec_type'] == 'edit')
        {
            parent::checkAuth('hc_edit_inventory_plan');
        }
        else if ($_REQUEST['exec_type'] == 'del')
        {
            parent::checkAuth('hc_del_inventory_plan');
        }
        else
        {
            throw new Exception('common:permission denied');
        }
	}

	protected function main()
	{
		if ($this->pid)
		{
            Warehouse_Api::updateInventoryPlan($this->pid, $this->plan);
		}
		else
		{
            $this->pid = Warehouse_Api::addInventoryPlan($this->plan);
		}
	}

	protected function outputPage()
	{
		$result = array('pid' => $this->pid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

