<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/11
 * Time: 下午5:05
 */
class Crm2_User_Fb_Api extends Base_Api
{
    public static function add($info)
    {
        $aa = new Crm2_User_Fb();

        $info['content'] = addslashes($info['content']);

        return $aa->add($info);
    }

    public static function getList($searConf, $start=0, $num=20)
    {
        $aa = new Crm2_User_Fb();
        $data = $aa->getList($searConf, $start, $num);
        if ($data['total'] <= 0)
        {
            return $data;
        }

        $list = $data['list'];
        $total = $data['total'];
        foreach ($list as &$item ) {
            $item['_content'] = mb_substr($item['content'],0,50,'utf-8');
            $item['_solve'] = mb_substr($item['solve'],0,50,'utf-8');
            if (mb_strlen($item['content'], 'utf-8') > 50)
            {
                $item['_content'] .= '...';
            }
            if (mb_strlen($item['solve'], 'utf-8') >50)
            {
                $item['_solve'] .= '...';
            }
        }

        if (empty($list))
        {
            return $list;
        }
        return array('list' => $list, 'total' => $total);
    }
    public static function update($id, $update, $change = array()){
        $aa = new Crm2_User_Fb();

        $update['solve'] = addslashes($update['solve']);

        return $aa->update($id, $update, $change);
    }
    public static function getOne($pk)
    {
        $aa = new Crm2_User_Fb();
        $data = $aa->get($pk);
        return $data;
    }

}