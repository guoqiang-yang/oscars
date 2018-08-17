<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $type;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
    }

    protected function main()
    {
        $status = Conf_Base::STATUS_OFFLINE;
        if ($this->type == 'online')
        {
            $status = Conf_Base::STATUS_NORMAL;
        }

        Shop_Api::switchProductOnlineStatus($this->pid, $status);
        if ($this->type != 'online')
        {
            Activity_Floor_Sale_Api::offLine($this->pid);
            Activity_Flash_Sale_Api::offLine($this->pid);
        }
        $flag = TRUE;
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
        {
            $flag = FALSE;
        }
        $productInfo = Shop_Api::getProductInfo($this->pid);
       
        if ($this->type == 'online' && $productInfo['product']['cost']==0)
        {
            throw new Exception('上架失败：商品成本为0，请联系产品运营小伙伴处理');
        }
        
        Shop_Api::setTopCategoryProduct($productInfo['product']['city_id'], $flag);
        Shop_Api::setTopCategoryBrandProduct($productInfo['product']['city_id'], $flag);
        $changed = '修改为'. ($this->type == 'online'? '上架':'下架') .'状态';
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->pid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_PRODUCT,
            'action_type' => 2,
            'params' => json_encode(array('pid' => $this->pid, 'changed' => $changed)),
        );
        Admin_Common_Api::addAminLog($info);
    }

    protected function outputPage()
    {
        $result = array('pid' => $this->pid);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();

