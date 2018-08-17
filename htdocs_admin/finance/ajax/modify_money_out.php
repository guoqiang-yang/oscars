<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $oldSource;
    private $newSource;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->oldSource = Tool_Input::clean('r', 'old_source', TYPE_UINT);
        $this->newSource = Tool_Input::clean('r', 'new_source', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->id) || empty($this->newSource) || ($this->newSource == $this->oldSource))
        {
            throw new Exception('款项来源错误，请核对！');
        }
    }

    protected function main()
    {
        $st = Finance_Api::modifySupplierPaidSource($this->id, $this->newSource, $this->oldSource);

        if ($st < 0)
        {
            throw new Exception('修改失败！');
        }
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