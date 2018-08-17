<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/11/6
 * Time: 16:47
 */
class Logistics_Fee_Beijing
{
    public function calFreight($products, $city, $district, $area)
    {
        $productsAmountInfo = Order_Helper::calProductTypeAndAmount($products);
        $price = $productsAmountInfo['common_amount'];
        $minPrice = ($productsAmountInfo['sand_amount'] > 0) ? 800 : 599;

        $role = Conf_Area::getFreightFeeType($city, $district, $area);
        if ($role !== 0)
        {
            //－商品总额800元，运费0
            //－商品总额不足800元，运费根据城市改变
            $price >= $minPrice * 100 ? $freight = 0 : $freight = Conf_Order::$ORDER_MIN_FREIGHT[$role];
            if ($role == Conf_Order::ORDER_MIN_FREIGHT_BEIJING)
            {
                $price >= $minPrice * 100 ? $freight = 0 : $freight = 39;
            }
            if ($role == Conf_Order::ORDER_MIN_FREIGHT_TIANJIN)
            {
                $price >= $minPrice * 100 ? $freight = 0 : $freight = 39;
            }
        }
        else
        {
            $freight = 0;
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
            if ($otherProductsPrice < 3000 * 100)
            {
                $fee = $carryFee + $otherProductCarryFee;
            }
            else
            {
                $fee = $carryFee;
            }

            $fee = $fee * $floorNum;
        }

        if ($sourceOid == 0 && $fee > 0 && $fee < Conf_Order::ORDER_MIN_CARRY_FEE * 100)
        {
            $fee = Conf_Order::ORDER_MIN_CARRY_FEE * 100;
        }

        $fee < 0 && $fee = 0;

        return $fee;
    }
}