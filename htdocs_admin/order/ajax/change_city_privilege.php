<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $suid;
	private $amount;
    private $city_saler;

	protected function getPara()
	{
		$this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
		$this->amount = floor(Tool_Input::clean('r', 'amount', TYPE_NUM) * 100);
        $this->city_saler = Tool_Input::clean('r', 'city_saler', TYPE_UINT);
	}

	protected function checkPara()
	{
		// 订单有效性的验证
		if (empty($this->suid))
		{
			throw new Exception('销售人员为空!');
		}
	}

	protected function main()
	{
	    $city_id = City_Api::getCity();
        $saleDao = new Data_Dao('t_sale_privilege_config');
        $can_amount = 0;
        $_info = $saleDao->getListWhere(array('month'=>date('Ym'),'city_id'=>$city_id['city_id'],'suid'=>$this->suid));
        if(empty($_info))
        {
            throw new Exception('该销售没有相应的优惠额度！');
        }else{
            $_info = current($_info);
            $can_amount += $_info['total_amount'];
        }
        $_user = Admin_Api::getStaff($this->suid);
        if(!empty($this->city_saler) && $this->city_saler != $this->suid)
        {
            $city_saler = $saleDao->getListWhere(array('month'=>date('Ym'),'city_id'=>$city_id['city_id'],'suid'=>$this->city_saler));
            if(!empty($city_saler))
            {
                $city_saler = current($city_saler);
                $can_amount += $city_saler['available_amount'];
            }
        }
        if(($_info['total_amount']-$_info['available_amount']) > $this->amount)
        {
            throw new Exception('新总优惠金额小于当前销售已使用金额');
        }
        if($can_amount < $this->amount)
        {
            throw new Exception('新总优惠金额小于当前可分配额度');
        }
        $saleDao->updateWhere(array('month'=>date('Ym'),'city_id'=>$city_id['city_id'],'suid'=>$this->suid),array('total_amount'=>$this->amount), array('available_amount'=>($this->amount-$_info['total_amount'])));
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => Conf_Admin::ADMINOR_AUTO,
            'obj_type' => Conf_Admin_Log::OBJTYPE_SALER_PRIVILEGE,
            'action_type' => 1,
            'params' => json_encode(array('name' => $_user['name'], 'old_amount' => $_info['total_amount']/100, 'amount' => $this->amount/100, 'month' => date('Y-m'))),
        );
        Admin_Common_Api::addAminLog($info);
        if($this->city_saler > 0 && $this->city_saler != $this->suid && $_info['total_amount'] != $this->amount)
        {
            $_citySale = Admin_Api::getStaff($this->city_saler);
            $saleDao->updateWhere(array('month'=>date('Ym'),'city_id'=>$city_id['city_id'],'suid'=>$this->city_saler),array(),array('total_amount'=> ($_info['total_amount']-$this->amount),'available_amount'=>($_info['total_amount']-$this->amount)));
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => Conf_Admin::ADMINOR_AUTO,
                'obj_type' => Conf_Admin_Log::OBJTYPE_SALER_PRIVILEGE,
                'action_type' => 1,
                'params' => json_encode(array('name' => $_citySale['name'], 'old_amount' => $city_saler['total_amount']/100, 'amount' => ($city_saler['total_amount']+$_info['total_amount']-$this->amount)/100, 'month' => date('Y-m'))),
            );
            Admin_Common_Api::addAminLog($info);
        }
	}

	protected function outputBody()
	{
		$result = array('suid' => $this->suid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();