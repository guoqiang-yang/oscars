<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/14
 * Time: 下午2:01
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
        $this->method = Tool_Input::clean('r', 'method', TYPE_STR);
        $this->info = array(
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'platform' => Tool_Input::clean('r', 'platform', TYPE_UINT),
            'city' => Tool_Input::clean('r', 'city', TYPE_STR),
            'rule' => Tool_Input::clean('r', 'rule', TYPE_STR),
            'start_time' => Tool_Input::clean('r', 'start_time', TYPE_STR),
            'end_time' => Tool_Input::clean('r', 'end_time', TYPE_STR),
        );
    }
    protected function checkPara()
    {
        if (empty($this->method) && empty($this->id)) {
            if (empty($this->info['name']) || empty($this->info['type']) || empty($this->info['platform']) || empty($this->info['city']) || empty($this->info['rule']) || empty($this->info['start_time']) ||empty($this->info['end_time'])) {
                throw new Exception('参数不合法');
            }
            if ($this->info['start_time'] >= $this->info['end_time']) {
                throw new Exception('活动开始时间不能大于结束时间');
            }
            if ($this->info['start_time'] <= date('Y-m-d H:i:s')) {
                throw new Exception('活动开始时间不能小于当前时间');
            }
        }
    }

    protected function main()
    {
        $this->info['start_time'] = str_replace('T', ' ', $this->info['start_time']).':00';
        $this->info['end_time'] = str_replace('T', ' ', $this->info['end_time']).':00';
        $this->info['city'] = rtrim($this->info['city'], ',');

        if (empty($this->id) && empty($this->method)) {
            $this->info['online'] = 1;
            $this->id = Activity_Flash_Api::add($this->info);
        }else if (!empty($this->id) && empty($this->method)){
            $this->info['online'] = 1;
            $this->id = Activity_Flash_Api::update($this->id,$this->info);
            Activity_Flash_Sale_Api::update($this->id,array('platform' => $this->info['platform']));
        }else if (!empty($this->id) && !empty($this->method)) {
            if ($this->method == 'up') {
                $flash = Activity_Flash_Api::getOne($this->id);
                $searchInfo = array(
                    'online' => 0,
                    'platform' => $flash['platform'],
                    //  'date' => date('Y-m-d H:i:s'),
                    'city' => $flash['city'],
                );
                if ($searchInfo['platform'] == 3) {
                    $searchInfo['platform'] = '';
                }
                $activity = Activity_Flash_Api::getList($searchInfo);
                if ($activity['platform'] == 3) {
                    $activity['platform'] = '';
                }
                if (!empty($activity['list'])) {
                    foreach ($activity['list'] as  $key => $value) {
                        if ($key != $this->id) {
                            if (!($value['start_time'] > $flash['end_time'] || $value['end_time'] < $flash['start_time'])) {
                                throw new Exception('同一时间、平台、城市只能有一个活动在线');
                            }
                        }
                    }
                }
                $date = date('Y-m-d H:i:s');
                $product = Activity_Flash_Sale_Api::getList(array('fid'=>$this->id,'online'=>0,'date'=>$date));
                if ($product['total'] < 3) {
                    throw new Exception('每个活动的在线商品数量不能小于3个');
                }
                if ($product['total'] > 8) {
                    throw new Exception('每个活动的在线商品不能不能大于8个');
                }
                $this->id = Activity_Flash_Api::update($this->id,array('online' => 0));
            }else if ($this->method == 'down') {
                $this->id = Activity_Flash_Api::update($this->id,array('online' => 1));
            }
        }
    }

    protected function outputPage()
    {
        $result = array('id' => $this->id);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();