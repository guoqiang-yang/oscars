<?php

include_once('../../global.php');

class App extends App_Cli
{
    protected function main()
    {

        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"></head><body>';

        $where = 'sid in (select sid from t_product where status in (0) and (cost=0 or cost>=price))';
        $order = 'order by title';

        $list = $this->one->select('t_sku', array('*'), $where, $order);

        foreach ($list['data'] as $item)
        {
            printf("<a href='http://sa.haocaisong.cn/shop/edit_product.php?pid=%d' target='_blank'>%s</a><br/>", $item['sid'], $item['title']);
            echo "\n";
        }

        echo '</body></html>';
    }
}

$app = new App('pri');
$app->run();

