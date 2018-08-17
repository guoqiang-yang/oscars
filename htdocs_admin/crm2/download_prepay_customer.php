<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    protected function main()
    {
        $ret = Finance_Api::statPrebuyCustomer(0, 0);
        $sysLevels = Conf_User::$Customer_Sys_Level_Descs;

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . 'prepay-customer-' . date('Ymd') . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        $head = array(
            'cid',
            '客户类型',
            '客户姓名',
            '客户手机号',
            '销售专员',
            '预存次数',
            '总预存金额',
            '剩余金额	'
        );
        Data_Csv::send($head);

        foreach ($ret['customer'] as $customer)
        {
            if ($customer['_customer']['identity'] == 1)
            {
                if ($customer['_customer']['name'] == $customer['_customer']['all_user_names'])
                {
                    $name = $customer['_customer']['all_user_names'];
                }
                else
                {
                    $name = $customer['_customer']['name'];
                    if ($customer['_customer']['all_user_names'] != 'HC_工长')
                    {
                        $name .= '-' . $customer['_customer']['all_user_names'];
                    }
                }
            }
            else
            {
                $name = $customer['_customer']['name'];
            }

            $mobiles = $customer['_customer']['all_user_mobiles'];
            $arr = array(
                $customer['cid'],
                $sysLevels[$customer['_customer']['level_for_sys']],
                $name,
                str_replace(',', '、',mb_substr($mobiles, 0, 59)),
                $customer['_saler']['name'],
                $customer['times'],
                $customer['sum_prices']/10/10,
                $customer['_customer']['account_amount']/10/10,
            );
            Data_Csv::send($arr);
        }
        exit;
    }
}

$app = new App();
$app->run();

