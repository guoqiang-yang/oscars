<?php
include_once('../../../global.php');
/**
 * title 获取当前操作者所选城市所能操作的仓库
 * @author wangxuemin
 */
class App extends App_Admin_Ajax
{
    /* 城市ID */
    private $cityId;
    /* 仓库信息 */
    private $warehouses;
    /* 输出数据 */
    private $result = array();

    /**
     * (non-PHPdoc)
     * @see Base_App::getPara()
     */
    protected function getPara()
    {
        $this->cityId = Tool_Input::clean('r', 'cityId', TYPE_INT);
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::main()
     */
    protected function main()
    {
        $this->result['code'] = 0;
        $this->result['cityId'] = $this->cityId;
        $this->result['warehouses'] = array();
        if ($this->cityId == 0){
            $t = 0;
            $cities = explode(',', $this->_user['cities']);
            /* 取所能操作的全部仓库 */
            for ($i = 0; $i < count($cities); $i++){
                for ($s = 0; $s < count($this->_user['city_wid_map'][$cities[$i]]); $s++) {
                    $this->result['warehouses'][$t]['wid'] = $this->_user['city_wid_map'][$cities[$i]][$s];
                    $this->result['warehouses'][$t]['wname'] = Conf_Warehouse::getWarehouseName($this->_user['city_wid_map'][$cities[$i]][$s], false);
                    $t = $t + 1;
                }
            }
        } else {
            /* 取所选城市所能操作的仓库 */
            for ($i = 0; $i < count($this->_user['city_wid_map'][$this->cityId]); $i++){
                $this->result['warehouses'][$i]['wid'] = $this->_user['city_wid_map'][$this->cityId][$i];
                $this->result['warehouses'][$i]['wname'] = Conf_Warehouse::getWarehouseName($this->_user['city_wid_map'][$this->cityId][$i], false);
            }
        }    
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::outputBody()
     */
    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent($this->result);
        $response->send();
        exit;
    }
}
$app = new App('pub');
$app->run();