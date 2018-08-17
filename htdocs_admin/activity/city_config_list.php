<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $list;

	protected function main()
	{
        $city_list = Conf_City::$CITY;
        $aa = new Data_Dao('t_article');
        $acb = new Activity_City_Brand();
        $ap = new Activity_Picture();
        $afs = new Activity_Floor_Sale_Func();
        foreach ($city_list as $city_id => $name ){
            $_info = array(
                'city_id' => $city_id,
                'name' => $name,
            );
            $_where = sprintf('(find_in_set(%d,city_ids) or city_ids=1) and article_type!=%d', $city_id, Conf_base::TYPE_OPERATION_ACT);
            $_articleList = $aa->limit(0,0)->getListWhere($_where);
            foreach ($_articleList as $item)
            {
                !isset($_info['article'][$item['article_type']]) && $_info['article'][$item['article_type']]= $item['aid'];
            }
            $_info['brand'] = $acb->getByCity($city_id);
            $_info['banner'] = $ap->getListByWhere(sprintf('find_in_set(%d,city_id) and start_time<="%s" and end_time>="%s"', $city_id, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), 0, 0));
            $_info['floor'] = $afs->getListByWhere(sprintf('type=1 and start_time<="%s" and end_time>="%s" and fid in(select fid from t_floor_activity where (find_in_set(%d,city) or find_in_set(1,city)))', date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $city_id));
            $this->list[] = $_info;
        }
	}

	protected function outputBody()
	{
		$this->smarty->assign('list', $this->list);
        $this->smarty->assign('policy_types', Conf_Base::articlePolicyType());
		$this->smarty->display('activity/city_config_list.html');
	}
}

$app = new App('pri');
$app->run();

