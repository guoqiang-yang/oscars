<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/3/30
 * Time: 下午2:11
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $sid;
    private $type;
    private $msg;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/mark_stockout');
    }


    protected function main()
    {
        $opDao = new Data_Dao('t_order_product');
        $oo = new Order_Order();

        $opWhere = sprintf(' oid=%d and sid=%d ', $this->oid, $this->sid);
        $tmpOrdProduct = $opDao->getListWhere($opWhere);
        $ordProduct = current($tmpOrdProduct);
        $order = $oo->get($this->oid);

        if ($order['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('该订单已出库，无法标记，请刷新页面重试！');
        }

        if (($order['step'] >= Conf_Order::ORDER_STEP_SURE && $order['step'] < Conf_Order::ORDER_STEP_PICKED && $ordProduct['vnum']==0) || !empty($ordProduct['tmp_inorder_id']))
        {
            throw new Exception('库存充足或已创建临采单，请刷新页面重试！');
        }

        if ($this->type == Conf_Warehouse::ORDER_VNUM_FLAG_LACK)
        {
            if ($ordProduct['vnum_deal_type'] == Conf_Warehouse::ORDER_VNUM_FLAG_LACK)
            {
                throw new Exception('该商品已标记为外采，请刷新页面重试！');
            }

            $info = array('vnum_deal_type' => $this->type, 'wid' => $order['wid']);
            $res = $opDao->updateWhere($opWhere, $info);
        }
        else if ($this->type == Conf_Warehouse::ORDER_VNUM_FLAG_LATER)
        {
            if ($ordProduct['vnum_deal_type'] == Conf_Warehouse::ORDER_VNUM_FLAG_LATER)
            {
                throw new Exception('该商品已标记为晚到，请刷新页面重试！');
            }
            if (!empty($ordProduct['tmp_inorder_id']))
            {
                throw new Exception('该商品已创建临采单，请刷新页面重试！');
            }

            $where = sprintf(' oid=%d and sid=%d ', $this->oid, $this->sid);
            $info = array('vnum_deal_type' => $this->type, 'wid' => $order['wid']);
            $res = $opDao->updateWhere($where, $info);
        }

        if ($res)
        {
            $this->msg = '标记成功！';
        }
    }

    protected function outputBody()
    {
        $result = array('msg' => $this->msg);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();