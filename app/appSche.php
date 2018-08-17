<?php

$now = time();
$ymd = date('Y-m-d', $now + 10);
$day = date('d', $now + 10);
$hour = date('H', $now + 10);
$minute = date('i', $now + 10);
$week = date('D', $now + 10);
$weekord = date('N', $now + 10);
$yesterday = date('Y-m-d', $now - 86400 + 10);

$phpcmd = exec('which php');
$nohupcmd = exec('which nohup');

if (chdir('coupon'))
{
    $cmd = "$phpcmd send_sms.php >> send_sms.log &";
    echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
    system($cmd);

    //优惠券过期提醒
    if ('16' == $hour && '00' == $minute)
    {
        $cmd = "$phpcmd remind_coupon.php >> remind_coupon.log &";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }

    chdir('..');
}

if (chdir('crontab'))
{
    if ('02' == $hour && '00' == $minute)
    {
        $cmd = "$phpcmd update_customer_payment_date.php >> update_payment.log &";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }

//    if ('03' == $hour && '00' == $minute)
//    {
//        $cmd = "$phpcmd update_product_cost.php >> update_product_cost.log &";
//        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
//        system($cmd);
//    }

    //客户购买商品的关系表 -增量更新
    if ('02' == $hour && '10' == $minute)
    {
        $cmd = "$phpcmd update_user_product_frequency.php >> update_user_product_frequency.log &";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }
    
    //商品的销售频次 -增量更新
    if ('02' == $hour && '15' == $minute)
    {
        $cmd = "$phpcmd update_product_sale_frequency.php >> update_product_sale_frequency.php &";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }

    if ('02' == $hour && '01' == $minute)
    {
        $cmd = "$phpcmd clear_driver_check_in.php >> clear_driver_check_in.log &";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }

    if (intval($minute) % 2 == 0)
    {
        $cmd = "$phpcmd driver_queue_revert.php >> driver_queue_revert.log &";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }

    // 每个月1号，跑销售优惠初始数据
    if ('01' == $day && '01' == $hour && '04' == $minute)
    {
        $cmd = "$phpcmd sale_privilege_init_by_month.php >> sale_privilege_init_by_month.log";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }

    if (chdir('statistics'))
    {
        if ('02' == $hour && '30' == $minute)
        {
            $cmd = "$phpcmd stat_common_per_day.php base >> stat_common_per_day.log &";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }
        if ('02' == $hour && '50' == $minute)
        {
            $cmd = "$phpcmd stat_warehouse_cost_per_day.php >> stat_warehouse_cost_per_day.log &";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }
        if ('03' == $hour && '30' == $minute)
        {
            $cmd = "$phpcmd stat_sku_per_day.php oneday >> stat_sku_per_day.log";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
            sleep(5);

            $cmd = "$phpcmd stat_sku_per_day.php cate >> stat_sku_per_day.log&";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }

        chdir('..');
    }
    
    // 积分
    if (chdir('cpoint'))
    {
        // 每个月1号，跑User等级
        if ('01' == $day && '01' == $hour && '02' == $minute)
        {
            $cmd = "$phpcmd update_user_grade.php >> update_user_grade.log";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }
        
        // 每天跑User解冻积分
        if ('01' == $hour && '12' == $minute)
        {
            $cmd = "$phpcmd unfreeze_user_point.php >> unfreeze_user_point.log";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }
        
        chdir('..');
    }

    //贴息
    if (chdir('finance'))
    {
//        // 计算贴息脚本
//        if ('01' == $hour && '40' == $minute)
//        {
//            $cmd = "$phpcmd finance_accrual.php >> finance_accrual.log";
//            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
//            system($cmd);
//        }
//
//        // 每天跑清模式商家结算（10天一周期）
//        if ('01' == $hour && '35' == $minute)
//        {
//            $cmd = "$phpcmd seller_bill.php >> seller_bill.log";
//            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
//            system($cmd);
//        }

        chdir('..');
    }

    //经销商
    if (chdir('agent'))
    {
        //日结
//        if ('02' == $hour && '05' == $minute)
//        {
//            $cmd = "$phpcmd agent_bill_day.php >> agent_bill_day.log";
//            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
//            system($cmd);
//        }
        //月结返点
//        if('16' == $day && '02' == $hour && '15' == $minute)
//        {
//            $cmd = "$phpcmd agent_bill_cashback.php >> agent_bill_cashback.log";
//            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
//            system($cmd);
//        }
        chdir('..');
    }

    //成本
    if (chdir('cost'))
    {
        //更新成本为零的商品
        if (0 == $minute % 20) {
            $cmd = "$phpcmd update_product_zero_cost.php >> update_product_zero_cost.log";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }
        if ('03' == $hour && '00' == $minute)
        {
            $cmd = "$phpcmd update_product_cost.php >> update_product_cost_online.log &";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }
        
        chdir('..');
    }
    
    //订单
    if (chdir('order'))
    {
        // 自动取消订单（24小时未确认）
        if (0==$hour%2 && $minute=='00')
        {
            $cmd = "$phpcmd auto_cancel_order.php >> auto_cancel_order.log";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }
        //老客户邀请新客户首单满599并出库15天后订单送50元券
        if('02' == $hour && '15' == $minute)
        {
            $cmd = "$phpcmd friend_invite_send_coupon.php >> friend_invite_send_coupon.log";
            echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
            system($cmd);
        }
        
        chdir('..');
    }
    
    chdir('..');
}

if (chdir('crm'))
{
    if ($hour >= 7 && $hour <= 23 && '00' == $minute)
    {
        $cmd = "$phpcmd salesman_performance.php 31 >> salesman_performance.log &";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }
    if ($hour == '02' && '00' == $minute)
    {
        $cmd = "$phpcmd salesman_performance.php 60 >> salesman_performance.log &";
        echo '[', $ymd, ' ', $hour, ':', $minute, '] ', $cmd, "\n";
        system($cmd);
    }

    chdir('..');
}