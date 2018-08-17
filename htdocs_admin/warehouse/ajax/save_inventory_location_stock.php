<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
    }

    protected function main()
    {
        $notDealConf = array(
            'pid' => $this->pid,
            'is_deal' => 2,
        );
        $notDealProducts = Warehouse_Api::getDiffProductList($notDealConf);

        if (!empty($notDealProducts))
        {
            throw new Exception('还有未处理的差异商品！');
        }

        // 将待处理的任务插入到队列
        $isDealConf = array(
            'pid' => $this->pid,
            'is_deal' => 1,
        );

        $queue = new Data_Queue();
        $isDealProducts = Warehouse_Api::getDiffProductList($isDealConf);

        if (!empty($isDealProducts))
        {
            $isDealConf['suid'] = $this->_uid;
            $queue->enqueue(Queue_Base::Queue_Type_Inventory, $isDealConf);
        }
//
//        foreach ($isDealProducts as $product)
//        {
//            Warehouse_Location_Api::saveCheckLocation($product['sid'], $product['location'], $product['wid'],
//                $product['last_num'], $product['note'], $this->_uid, 5, -1, true);
//        }

        $data = array(
            'is_update' => 1
        );
        Warehouse_Api::updateInventoryPlan($this->pid, $data);
    }

    protected function outputBody()
    {
        $result = array('st' => 1);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }

}

$app = new App();
$app->run();