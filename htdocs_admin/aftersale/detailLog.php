<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/5
 * Time: 15:35
 */
include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $aftersale;
    private $orders;
    private $r_orders;
    private $log;
    private $isAftersale;
    private $isAdmin;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
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
        $where = 'sid='.$this->id;
        $this->log = Aftersale_Log_Api::getList($where);
        $aftersale = Aftersale_Api::getDetail($this->id);

        //判断是否有处理的权限
        $this->isAftersale = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_AFTER_SALE);
        $this->isAdmin = Admin_Role_Api::isAdmin($this->_uid);

        if($aftersale['exec_suid'] == 0 && !$this->isAdmin)
        {
            echo '<div style="font-size: 20px; color: red;">需要先认领再查看</div><script type="application/javascript">window.setTimeout("window.location=\'/\aftersale/\list.php\'",2000);</script>';exit;
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
    }

    protected function outputBody()
    {
        $this->smarty->assign('log',$this->log);
        $this->smarty->assign('id',$this->id);
        $this->smarty->assign('aftersale',$this->aftersale);
        $this->smarty->assign('r_orders',$this->r_orders);
        $this->smarty->assign('orders',$this->orders);
        $this->smarty->assign('isAftersale', $this->isAftersale);
        $this->smarty->assign('isAdmin', $this->isAdmin);
        $this->smarty->display('aftersale/detailLog.html');
    }
}

$app = new App('pri');
$app->run();