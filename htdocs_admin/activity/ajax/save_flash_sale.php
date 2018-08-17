<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/13
 * Time: 上午9:25
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $info;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_activity_flash');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->fid = Tool_Input::clean('r', 'fid', TYPE_UINT);
        $this->method = Tool_Input::clean('r', 'method', TYPE_STR);
        if (isset($_REQUEST['s_sort'])) {
            $this->sort = Tool_Input::clean('r', 's_sort', TYPE_UINT);
        }
        $this->info = array(
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'fid' => $this->fid,
            'start_time' => Tool_Input::clean('r', 'start_time', TYPE_STR),
            'end_time' => Tool_Input::clean('r', 'end_time', TYPE_STR),
            'cover' => Tool_Input::clean('r', 'cover', TYPE_STR),
            'sale_num' => Tool_Input::clean('r', 'sale_num', TYPE_UINT),
            'limit_count' => Tool_Input::clean('r', 'limit_num', TYPE_STR),
            'platform' => Tool_Input::clean('r', 'platform', TYPE_UINT),
            'sort' => Tool_Input::clean('r', 'sort', TYPE_UINT),
            'sale_price' => Tool_Input::clean('r', 'price', TYPE_STR),
        );
    }
    protected function checkPara()
    {
        if (empty($this->method) && !isset($this->sort)) {
            if (empty($this->info['pid']) || empty($this->info['sale_price']) || empty($this->info['start_time']) ||empty($this->info['end_time'])) {
                throw new Exception('参数不合法');
            }
            if ($this->info['start_time'] >= $this->info['end_time']) {
                throw new Exception('活动开始时间不能小于结束时间');
            }
            if (empty($this->id)) {
                if ($this->info['start_time'] <= date('Y-m-d H:i:s')) {
                    throw new Exception('活动开始时间不能小于当前时间');
                }
            }

            if (empty($this->fid) && empty($this->id)) {
                throw new Exception('参数不合法');
            }
        }

    }
    protected function main()
    {
        if (!empty($this->fid)) {
            $activity = Activity_Flash_Api::getOne($this->fid);
            $this->info['platform'] = $activity['platform'];
        }
        $this->info['start_time'] = str_replace('T', ' ', $this->info['start_time']).':00';
        $this->info['end_time'] = str_replace('T', ' ', $this->info['end_time']).':00';
        if (empty($this->id) && empty($this->method)) {
            $this->sid = Activity_Flash_Sale_Api::add($this->info);
        }else if (!empty($this->id) && (empty($this->method) && !isset($this->sort))){
            $this->sid = Activity_Flash_Sale_Api::update($this->id,$this->info);
        }else if (!empty($this->id) && !empty($this->method)) {
            if ($this->method == 'up') {
                $info = Activity_Flash_Sale_Api::getOne($this->id);
                $date = date('Y-m-d H:i:s');
                if ($date <= $info['start_time']) {
                    throw new Exception('该商品上架时间晚于当前时间，若想立即上架该商品，请手动修改上架时间！');
                }
                $this->sid = Activity_Flash_Sale_Api::update($this->id,array('online' => 0));
            }else if ($this->method == 'down') {
                $this->sid = Activity_Flash_Sale_Api::update($this->id,array('online' => 1));
            }

        }
        if (!empty($this->id) && isset($this->sort) && empty($this->method)) {
            $this->sid = Activity_Flash_Sale_Api::update($this->id,array('sort' => $this->sort));
        }
    }

    protected function outputPage()
    {
        $result = array('id' => $this->sid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();
