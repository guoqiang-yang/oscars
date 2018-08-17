<?php


include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $wid;
	private $distance;
	private $communityId;
	private $feeList;
	private $status;
	private $note;
	private $oldDistance;

	protected function getPara()
	{
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
		$this->distance = Tool_Input::clean('r', 'distance', TYPE_NUM);
		$this->communityId = Tool_Input::clean('r', 'community_id', TYPE_UINT);
		$this->feeList = json_decode($this->feeList, true);
		$this->status = Tool_Input::clean('r', 'status', TYPE_UINT);
		$this->note = Tool_Input::clean('r', 'note', TYPE_STR);
		$this->oldDistance = Tool_Input::clean('r', 'old_distance', TYPE_NUM);
	}

	protected function checkPara()
	{
		if (empty($this->distance))
		{
			throw new Exception('common:params error');
		}
	}

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_community_fee');
    }


    protected function main()
	{
		Order_Community_Api::saveDistance($this->wid, $this->communityId, $this->distance, $this->status, $this->note);
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->communityId,
            'obj_type' => Conf_Admin_Log::OBJTYPE_COMMUNITY,
            'action_type' => 1,
            'params' => json_encode(array('cmid' => $this->communityId, 'from_distance' => $this->oldDistance , 'to_distance' => $this->distance)),
        );
		Admin_Common_Api::addAminLog($info);
	}

	protected function outputPage()
	{
		$this->smarty->assign('res', $this->res);
		$this->smarty->assign('distance', $this->distance);
		$this->smarty->assign('status', $this->status);
		$this->smarty->assign('community_id', $this->communityId);
		$this->smarty->assign('wid', $this->wid);

		$html = $this->smarty->fetch('order/block_distance_fee.html');
		$result = array('html' => $html);
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();
