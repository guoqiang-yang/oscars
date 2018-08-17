<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/3/6
 * Time: 下午5:21
 */

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sidWid;
    private $cost;
    private $msg;
    private $originalPrice;
    private $supplierId;

    protected function getPara()
    {
        $this->sidWid = Tool_Input::clean('r', 'sidWid', TYPE_STR);
        $this->cost = Tool_Input::clean('r', 'price', TYPE_STR);
        $this->originalPrice = Tool_Input::clean('r', 'originalPrice', TYPE_UINT);
        $this->supplierId = Tool_Input::clean('r', 'supplierId', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/save_stock_cost');
    }

    protected function main()
    {
        $cost = empty($this->cost) ? 0 : $this->cost * 100;
        list($sid, $wid) = explode('_', $this->sidWid);

        $ws = new Warehouse_Stock();

        $res = $ws->update($wid, $sid, array('cost' => $cost));
        if ($res)
        {
            $this->msg = '更新成功！';
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->supplierId,
                'obj_type' => Conf_Admin_Log::OBJTYPE_SUPPLIER,
                'action_type' => 1,
                'params' => json_encode(array('sid' => $sid, 'from_price' => $this->originalPrice/100, 'to_price' => $cost/100)),
                'wid' => $wid,
                'city_id' => $this->city_id
            );
            Admin_Common_Api::addAminLog($info);
        }
    }

    protected function outputPage()
    {
        $result = array('msg' => $this->msg);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();