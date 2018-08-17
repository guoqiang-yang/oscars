<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/11/6
 * Time: 16:47
 */
class Logistics_Fee_Langfang
{
    public function calFreight($products, $cid, $communityId)
    {
        $productsAmountInfo = Order_Helper::calProductTypeAndAmount($products);
        $price = $productsAmountInfo['common_amount'];
        $minPrice = 999 * 100;
        $freight = 0;

        if ($cid > 0)
        {
            $customer = Crm2_Api::getCustomerInfo($cid, false, false);
            if ($customer['customer']['level_for_sys'] == Conf_User::CRM_SYS_LEVEL_VIP)
            {
                //vip客户和大于999的客户规则一样，所以这里不管订单金额多少，大于999即可
                $price = 999900;
            }
        }

        if ($communityId > 0)
        {
            $distance = Order_Community_Api::getDistanceByWid($communityId, Conf_Warehouse::WID_LF1);
            if ($price >= $minPrice)
            {
                if ($distance > 10 * 1000)
                {
                    $freight = (round($distance / 1000) - 10) * 5;
                }
                else
                {
                    $freight = 0;
                }
            }
            else
            {
                if ($price <= 0)
                {
                    $freight = 0;
                }
                else
                {
                    if ($distance > 10 * 1000)
                    {
                        $freight = round($distance / 1000) * 5;
                    }
                    else
                    {
                        $freight = 29;
                        if ($communityId > 0)
                        {
                            $communityInfo = Order_Community_Api::get($communityId);
                            $arr = array('弘德家园', '辉旭十九城邦', '香榭丽舍', '大学里', '荣盛花语城', '专家公寓', '凤凰城');
                            if (in_array($communityInfo['name'], $arr))
                            {
                                $freight = 10;
                            }
                        }
                    }
                }
            }
        }

        return $freight;
    }

    public function calCarryFee($products, $service, $floorNum, $sourceOid)
    {
        $carryFee = $carryFeeEle = 0;
        $otherProductsPrice = $otherProductCarryFee = $otherProductCarryFeeEle = 0;
        $sk = new Shop_Product();

        if (!empty($products))
        {
            // 按照pid获取是否为砂石砖
            $pids = array();
            foreach ($products as $p)
            {
                if (!empty($p['_package']))
                {
                    foreach ($p['_package'] as $pack)
                    {
                        $pids[] = $pack['pid'];
                    }
                }
                else
                {
                    $pids[] = $p['pid'];
                }
            }
            $productInfos = $sk->getBulk($pids);

            $isSandCementBrickByPids = Shop_Api::isSandCementBrickByPids($pids);
            foreach ($products as $product)
            {
                if (!empty($product['_package']))
                {
                    foreach ($product['_package'] as $pack)
                    {
                        $productInfo = $productInfos[$pack['pid']];
                        if ($isSandCementBrickByPids[$pack['pid']])
                        {
                            //砂石水泥类的正常算搬运费
                            $carryFee += $productInfo['carrier_fee'] * $pack['num'];
                            $carryFeeEle += $productInfo['carrier_fee_ele'] * $pack['num'];
                        }
                        else
                        {
                            //其他的要累计价格，看看价格是多少，顺便算一下楼梯上楼和电梯上楼的搬运费是多少
                            //然后根据其他建材的总金额判断是不是要加上这部分的搬运费
                            $otherProductsPrice += $pack['price'] * $pack['num'];
                            $otherProductCarryFee += $productInfo['carrier_fee'] * $pack['num'];
                            $otherProductCarryFeeEle += $productInfo['carrier_fee_ele'] * $pack['num'];
                        }
                    }
                }
                else
                {
                    $productInfo = $productInfos[$product['pid']];
                    if ($isSandCementBrickByPids[$product['pid']])
                    {
                        //砂石水泥类的正常算搬运费
                        $carryFee += $productInfo['carrier_fee'] * $product['num'];
                        $carryFeeEle += $productInfo['carrier_fee_ele'] * $product['num'];
                    }
                    else
                    {
                        $price = $product['price'];
                        //其他的要累计价格，看看价格是多少，顺便算一下楼梯上楼和电梯上楼的搬运费是多少
                        //然后根据其他建材的总金额判断是不是要加上这部分的搬运费
                        $otherProductsPrice += $price * $product['num'];
                        $otherProductCarryFee += $productInfo['carrier_fee'] * $product['num'];
                        $otherProductCarryFeeEle += $productInfo['carrier_fee_ele'] * $product['num'];
                    }
                }
            }
        }

        $fee = 0;
        //电梯
        if ($service == 1)
        {
            if ($otherProductsPrice < 1500 * 100)
            {
                $fee = $carryFeeEle + $otherProductCarryFeeEle;
            }
            else
            {
                $fee = $carryFeeEle;
            }
        }
        else if ($service == 2)
        {
            $newFloorNum = $floorNum;
            if ($otherProductsPrice >= 1500 * 100)
            {
                $newFloorNum = $floorNum - 1;
            }
            $fee = $carryFee * $floorNum + $otherProductCarryFee * $newFloorNum;
        }

        if ($sourceOid == 0 && $fee > 0 && $fee < Conf_Order::ORDER_MIN_CARRY_FEE * 100)
        {
            $fee = 20 * 100;
        }

        $fee < 0 && $fee = 0;

        return $fee;
    }
}