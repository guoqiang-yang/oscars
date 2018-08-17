<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/6/20
 * Time: 下午4:32
 */

class Logistics_Fee_Qingdao
{
    public function calFreight($products, $district)
    {
        $productsAmountInfo = Order_Helper::calProductTypeAndAmount($products);
        $weight = Shop_Helper::calVolAndWeight4ProductList($products);
        $feeNum = ceil($weight['w']/1000); //商品吨数
        if($productsAmountInfo['sand_amount'] > 0)
        {
            //含沙石砖类
            if(in_array($district,array(370202,370203,370212,370213,370214,370215)))
            {
                //市南区 市北区 崂山区 李沧区 城阳区 即墨区
                if($productsAmountInfo['common_amount'] >= 999*100)
                {
                    $feeNum -= floor($productsAmountInfo['common_amount']/99900);
                }
                if($feeNum<0)
                {
                    $feeNum = 0;
                }
                $freight = $feeNum * 49;
            }
        }else{
            //不含沙石砖类
            if(in_array($district,array(370202,370203,370212,370213,370214,370215)))
            {
                //市南区 市北区 崂山区 李沧区 城阳区 即墨区
                if($productsAmountInfo['common_amount'] >= 999*100)
                {
                    $freight = 0;
                }else{
                    $freight = 49;
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

        $fee < 0 && $fee = 0;

        return $fee;
    }
}