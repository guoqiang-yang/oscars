<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/19
 * Time: 上午9:18
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $fid;
    private $info;
    private $method;
    private $sort;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_floor_activity');
    }

    protected function getPara()
    {
        $this->fid = Tool_Input::clean('r', 'fid', TYPE_UINT);
        $this->method = Tool_Input::clean('r', 'method', TYPE_STR);
        $this->sort = Tool_Input::clean('r', 'f_sort', TYPE_UINT);
        $this->data_city = Tool_Input::clean('r', 'data_city', TYPE_STR);
        $this->data_type= Tool_Input::clean('r', 'data_type', TYPE_UINT);
        $this->info = array(
            'city' => Tool_Input::clean('r', 'city', TYPE_STR),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'sort' => Tool_Input::clean('r', 'sort', TYPE_UINT),
        );
    }
    protected function checkPara()
    {
        if (empty($this->method) && empty($this->sort)) {
            if (empty($this->info['city']) || empty($this->info['type'])) {
                throw new Exception('参数不合法');
            }
        }
    }
    protected function main()
    {
        $this->info['city'] = rtrim($this->info['city'],',');
        if (empty($this->fid) && empty($this->method)) {
            $this->info['online'] = 1;
            $this->id = Activity_Floor_Api::add($this->info);
        }else if (!empty($this->fid) && (empty($this->method) && empty($this->sort))){
            $this->info['online'] = 1;
            $this->id = Activity_Floor_Api::update($this->fid,$this->info);
        }else if (!empty($this->fid) && !empty($this->method)) {
            if ($this->method == 'up') {
                //查验同一城市是否已有该类型的楼层活动
                $data = Activity_Floor_Api::getList(array('online' => 0,'type' => $this->data_type));
                if ($data['total'] >= 1) {
                    foreach ($data['list'] as $key => $value ) {
                        if ($key != $this->fid) {
                            $city = explode(',', $value['city']);
                            $data_city = explode(',', $this->data_city);
                            foreach ($city as $v) {
                                foreach ($data_city as $vv) {
                                    if ($v == $vv) {
                                        throw new Exception('同一城市(包含全部城市)不能同时开展两个及以上相同类型的活动');
                                    }
                                }
                            }
                        }
                    }
                }
                //查验该楼层活动是否有大图，小图是否小于三个
                $date = date('Y-m-d H:i:s');
                $products = Activity_Floor_Sale_Api::getList(array('online' => 0,'date' => $date,'fid' => $this->fid));
                $has_big = 0;
                $pic_count = 0;
                foreach ($products['list'] as  $product ) {
                    if ($product['type'] == 1) {
                        $has_big++;
                    }else {
                        $pic_count++;
                    }
                }
                if ($has_big < 1) {
                    throw new Exception('楼层活动必须有在线大图');
                }
                if ($pic_count < 3) {
                    throw new Exception('楼层活动在线小图数量不能小于3个');

                }

                $this->id = Activity_Floor_Api::update($this->fid, array('online' => 0));
            } else if ($this->method == 'down') {
                $this->id = Activity_Floor_Api::update($this->fid, array('online' => 1));
            }
        }
        if (!empty($this->fid) && !empty($this->sort)) {
            $this->id = Activity_Floor_Api::update($this->fid,array('sort' => $this->sort));
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