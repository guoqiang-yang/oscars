<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/11/1
 * Time: 下午6:51
 */
include_once('../../../global.php');

//保存服务政策？暂时没用
exit;

class App extends App_Admin_Ajax
{
    private $fid;
    private $info;
    private $method;
    private $sort;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->method = Tool_Input::clean('r', 'method', TYPE_STR);
        $this->sort = Tool_Input::clean('r', 's_sort', TYPE_UINT);
        $this->info = array(
            'feature1'  => Tool_Input::clean('r', 'feature1', TYPE_STR),
            'feature2'  => Tool_Input::clean('r', 'feature2', TYPE_STR),
            'feature3'  => Tool_Input::clean('r', 'feature3', TYPE_STR),
            'city' => Tool_Input::clean('r', 'city', TYPE_STR),
            'sort' => Tool_Input::clean('r', 'sort', TYPE_UINT),
            'start_time' => Tool_Input::clean('r', 'start_time', TYPE_STR),
            'end_time' => Tool_Input::clean('r', 'end_time', TYPE_STR),
        );
    }

    protected function main()
    {
        $this->info['city'] = rtrim($this->info['city'], ',');
        $this->info['start_time'] = str_replace('T', ' ', $this->info['start_time']).':00';
        $this->info['end_time'] = str_replace('T', ' ', $this->info['end_time']).':00';
        if (empty($this->sid))
        {
            $this->info['online'] = 1;
            $this->id = Activity_Service_Feature_Api::add($this->info);
        }
        else
        {
            if (!empty($this->method))
            {
                $item = Activity_Service_Feature_Api::getOne($this->sid);

                $conf = array(
                    'date' => date('Y-m-d H:i:s'),
                    'online' => 0,
                );
                $city = explode(',', $item['city']);
                $data = Activity_Service_Feature_Api::getList($conf, 0, 1000);
                $num = 0;
                foreach ($data['list'] as $v)
                {
                    $v_city = explode(',', $v['city']);
                    if (count(array_intersect($city, $v_city)) != 0 && !($v['start_time'] > $item['end_time'] || $v['end_time'] <  $item['start_time']))
                    {
                        $num++;
                    }
                }
                if ($num > 4)
                {
                    throw new Exception('同一时间、城市的在线快捷入口数量不能超过4个');
                }

                switch ($this->method)
                {
                    case 'up':
                        $this->id = Activity_Service_Feature_Api::update($this->sid, array('online' => 0));
                        break;
                    case 'down':
                        $this->id = Activity_Service_Feature_Api::update($this->sid, array('online' => 1));
                        break;
                }
            }
            if (!empty($this->sort))
            {
                $this->id = Activity_Service_Feature_Api::update($this->sid, array('sort' => $this->sort));
            }
            if (empty($this->method) && empty($this->sort))
            {
                $this->id = Activity_Service_Feature_Api::update($this->sid, $this->info);
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