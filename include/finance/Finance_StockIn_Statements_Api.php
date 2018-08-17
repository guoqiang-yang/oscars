<?php
/**
 * 入库结算单相关接口.
 */

class Finance_StockIn_Statements_Api extends Base_Api
{
    public static function addStockInStatements($sid, $amount, $stockInIds = array(), $suid = 0)
    {
        $sid = intval($sid);
        $suid = intval($suid);
        assert($sid > 0);
        assert($stockInIds);
        $wsi = new Warehouse_Stock_In();
        $num = $wsi->getTotalByWhere('id IN('.implode(',',$stockInIds).') AND paid=2');
        $info = array();
        if($num == 0){
            $info['paid'] = 1;
        }

        $info['supplier_id'] = $sid;
        $info['suid'] = $suid;
        $info['amount'] = $amount;
        $info['ctime'] = date('Y-m-d H:i:s', time());
        $fss = new Finance_StockIn_Statements();
        $id = $fss->add($info);

        if (!empty($stockInIds))
        {
            foreach ($stockInIds as $_id)
            {
                $wsi->update($_id,array('statement_id' => $id));
            }
        }

        return $id;
    }

    //获取结算单列表
    public static function getStatementList($searchConf,&$total, $start = 0, $num = 20, $order)
    {
        $where = '1=1';
        if ($searchConf['id'])
        {
            $where .= sprintf(' AND id = %d', $searchConf['id']);
        }
        if ($searchConf['supplier_id'])
        {
            $where .= sprintf(' AND supplier_id = %d', $searchConf['supplier_id']);
        }
        if ($searchConf['start_ctime'])
        {
            $where .= sprintf(' AND ctime >= "%s"', $searchConf['start_ctime']);
        }
        if ($searchConf['end_ctime'])
        {
            $where .= sprintf(' AND ctime <= "%s"', $searchConf['end_ctime']);
        }
        if ($searchConf['payment_type'])
        {
            $where .= sprintf(' AND payment_type = %d', $searchConf['payment_type']);
        }
        if ($searchConf['paid']== 0 || $searchConf['paid'] != Conf_Base::STATUS_ALL)
        {
            $where .= sprintf(' AND paid = %d', $searchConf['paid']);
        }
        if($searchConf['is_invoice'] == 1)
        {
            $where .= ' AND invoice_ids = ""';
        }elseif($searchConf['is_invoice'] == 2)
        {
            $where .= ' AND invoice_ids <> ""';
        }
        $fss = new Finance_StockIn_Statements();
        $data = $fss->getListRawWhere($where, $total, $start, $num, $order);
        if($total>0){
            $sids = Tool_Array::getFields($data, 'supplier_id');
            $suppliers = Warehouse_Api::getSupplerByIds($sids);
            $supplerList = Tool_Array::list2Map($suppliers, 'sid', 'name');
            $staffs = Admin_Api::getStaffList();
            $staffList = Tool_Array::list2Map($staffs['list'], 'suid', 'name');
            foreach ($data as $key=>$item)
            {
                $data[$key]['supplier_name'] = $supplerList[$item['supplier_id']];
                $data[$key]['create_name'] = $staffList[$item['suid']];
                $data[$key]['payer_name'] = $staffList[$item['payer_suid']];
                if($item['invoice_ids'] == '')
                {
                    $data[$key]['invoice_status'] = '未开票';
                }else{
                    $invoice_ids = explode(',', $item['invoice_ids']);
                    foreach ($invoice_ids as $id)
                    {
                        $invoice_info = Invoice_Api::getInputInvoiceInfo($id);
                        if($invoice_info['step'] < Conf_Invoice::INVOICE_STEP_FINISHED)
                        {
                            $data[$key]['invoice_status'] = '开票中';
                            break;
                        }
                    }
                    $data[$key]['invoice_status'] = '已开票';
                }
            }
        }
        return $data;
    }

    public static function getAllCanBillStatementsOfSupplier($sid)
    {
        $sid = intval($sid);
        assert($sid > 0);
        $fss = new Finance_StockIn_Statements();
        $where = sprintf('supplier_id=%d and status=%d and paid>0 and invoice_ids=""',  $sid, Conf_Base::STATUS_NORMAL);
        return $fss->getListRawWhere($where,$total,false,false);
    }

    //获取结算单详情
    public static function getStatementDetail($id)
    {
        $fss = new Finance_StockIn_Statements();
        $statement_info = $fss->get($id);

        return $statement_info;
    }

    //更新结算单
    public static function updateStatement($id,$update)
    {
        $fss = new Finance_StockIn_Statements();
        assert($id>0);
        assert($update);
        return $fss->update($id,$update);
    }

    //撤回
    public static function recallStockIn($id,$update)
    {
        $wsi = new Warehouse_Stock_In();
        assert($id>0);
        assert($update);
        $info = $wsi->get($id);
        assert($info['statement_id']>0);
        $res = $wsi->update($id,$update);
        if($res)
        {
            $num = $wsi->getTotalByWhere(array('statement_id'=>$info['statement_id']));
            if($num>0){
                $updateArr = array();
                $updateArr['amount'] = $wsi->getSumByConf(array('statement_id'=>$info['statement_id']),'price');
                $statement_info = self::getStatementDetail($info['statement_id']);
                if($statement_info['paid'] == 0)
                {
                    $num = $wsi->getTotalByWhere(array('statement_id'=>$info['statement_id'],'paid'=>2));
                    if($num == 0){
                        $updateArr['paid'] = 1;
                    }
                }

                return self::updateStatement($info['statement_id'],$updateArr);
            }else{
                $fss = new Finance_StockIn_Statements();
                return $fss->update($info['statement_id'], array('status' => Conf_Base::STATUS_DELETED));
            }

        }else{
            return false;
        }
    }

    //更新结算单付款状态
    public static function updateStatementPaidByStockInID($id,$suid)
    {
        assert($id>0);
        $wsi = new Warehouse_Stock_In();
        $info = $wsi->get($id);
        if($info['statement_id']>0)
        {
            $fss = new Finance_StockIn_Statements();
            $statement_info = $fss->get($info['statement_id']);
            if($statement_info['paid'] == 0)
            {
                $num = $wsi->getTotalByWhere(array('statement_id'=>$info['statement_id'],'paid'=>2));
                if($num == 0){
                    $updateArr = array();
                    $updateArr['paid'] = 1;
                    self::updateStatement($info['statement_id'],$updateArr);
                }
            }
        }
    }

    public static function getSumByStockInID($conf)
    {
        assert($conf['statement_id']>0);
        $wsi = new Warehouse_Stock_In();
        return $wsi->getSumByConf($conf, 'price');
    }

    public static function getRefundSumByStockInID($conf)
    {
        assert($conf['ids']);
        $wsi = new Warehouse_Stock_In_Refund();
        return $wsi->getSumByConf($conf, array('sum(price) as price'));
    }
}