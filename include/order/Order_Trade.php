<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/6/14
 * Time: 10:30
 */
class Order_Trade extends Base_Func
{
    private $_dao;


    public function __construct()
    {
        $this->_dao = new Data_Dao('t_out_trade');

        parent::__construct();
    }

    public function add($info)
    {
        Data_Memcache::getInstance()->set('order_trade_tag_' . $info['out_trade_no'], 1, 86400);
        return $this->_dao->add($info);
    }

    public function hasTradeNo($tradeNo)
    {
        $tradeTag = Data_Memcache::getInstance()->get('order_trade_tag_' . $tradeNo);
        if ($tradeTag > 0)
        {
            return true;
        }

        $where = array('out_trade_no' => $tradeNo);
        $list = $this->_dao->getListWhere($where);

        return !empty($list);
    }
}