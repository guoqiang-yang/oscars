<?php
include_once ('../../global.php');

class App extends App_Cli
{
	protected function main()
	{
		$this->_test();
	}

	private function _test()
	{
		$table = 't_order_product as A inner join t_order as B on A.oid = B.oid';
		$where = 'A.status=0 and A.rid=0 and B.status=0 and B.step>5';
		$fields = array('A.pid','A.num','A.price','A.oid','B.wid','B.cid','B.saler_suid', 'B.delivery_date', 'B.freight', 'B.customer_carriage', 'B.privilege', 'B.price');
		$res = $this->one->setDBMode()->select($table, $fields, $where, '', 0, 1);
		print_r($res);exit;

		$dao = new Data_Dao('t_customer');

		//primary key查询
		$c = $dao->get(6001);
		printf("%s\n", $c['address']);

		//primary keys查询
		$cs = $dao->getList(array(6001,6002));
		printf("%d\n", count($cs));

		//设置fields查询
		$cs = $dao->setFields(array('cid','name'))->getList(array(6001,6002));
		printf("%d\n", count($cs));
		var_dump($cs);

		//条件查询 & 排序 & 翻页
		$cs = $dao->order('cid', 'desc')->limit(2,5)
			->setFields(array('cid','name', 'order_num'))
			->getListWhere('order_num=%d', 2);
		printf("%d\n", count($cs));
		var_dump($cs);
		$total = $dao->getTotal();
		printf("%d\n", $total);
		return;

		//删除修改
		$dao->delete(6001);
		$c = $dao->get(6001);
		printf("%s\n", $c['status']);

		$dao->update(6001, array('status'=>0));
		$c = $dao->get(6001);
		printf("%s\n", $c['status']);
	}
}

$app = new App();
$app->run();
