<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $id;
    private $claim;
    private $user_info;
	private $exec_result;
    private $log;
    private $aftersale;
    private $r_orders;
    private $orders;

    protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->claim = Tool_Input::clean('r', 'claim', TYPE_UINT);
	}

    protected function checkPara()
    {
        if (empty($this->id))
        {
            throw new Exception('aftersale: not on rule');
        }
    }

	protected function main()
	{
		$this->_uid = $this->getLoginUid();
        $this->user_info = Admin_Api::getStaff($this->_uid);
        $where = 'sid='.$this->id;
        $this->log = Aftersale_Log_Api::getList($where);
        $aftersale = Aftersale_Api::getDetail($this->id);
        if (!empty($this->id))
        {
            $info = Aftersale_Api::getDetail($this->id);
            $this->exec_result = $info['exec_status'];
            if($this->claim == 1)
            {
                $aa = new Aftersale_Func();
                $list = $aa->getByWhere(sprintf('exec_suid=%d and exec_status<%d and status=%d',$this->_uid , Conf_Aftersale::STATUS_DEAL, Conf_Base::STATUS_NORMAL));
                if(!empty($list) && $this->_user['department'] == Conf_Permission::DEPARTMENT_CS)
                {
                    echo '<div style="font-size: 20px; color: red;">你有未处理完的工单，不能认领</div><script type="application/javascript">window.setTimeout("window.location=\'/\aftersale/\list.php\'",2000);</script>';exit;
                }
                Aftersale_Api::update($this->id,array('exec_suid'=>$this->_uid));
                Aftersale_Log_Api::updateWhere(array('sid'=>$this->id,'exec_suid'=>0),array('exec_department'=>$this->user_info['role'],'exec_suid'=>$this->_uid));
                header('Location:/aftersale/detailLog.php?id='.$this->id);
            }
        }
		if ($this->exec_result < 5)
		{
			$this->deleMethod = Conf_Aftersale_Log::$DEAL_METHOD;
		}else if ($this->exec_result == 5 ) {
			$this->deleMethod = Conf_Aftersale_Log::$DEAL_METHOD_DONE;
		}
        if(!empty($aftersale)){
            $type_list=Conf_Aftersale::getShortDescOfObjtype($aftersale['type']);
            $aftersale['_re_type'] = Conf_Aftersale::$Objtype_Desc[$aftersale['type']].'-'.$type_list[$aftersale['type']][$aftersale['typeid']];
            $aftersale['_fb_type'] = Conf_Aftersale::$FB_TYPE[$aftersale['fb_type']];
            if(!empty($aftersale['objid'])){
                //查询相关订单信息
                $oids = explode(',',$aftersale['objid']);
                $orderInfo = Order_Api::getListByPk($oids);
                $orders = $orderInfo;
                foreach ($oids as $oid) {
                    //根据订单查询订单产品信息
                    $orderProducts = Order_Api::getOrderProducts($oid);
                    $orders[$oid]['products'] = $orderProducts['products'];
                }
                $this->orders = $orders;
            }

            //根据退货单或者补漏单查询相关产品信息
            $roids = explode(',', $aftersale['rid']);
            $r_orderInfo = Order_Api::getListByPk($roids);
            $r_orders = $r_orderInfo;
            if (!empty($aftersale['rid'])) {
                foreach ($roids as $roid) {
                    //根据订单查询订单产品信息
                    $r_orderProducts = Order_Api::getOrderProducts($roid);
                    $r_orders[$roid]['products'] = $r_orderProducts['products'];
                }
            }
            $this->r_orders = $r_orders;
        }
        $this->aftersale = $aftersale;
		$this->addFootJs(array('js/apps/aftersale.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('exec_result', $this->exec_result);
		$this->smarty->assign('uid', $this->_uid);
		$this->smarty->assign('work_group', Conf_Aftersale::$WORK_GROUP);
		$this->smarty->assign('admins', Conf_Aftersale::$WORK_GROUP);
		$this->smarty->assign('status', Conf_Aftersale::$STATUS);
		$this->smarty->assign('deal_method', $this->deleMethod);
		$this->smarty->assign('id', $this->id);
        $this->smarty->assign('staff_roles', Conf_Permission::$DEPAREMENT);
        $roles_list = Admin_Role_Api::getDepartmentOfStaff();
        $this->smarty->assign('staff_grouped', json_encode($roles_list));
        $this->smarty->assign('log',$this->log);
        $this->smarty->assign('aftersale',$this->aftersale);
        $this->smarty->assign('r_orders',$this->r_orders);
        $this->smarty->assign('orders',$this->orders);
        $this->smarty->assign('work_group',$roles_list[$this->user_info['role']]);
		$this->smarty->display('aftersale/deal.html');
	}
}

$app = new App('pri');
$app->run();
