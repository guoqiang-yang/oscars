<?php
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $wid;
	private $sid;
	private $type;
	private $num;
	private $remark;

	private $response = array('errno'=>1);
	

	protected function getPara()
	{
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		$this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
		$this->remark = Tool_Input::clean('r', 'remark', TYPE_STR);
	}

	protected function checkPara()
	{
		if (empty($this->wid) ||empty($this->sid))
		{
			$this->response['errno'] = 0;
			$this->response['errmsg'] = '参数输入有误';
		}
		
		if ($this->_user['wid']!=0 && $this->_user['wid']!=$this->wid)
		{
			$this->response['errno'] = 0;
			$this->response['errmsg'] = '不属于改该仓库';
		}
        
        if (1)
        {
            $this->response['errno'] = 0;
            $this->response['errmsg'] = '此处盘库已下线，在【货位库存处盘库】！或联系管理员！';
        }
	}

	protected function main(){
		if ($this->response['errno'])
		{
			$num = $this->type==Conf_Warehouse::STOCK_HISTORY_CHK_LOSS? 0-abs($this->num): abs($this->num);
			$this->response['finalNum'] = Warehouse_Api::updateChkStock($this->_uid, $this->wid, $this->sid, $num, $this->remark);
		}
	}

	protected function outputPage()
	{
		echo json_encode($this->response);
		exit;
	}
}

$app = new App('pri');
$app->run();
