<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $fromDate;
    private $endDate;
    private $data;
    private $fromDateReal;
    private $endDateReal;
    private $totalData = array();
    private $warehouseFee;

    protected function getPara()
    {
        $this->fromDate = Tool_Input::clean('r', 'from_date', TYPE_STR);
        $this->endDate = Tool_Input::clean('r', 'end_date', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->fromDate))
        {
            if (date('d') == '01')
            {
                $this->fromDate = date("Y-m", strtotime('-1 day'));
            }
            else
            {
                $this->fromDate = date("Y-m");
            }
        }
        if (empty($this->endDate))
        {
            if (date('d') == '01')
            {
                $this->endDate = date("Y-m", strtotime('-1 day'));
            }
            else
            {
                $this->endDate = date("Y-m", strtotime('-1 day'));
            }
        }

        $this->fromDateReal = date('Y-m-01', strtotime($this->fromDate));
        $this->endDateReal = date('Y-m-t', strtotime($this->endDate));
    }

    protected function main()
    {
        //盘亏数据 TODO 时间间隔大了盘亏这里就会导致很慢，应该修复一下
        $start = 0;
        $num = 5000;
        $checkOutDataByWid = array();
        do
        {
            $checkOutList = null;
            $checkOutList = Statistics_Api::getBaseSkuInfoBetweenDateWithoutTotal(0, $this->fromDateReal, $this->endDateReal, $start, $num, array('sid', 'day', 'wid', 'check_out_cost', 'check_in_cost'));

            foreach ($checkOutList as $item)
            {
                $wid = $item['wid'];
                $checkOutDataByWid[$wid] += $item['check_out_cost'] - $item['check_in_cost'];
            }

            $start += $num;
        }
        while (count($checkOutList) > 0);

        $this->warehouseFee = Statistics_Api::getWarehouseFeeList(0);
        $warehouseFeeByMonth = array();
        foreach ($this->warehouseFee as $item)
        {
            $warehouseFeeByMonth[$item['wid']][$item['month']] = $item;
        }

        //费用数据
        $start = 0;
        $num = 5000;
        $feeDataByWid = array();
        do
        {
            $feeData = $res = null;
            $res = Statistics_Api::getWarehouseBaseInfoBetweenDate($this->fromDateReal, $this->endDateReal, $start, $num);
            $feeData = $res['list'];
            if (count($feeData) == 0)
            {
                break;
            }

            foreach ($feeData as $feeItem)
            {
                $wid = $feeItem['wid'];
                $price = $feeItem['amount'] - $feeItem['customer_freight'] - $feeItem['customer_carriage'] + $feeItem['privilege'];
                $cost = $feeItem['cost'];
                $logisticsIn = $feeItem['customer_freight'] + $feeItem['customer_carriage'];
                $logisticsOut = $feeItem['freight'] + $feeItem['carriage'];

                if (!isset($feeDataByWid[$wid]))
                {
                    $feeDataByWid[$wid] = array(
                        'wid' => $wid,
                        'price_total' => $price,
                        'cost' => $cost,
                        'refund' => $feeItem['refund'],
                        'logistics_in' => $logisticsIn,
                        'logistics_out' => $logisticsOut,
                        'privilege' => $feeItem['privilege'],
                    );
                }
                else
                {
                    $feeDataByWid[$wid]['price_total'] += $price;
                    $feeDataByWid[$wid]['cost'] += $cost;
                    $feeDataByWid[$wid]['refund'] += $feeItem['refund'];
                    $feeDataByWid[$wid]['logistics_in'] += $logisticsIn;
                    $feeDataByWid[$wid]['logistics_out'] += $logisticsOut;
                    $feeDataByWid[$wid]['privilege'] += $feeItem['privilege'];
                }
            }

            $start += $num;
        }
        while (count($feeData['list']) > 0);

        $this->totalData = array(
            'price_total' => 0,
            'refund' => 0,
            'logistics_in' => 0,
            'logistics_out' => 0,
            'fixed_input' => 0,
            'staff_salary' => 0,
            'other_input' => 0,
            'cost' => 0,
            'privilege' => 0,
        );

        $wids = array_keys($feeDataByWid);
        foreach ($wids as $wid)
        {
            $wname = Conf_Warehouse::$WAREHOUSES[$wid];
            //其他费用
            $startDate = $this->fromDate;
            $endDate = $this->endDate;
            $fixedInput = $staffSalary = $otherInput = 0;
            while ($startDate <= $endDate)
            {
                $lastMonth = date('Y-m', strtotime("-1 month", strtotime($startDate)));
                $fixedInputMonth = $warehouseFeeByMonth[$wid][$startDate]['fixed_input'];
                if ($fixedInputMonth == 0)
                {
                    $fixedInputMonth = $warehouseFeeByMonth[$wid][$lastMonth]['fixed_input'] / 30 * date('j');
                }
                $staffSalaryMonth = $warehouseFeeByMonth[$wid][$startDate]['staff_salary'];
                if ($staffSalaryMonth == 0)
                {
                    $staffSalaryMonth = $warehouseFeeByMonth[$wid][$lastMonth]['staff_salary'] / 30 * date('j');
                }

                $fixedInput += $fixedInputMonth;
                $staffSalary += $staffSalaryMonth;
                $otherInput += $warehouseFeeByMonth[$wid][$startDate]['other_input'];
                //物流费用 = 线上物流费用 + 线下物流费用
                $feeDataByWid[$wid]['logistics_out'] += $warehouseFeeByMonth[$wid][$startDate]['offline_logistics_fee'] * 100;

                $startDate = date('Y-m', strtotime("+1 month", strtotime($startDate)));
            }

            $grossRate = round(($feeDataByWid[$wid]['price_total'] - $feeDataByWid[$wid]['cost']) / $feeDataByWid[$wid]['price_total'] * 100, 2);
            $logisticsSubsidy = $feeDataByWid[$wid]['logistics_out'] - $feeDataByWid[$wid]['logistics_in'];
            $grossLogisticsFeePercent = round($feeDataByWid[$wid]['logistics_out'] / ($feeDataByWid[$wid]['price_total'] - $feeDataByWid[$wid]['refund']) * 100, 2);
            $realLogisticsFeePercent = round(($feeDataByWid[$wid]['logistics_out'] - $feeDataByWid[$wid]['logistics_in']) / ($feeDataByWid[$wid]['price_total'] - $feeDataByWid[$wid]['refund']) * 100, 2);
            $totalLogisticsScale = round(($feeDataByWid[$wid]['logistics_out'] + $fixedInput * 100 + $staffSalary * 100 + $otherInput * 100 - $feeDataByWid[$wid]['logistics_in']) / ($feeDataByWid[$wid]['price_total'] - $feeDataByWid[$wid]['refund']) * 100, 2);
            $totalLogisticsScaleWithPrivilege = round(($feeDataByWid[$wid]['logistics_out'] + $fixedInput * 100 + $staffSalary * 100 + $otherInput * 100 + $feeDataByWid[$wid]['privilege'] - $feeDataByWid[$wid]['logistics_in']) / ($feeDataByWid[$wid]['price_total'] - $feeDataByWid[$wid]['refund']) * 100, 2);
            $totalFee = ($feeDataByWid[$wid]['logistics_out'] + $fixedInput * 100 + $staffSalary * 100 + $otherInput * 100 + $feeDataByWid[$wid]['privilege'] + $checkOutDataByWid[$wid] - $feeDataByWid[$wid]['logistics_in']);
            $totalLogisticsScaleWithPrivilegeCheckout = round($totalFee / ($feeDataByWid[$wid]['price_total'] - $feeDataByWid[$wid]['refund']) * 100, 2);
            $this->data[$wid] = array(
                'wid' => $wid,
                'wname' => $wname,
                'price_total' => round($feeDataByWid[$wid]['price_total'] / 100, 2),
                'gross_profit' => $grossRate,
                'refund' => round($feeDataByWid[$wid]['refund'] / 100, 2),
                'logistics_in' => round($feeDataByWid[$wid]['logistics_in'] / 100, 2),
                'logistics_out' => round($feeDataByWid[$wid]['logistics_out'] / 100, 2),
                'logistics_subsidy' => round($logisticsSubsidy / 100, 2),
                'gross_logistics_scale' => $grossLogisticsFeePercent,
                'real_logistics_scale' => $realLogisticsFeePercent,
                'fixed_input' => $fixedInput,
                'staff_salary' => $staffSalary,
                'other_input' => $otherInput,
                'total_logistics_scale' => $totalLogisticsScale,
                'total_logistics_scale_with_privilege' => $totalLogisticsScaleWithPrivilege,
                'total_logistics_scale_with_privilege_checkout' => $totalLogisticsScaleWithPrivilegeCheckout,
                'privilege' => round($feeDataByWid[$wid]['privilege'] / 100, 2),
                'check_out' => round($checkOutDataByWid[$wid] / 100, 2),
                'total_fee' => round($totalFee / 100, 2),
            );

            $this->totalData['price_total'] += $feeDataByWid[$wid]['price_total'];
            $this->totalData['refund'] += $feeDataByWid[$wid]['refund'];
            $this->totalData['logistics_in'] += $feeDataByWid[$wid]['logistics_in'];
            $this->totalData['logistics_out'] += $feeDataByWid[$wid]['logistics_out'];
            $this->totalData['fixed_input'] += $fixedInput;
            $this->totalData['staff_salary'] += $staffSalary;
            $this->totalData['other_input'] += $otherInput;
            $this->totalData['cost'] += $feeDataByWid[$wid]['cost'];
            $this->totalData['privilege'] += $feeDataByWid[$wid]['privilege'];
            $this->totalData['check_out'] += $checkOutDataByWid[$wid];
        }

        $totalGrossRate = round(($this->totalData['price_total'] - $this->totalData['cost']) / $this->totalData['price_total'] * 100, 2);
        $logisticsSubsidy = $this->totalData['logistics_out'] - $this->totalData['logistics_in'];
        $grossLogisticsFeePercent = round($this->totalData['logistics_out'] / ($this->totalData['price_total'] - $this->totalData['refund']) * 100, 2);
        $realLogisticsFeePercent = round(($this->totalData['logistics_out'] - $this->totalData['logistics_in']) / ($this->totalData['price_total'] - $this->totalData['refund']) * 100, 2);
        $totalLogisticsScale = round(($this->totalData['logistics_out'] + $this->totalData['fixed_input'] * 100 + $this->totalData['staff_salary'] * 100 + $this->totalData['other_input'] * 100 - $this->totalData['logistics_in']) / ($this->totalData['price_total'] - $this->totalData['refund']) * 100, 2);
        $totalLogisticScaleWithPrivilege = round(($this->totalData['logistics_out'] + $this->totalData['fixed_input'] * 100 + $this->totalData['staff_salary'] * 100 + $this->totalData['other_input'] * 100 + $this->totalData['privilege'] - $this->totalData['logistics_in']) / ($this->totalData['price_total'] - $this->totalData['refund']) * 100, 2);
        $this->totalData['total_fee'] = ($this->totalData['logistics_out'] + $this->totalData['fixed_input'] * 100 + $this->totalData['staff_salary'] * 100 + $this->totalData['other_input'] * 100 + $this->totalData['privilege'] + $this->totalData['check_out'] - $this->totalData['logistics_in']);
        $totalLogisticScaleWithPrivilegeCheckout = round($this->totalData['total_fee'] / ($this->totalData['price_total'] - $this->totalData['refund']) * 100, 2);
        $this->totalData['price_total'] = round($this->totalData['price_total'] / 100, 2);
        $this->totalData['refund'] = round($this->totalData['refund'] / 100, 2);
        $this->totalData['logistics_in'] = round($this->totalData['logistics_in'] / 100, 2);
        $this->totalData['logistics_out'] = round($this->totalData['logistics_out'] / 100, 2);
        $this->totalData['privilege'] = round($this->totalData['privilege'] / 100, 2);
        $this->totalData['check_out'] = round($this->totalData['check_out'] / 100, 2);
        $this->totalData['gross_profit'] = $totalGrossRate;
        $this->totalData['gross_logistics_scale'] = $grossLogisticsFeePercent;
        $this->totalData['real_logistics_scale'] = $realLogisticsFeePercent;
        $this->totalData['total_logistics_scale'] = $totalLogisticsScale;
        $this->totalData['logistics_subsidy'] = round($logisticsSubsidy / 100, 2);
        $this->totalData['total_logistics_scale_with_privilege'] = $totalLogisticScaleWithPrivilege;
        $this->totalData['total_logistics_scale_with_privilege_checkout'] = $totalLogisticScaleWithPrivilegeCheckout;
        $this->totalData['total_fee'] = $this->totalData['total_fee'] / 100;

        $this->addFootJs(array(
            'js/apps/warehouse_fee.js'
                         ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('data', $this->data);
        $this->smarty->assign('from_date', $this->fromDate);
        $this->smarty->assign('end_date', $this->endDate);
        $this->smarty->assign('total_data', $this->totalData);
        $this->smarty->assign('from_date_real', $this->fromDateReal);
        $this->smarty->assign('end_date_real', $this->endDateReal);
        $this->smarty->assign('warehouse_fee', $this->warehouseFee);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);

        $this->smarty->display('statistics/warehouse_fee_scale.html');
    }
}

$app = new App('pri');
$app->run();
