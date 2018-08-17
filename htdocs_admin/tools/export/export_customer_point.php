<?php
/**
 * Created by 李昆伟
 *
 * Date: 2018/8/13 14:46
 */
include_once('../../../global.php');

/**
 * 导出用户的积分
 *
 * Class App
 */
class App extends App_Cli
{
    protected function main()
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . '客户积分.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $head = array(
            'cid',
            '城市',
            '可用积分'
        );
        Data_Csv::send($head);

        $cc = new Crm2_Customer();
        $cu = new Crm2_User();
        $start = 0;
        $step = 500;
        $citys = Conf_City::getAllCities();
        do {
            $points = array();
            $ccInfo = $cc->getList(array('status' => Conf_Base::STATUS_NORMAL), array('cid', 'city_id'), $start, $step);
            if (count($ccInfo) <= 0) {
                break;
            }
            $cids = self::arrayColumn($ccInfo, 'cid');
            $cuInfo = $cu->getListByCids($cids, array('cid', 'vaild_point'));
            foreach ($ccInfo as $key => $val) {
                foreach ($cuInfo as $k => $v) {
                    if ($v['cid'] == $val['cid']) {
                        //合并过后多个user可用管理一个cid
                        if (isset($points[$v['cid']])) {
                            $points[$v['cid']] += $v['vaild_point'];
                            continue;
                        }
                        $points[$v['cid']] = $v['vaild_point'];
                    }
                }
                if (!isset($points[$val['cid']])) {
                    $points[$val['cid']] = 0;
                }
                isset($citys[$val['city_id']]) ? $city = $citys[$val['city_id']] : $city = $val['city_id'];
                $arr = array(
                    $val['cid'],
                    $city,
                    $points[$val['cid']]
                );
                Data_Csv::send($arr);
            }

            $start += $step;
        } while (count($ccInfo) > 0);
    }

    /**
     * array_column() // 不支持低版本;
     * 以下方法兼容PHP低版本
     */
    private static function arrayColumn(array $array, $column_key, $index_key = null)
    {
        $result = array();
        foreach ($array as $arr) {
            if (!is_array($arr)) continue;

            if (is_null($column_key)) {
                $value = $arr;
            } else {
                $value = $arr[$column_key];
            }

            if (!is_null($index_key)) {
                $key = $arr[$index_key];
                $result[$key] = $value;
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }

    protected function outputHead()
    {
    }

    protected function outputBody()
    {
    }

    protected function outputTail()
    {
    }
}

$app = new App();
$app->run();