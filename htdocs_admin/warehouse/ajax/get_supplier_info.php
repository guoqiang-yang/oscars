<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/5
 * Time: 下午10:57
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sid;
    private $supplier;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
    }

    protected function main()
    {
        $this->supplier = Warehouse_Api::getSupplier($this->sid);

        if (!empty($this->supplier['public_bank']))
        {
            $publicBank = explode(',', $this->supplier['public_bank']);
            $this->supplier['public_bank'] = $publicBank;
        }

    }

    protected function outputBody()
    {
        $res = array('supplier' => $this->supplier);

        $response = new Response_Ajax();
        $response->setContent($res);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();