<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $otype;
    private $communityId;
    private $address;
    
    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->otype = Tool_Input::clean('r', 'otype', TYPE_STR);
        $this->communityId = Tool_Input::clean('r', 'community_id', TYPE_UINT);
        $this->address = Tool_Input::clean('r', 'address', TYPE_STR);
    }
    
    protected function checkPara()
    {
        if (empty($this->id))
        {
            throw new Exception('参数错误');
        }
    }
    
    protected function main()
    {
        switch($this->otype)
        {
            case 'del':
                $this->_del();
                break;
            case 'match':
                $this->_matchCommuntiy();
                break;
            default:
                break;
        }
    }
    
    protected function outputBody()
    {
        $result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

    }
    
    private function _del()
    {
        $upData = array(
            'status' => Conf_Base::STATUS_DELETED,
            'suid' => $this->_uid,
        );
        Crm2_Api::updateConstructionSite($this->id, $upData);
    }
    
    private function _matchCommuntiy()
    {
        if (empty($this->communityId))
        {
            throw new Exception('没有选定小区！');
        }
        
        if (empty($this->address))
        {
            throw new Exception('工地地址不能为空！');
        }
        
        $communityInfo = Order_Community_Api::get($this->communityId);
        
        if (empty($communityInfo))
        {
            throw new Exception('选定的小区无效！');
        }
        
        $newAddress = $communityInfo['name'].Conf_Area::Separator_Construction.$this->address;
        $upData = array(
            'community_id'=> $this->communityId,
            'city' => $communityInfo['city_id'],
            'district' => $communityInfo['district_id'],
            'area' => $communityInfo['ring_road'],
            'suid' => $this->_uid,
            'address' => $newAddress,
        );
        Crm2_Api::updateConstructionSite($this->id, $upData);
        
        $oo = new Order_Order();
        $oUpData = array(
            'community_id' => $this->communityId,
            'address' => $newAddress,
        );
        $_where = 'construction='.$this->id;
        $oo->updateByWhere($oUpData, array(), $_where);
    }
}

$app = new App();
$app->run();