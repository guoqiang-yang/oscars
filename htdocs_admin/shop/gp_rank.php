<?php

include_once ('../../global.php');

class App extends App_Cli
{
	protected function main()
	{

		echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"></head><body>';
		echo "<table><tr><th>商品</th><th>贡献毛利</th><th>总销量</th><th>卖价</th><th>成本</th><th>毛利</th></tr>";
		$fields = array('pid','sum(price*num)/100-sum(cost*num)/100 as gp, sum(num) as total_num');
		//$where = ' cost>0 and cost<price and status=0 and pid in (select pid from t_product where status in (0,4)) group by pid';
		$where = ' cost>0 and cost<price and status=0 and num>20 and pid  in (select pid from t_product where status in (0,4)) group by pid';
		$order = 'order by gp desc';
		$list = $this->one->select('t_order_product', $fields, $where, $order);
		$gpList = Tool_Array::list2Map($list['data'], 'pid');
		$pids = Tool_Array::getFields($gpList, 'pid');

		$where = array('sid' => $pids);

		$list = $this->one->select('t_product', array('*'), $where);
		$products = Tool_Array::list2Map($list['data'], 'pid');

		$list = $this->one->select('t_sku', array('*'), $where);
		$skus = Tool_Array::list2Map($list['data'], 'sid');


		foreach ($gpList as $item)
		{
			$pid = $item['pid'];
			$sku = $skus[$pid];
			$product = $products[$pid];
			if ($product['price']>0)
			{
				$profit = ($product['price']- $product['cost'])/$product['price']*100;
			}
			else
			{
				$profit = 0;
			}
			printf("<td><a href='http://sa.haocaisong.cn/shop/edit_product.php?pid=%d' target='_blank'>%s</a></td><td>￥%d</td><td>%d</td><td>￥%.2f</td><td>￥%.2f</td><td>￥%.2f%%</td></tr>",
				$pid, $sku['title'], $item['gp'], $item['total_num'], $product['price']/100, $product['cost']/100, $profit);
			echo "\n";
		}

		echo '</table></body></html>';
	}

}

$app = new App('pri');
$app->run();

