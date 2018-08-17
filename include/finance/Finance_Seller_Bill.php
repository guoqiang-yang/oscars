<?php
/**
 * 商家结算
 * Created by PhpStorm.
 * User: zouliangwei
 * Date: 2017/6/8
 * Time: 下午5:16
 */
class Finance_Seller_Bill extends Base_Func
{
    private $sellerDao;
    private $sellerReceiptDao;

    public function __construct()
    {
        $this->sellerDao = new Data_Dao('t_seller_bill');
        $this->sellerReceiptDao = new Data_Dao('t_seller_bill_receipt');
    }

    public function getSellerBillInfo($bid)
    {
        $info = $this->sellerDao->get($bid);
        if($info['suid'] > 0)
        {
            $userInfo = Admin_Api::getStaff($info['suid']);
            $info['_suid_name'] = $userInfo['name'];
        }else{
            $info['_suid_name'] = '--';
        }
        return $info;
    }

    public function addSellerBill($info)
    {
        $bid = $this->sellerDao->add($info);
        return $bid;
    }

    public function updateSellerBill($bid, $info)
    {
        return $this->sellerDao->update($bid, $info);
    }

    public function getSellerBillList($conf, $start = 0, $num = 20)
    {
        return $this->sellerDao->limit($start, $num)->getListWhere($conf);
    }

    public function getList($conf, &$total, $start = 0, $num = 20)
    {
        $where = $this->_getWhereByConf($conf);
        $total = $this->sellerDao->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }
        $data = $this->sellerDao->order('order by bid desc')->limit($start, $num)->getListWhere($where);

        return $data;
    }

    public function getTotal($conf)
    {
        return $this->sellerDao->getTotal($conf);
    }

    public function addSellerBillReceipt($info)
    {
        $id = $this->sellerReceiptDao->add($info);
        return $id;
    }

    public function getSellerBillReceiptInfo($id)
    {
        return $this->sellerReceiptDao->get($id);
    }

    public function getSellerBillReceiptList($conf, &$total, $start = 0, $num = 20)
    {
        $total = $this->sellerReceiptDao->getTotal($conf);
        if ($total <= 0)
        {
            return array();
        }
        $data = $this->sellerReceiptDao->order('order by id desc')->limit($start, $num)->getListWhere($conf);

        return $data;
    }

    public function exportSellerBillInfo($bid)
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . '商家结算清单明细-' . $bid . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $billInfo = $this->getSellerBillInfo($bid);
        $warehouses = Conf_Warehouse::getSellerWarehouse();
        $payment_list = Conf_Finance::$MONEY_OUT_PAID_TYPES;
        Data_Csv::send(array('结算ID',$bid));
        Data_Csv::send(array('经销商',$warehouses[$billInfo['wid']]));
        Data_Csv::send(array('结算日期',$billInfo['balance_date_start'].' 至 '.$billInfo['balance_date_end']));
        Data_Csv::send(array('订单总金额',$billInfo['order_amount']/100));
        Data_Csv::send(array('退款总金额',$billInfo['refund_amount']/100));
        Data_Csv::send(array('扣点',$billInfo['ratio'].'%'));
        if($billInfo['step'] == 1)
        {
            Data_Csv::send(array('结算状态','未结算'));
        }else{
            Data_Csv::send(array('结算状态','已结算（'.$payment_list[$billInfo['payment_type']].'）'));
            Data_Csv::send(array('实付金额',$billInfo['real_amount']/100));
            Data_Csv::send(array('结算人',$billInfo['_suid_name']));
        }
        Data_Csv::send(array('备注',$billInfo['note']));

        $head = array(
            '单据ID', '单据类型','订单金额/退单金额', '付款时间', '回单时间/提交财务时间'
        );
        Data_Csv::send($head);

        $start = 0;
        $step = 100;
        do
        {
            $orders = $this->sellerReceiptDao->order('id', 'asc')->limit($start, $step)->getListWhere(array('bid' => $bid));
            if (count($orders) <= 0)
            {
                break;
            }

            foreach ($orders as $order)
            {
                $arr = array($order['objid'], $order['objtype'] == 1 ? '订单':'退款单', $order['bill_amount']/100, $order['pay_time'], $order['delivery_time']);
                Data_Csv::send($arr);
            }

            $start += $step;

        } while (count($orders) > 0);
    }

    /**
     * 根据conf生成where语句
     * @param     $conf
     * @param int $stausTag
     *
     * @return string
     */
    private function _getWhereByConf($conf)
    {
        $where = '1=1';

        if (!empty($conf['bid']))
        {
            $where .= sprintf(' AND bid=%d', $conf['bid']);
        }
        if (!empty($conf['wid']))
        {
            $where .= sprintf(' AND wid=%d', $conf['wid']);
        }
        return $where;
    }
}