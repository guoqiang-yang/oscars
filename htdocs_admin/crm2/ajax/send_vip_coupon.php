<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $num;
    private $coupon_id;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
        $this->coupon_id = Tool_Input::clean('r', 'coupon_id', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->cid) || empty($this->num) || empty($this->coupon_id))
        {
            throw new Exception('common:params error');
        }
        if(!in_array($this->coupon_id, array_keys(Conf_Coupon::getVipCouponList())))
        {
            throw new Exception('现金券类型不合法');
        }
    }

    protected function main()
    {
        $cp = new Data_Dao('t_promotion_coupon');
        $coupon_info = $cp->get($this->coupon_id);
        if(empty($coupon_info))
        {
            throw new Exception('该现金券不存在');
        }
        if($coupon_info['status'] == Conf_Base::STATUS_DELETED)
        {
            throw new Exception('该现金券已删除');
        }
        //发送vip新券
        for($i=0;$i<$this->num;$i++)
        {
            Coupon_Api::addPromotionCoupon($this->cid, $this->coupon_id, 0, 0, Conf_Coupon::TYPE_BUCHANG, $this->_uid);
        }
//        $couponList = array(50 => $this->num);
//        Coupon_Api::addVipCoupon($this->cid, $couponList);

        Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_SEND_VIP_COUPON, array(
            'cid' => $this->cid,
            'num' => $this->num,
            'coupon_id' => $this->coupon_id,
        ));
    }

    protected function outputBody()
    {
        $result = array('cid' => $this->cid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();