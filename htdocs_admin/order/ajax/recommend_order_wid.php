<?php

include_once ('../../../global.php');

Class App extends App_Admin_Ajax
{
    private $cmid;
    private $wid;

    protected function getPara()
    {
        $this->cmid = Tool_Input::clean('r', 'cmid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->cmid))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function main()
    {
        //根据小区id推荐出货仓库wid
        $oc = new Order_Community();
        $communityInfo = $oc->get($this->cmid);

        //丰台区3环内订单分给8号库（新南库）
        if ($communityInfo['district_id'] == 1005 && $communityInfo['ring_road'] == 2)
        {
            $this->wid = Conf_Warehouse::WID_8;
        }
        else if (in_array($this->cmid, array(40827,40584,26260,41435,40980,41436))) //知香园、鑫洋园、东旺家园、丰达园、富力又一城、世茂御龙湾
        {
            $this->wid = Conf_Warehouse::WID_TJ2;
        }
    }

    protected function outputBody()
    {
        $result = array('wid' => $this->wid, 'html' => '');

        if ($this->wid && array_key_exists($this->wid, Conf_Warehouse::getWarehouseByAttr('customer')))
        {
            $result['html'] = '<div style="display:inline; color: #ff0000; margin-left:15px">推荐仓库：' . Conf_Warehouse::$WAREHOUSES[$this->wid] . '</div>';
        }

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
    }
}

$app = new App('pub');
$app->run();