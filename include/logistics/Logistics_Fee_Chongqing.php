<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/11/6
 * Time: 16:47
 */
class Logistics_Fee_Chongqing
{
    public function calFreight($products, $area)
    {
        $productsAmountInfo = Order_Helper::calProductTypeAndAmount($products);
        $price = $productsAmountInfo['total'];
        $pids = Tool_Array::getFields($products, 'pid');
        $freight = 0;

        //绕城高速外，自己算
        if ($area == 2)
        {
            $freight = 0;
        }
        else
        {
            $hasSandBrick = false;
            $isSandCements = Shop_Api::isSandCementBrickByPids($pids);
            foreach ($isSandCements as $isSandCement)
            {
                if ($isSandCement == 1)
                {
                    $hasSandBrick = true;
                    break;
                }
            }

            if ($hasSandBrick)
            {
                $price = $productsAmountInfo['common_amount'];
                $num999 = floor($price / 999 / 100);
                $sp = new Shop_Product();
                $productList = $sp->getBulk($pids);
                $sids = Tool_Array::getFields($productList, 'sid');
                $ss = new Shop_Sku();
                $skus = $ss->getBulk($sids);
                $weight = 0;
                foreach ($products as $p)
                {
                    $pid = $p['pid'];
                    $product = $productList[$pid];
                    $sid = $product['sid'];
                    $sku = $skus[$sid];
                    $num = $p['num'];
                    $weight += $num * $sku['weight'];
                }

                $numDun = ceil($weight / 1000 / 1000);

                $freight = 49 * ($numDun - $num999);
            }
            else
            {
                $price = $productsAmountInfo['common_amount'];
                if ($price >= 799 * 100)
                {
                    $freight = 0;
                }
                else
                {
                    $freight = 49;
                }
            }
        }

        $freight < 0 && $freight = 0;

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
            if ($otherProductsPrice >= 1500 * 100)
            {
                $newFloorNum = $floorNum - 1;
                $fee = $otherProductCarryFee * $newFloorNum + $carryFee * $floorNum;
            }
            else
            {
                $fee = ($carryFee + $otherProductCarryFee) * $floorNum;
            }
        }

//        if ($sourceOid == 0 && $fee > 0 && $fee < Conf_Order::ORDER_CHENGDU_MIN_CARRY_FEE * 100)
//        {
//            $fee = Conf_Order::ORDER_CHENGDU_MIN_CARRY_FEE * 100;
//        }

        $fee < 0 && $fee = 0;

        return $fee;
    }
}