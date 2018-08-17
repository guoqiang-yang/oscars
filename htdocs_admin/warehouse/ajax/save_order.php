<?php

/**
 * 保存采购单基础信息, 不在此处理采购单的商品.
 *
 * @author yangguoqiang
 * @date 2016-09-24
 */

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $step;  //=1：创建; >1：编辑
    private $oid;
    private $sid;
    private $order = array();

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->step = Tool_Input::clean('r', 'step', TYPE_UINT);

        if (empty($this->step))
        {
            $this->step = Conf_In_Order::ORDER_STEP_NEW;
        }

        if (Conf_In_Order::ORDER_STEP_NEW==$this->step
            || Conf_In_Order::ORDER_STEP_SURE==$this->step)
        {
            $this->order = array(
                'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
                'contact_name' => Tool_Input::clean('r', 'contact_name', TYPE_STR),
                'contact_phone' => Tool_Input::clean('r', 'contact_phone', TYPE_STR),
                'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
                'freight' => 100 * Tool_Input::clean('r', 'freight', TYPE_UINT),
                'privilege' => 100 * Tool_Input::clean('r', 'privilege', TYPE_UINT),
                'privilege_note' => Tool_Input::clean('r', 'privilege_note', TYPE_STR),
                'note' => Tool_Input::clean('r', 'note', TYPE_STR),
                'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
                'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
                'in_order_type' => Tool_Input::clean('r', 'in_order_type', TYPE_UINT),
                'payment_days_date' => Tool_Input::clean('r', 'payment_days_date', TYPE_STR),
            );
        }
        else
        {
            $this->order = array(
                //'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
                'contact_name' => Tool_Input::clean('r', 'contact_name', TYPE_STR),
                'contact_phone' => Tool_Input::clean('r', 'contact_phone', TYPE_STR),
                'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
                'freight' => 100 * Tool_Input::clean('r', 'freight', TYPE_UINT),
                //'privilege' => 100 * Tool_Input::clean('r', 'privilege', TYPE_UINT),
                //'privilege_note' => Tool_Input::clean('r', 'privilege_note', TYPE_STR),
                'note' => Tool_Input::clean('r', 'note', TYPE_STR),
                'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
                //'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
                'payment_days_date' => Tool_Input::clean('r', 'payment_days_date', TYPE_STR),
            );
        }
    }

    protected function checkPara()
    {
        if (empty($this->sid))
        {
            throw new Exception('未能获取供应商信息，请联系技术人员！');
        }

        if (Conf_In_Order::ORDER_STEP_NEW==$this->step
            || Conf_In_Order::ORDER_STEP_SURE==$this->step)
        {
            if (empty($this->order['contact_name']))
            {
                throw new Exception('customer:contact person name empty');
            }
            if (empty($this->order['contact_phone']))
            {
                throw new Exception('order:empty phone');
            }
            if (empty($this->order['wid']))
            {
                throw new Exception('order:empty wid');
            }
        }
        else
        {
            if (empty($this->oid))
            {
                throw new Exception('order:empty order id');
            }
        }

//		if ($this->order['in_order_type'] == Conf_In_Order::IN_ORDER_TYPE_GIFT && Conf_Warehouse::isAgentWid($this->order['wid']))
//		{
//			throw new Exception('经销商采购单的采购类型不能是赠品入库！');
//		}
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/edit_in_order');
    }

    protected function main()
    {
        $supplierInfo = Warehouse_Api::getSupplier($this->sid);

        if ($supplierInfo['status'] != Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('供应商不是正常状态，不能下单！');
        }

        // 新建订单
        if (Conf_In_Order::ORDER_STEP_NEW == $this->step && empty($this->oid))
        {
            $this->order['step'] = Conf_In_Order::ORDER_STEP_SURE;
            $this->order['buyer_uid'] = $this->_uid;
            $this->oid = Warehouse_Api::addOrder($this->sid, $this->order, array());

            //生成采购单日志
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->oid,
                'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
                'action_type' => 1,
                'params' => json_encode(array('oid' => $this->oid)),
                'wid' => $this->order['wid'],
            );
            Admin_Common_Api::addAminLog($info);
        }
        // 更新订单信息
        else
        {
            $wio = new Warehouse_In_Order();
            $info = $wio->get($this->oid);
            if (Conf_Base::switchForManagingMode() && $info['managing_mode'] != $supplierInfo['managing_mode'])
            {
                throw new Exception('供应商与采购单的经营模式不一致！');
            }
            Warehouse_Api::updateOrder($this->_uid, $this->oid, $this->order);
        }
    }

    protected function outputPage()
    {
        $result = array('oid' => $this->oid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

