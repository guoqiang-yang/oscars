<?php
/**
 * Created by PhpStorm.
 * User: zouliangwei
 * Date: 16/11/9
 * Time: 下午1:57
 */

include_once("../../global.php");

class App extends App_Admin_Page
{
    private $start;
    private $num = 20;
    private $total;
    private $list;
    private $tid;
    private $searchConf;
    private $submit;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
        $this->searchConf = array(
            'used' => Tool_Input::clean('r', 'used', TYPE_INT),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_INT),
            'ctime' => Tool_Input::clean('r', 'ctime', TYPE_STR),
        );
        $this->submit = Tool_Input::clean('r', 'submit', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->searchConf['used']))
        {
            $this->searchConf['used'] = -1;
        }
    }

    protected function main()
    {
        if ($this->submit == 'download')
        {
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=" . 'coupon-log-' . $this->tid . '-' . date('Ymd') . '.csv');
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');

            $head = array(
                '优惠券ID', 'CID', '客户名', '销售', '发放日期', '使用日期', '过期日期', '优惠券面额', '活动ID'
            );
            Data_Csv::send($head);

            //下载
            $start = 0;
            $step = 1000;
            do
            {
                $data = Coupon_Api::getCouponListByTid($this->tid, $this->searchConf, $start, $step);
                $list = $data['data'];
                if (count($list) <= 0)
                {
                    break;
                }

                $cids = Tool_Array::getFields($list, 'cid');
                $customers = Crm2_Api::getCustomers($cids);
                $suids = Tool_Array::getFields($customers, 'sales_suid');
                $admins = Admin_Api::getStaffs($suids);
                $adminMap = Tool_Array::list2Map($admins, 'suid');

                foreach ($list as $coupon)
                {
                    $cid = $coupon['cid'];
                    $customer = $customers[$cid];
                    $sales = $adminMap[$customer['sales_suid']];
                    $mtime = $coupon['used'] > 0 ? $coupon['mtime'] : '0000-00-00 00:00:00';
                    //'优惠券ID', 'CID', '客户名', '销售', '发放日期', '使用日期'
                    //, '过期日期', '优惠券面额', '活动ID'
                    $arr = array(
                        $coupon['id'], $coupon['cid'], $customer['name'],
                        $sales['name'], $coupon['ctime'],
                        $mtime, $coupon['deadline'], $coupon['amount'], $coupon['aid'],
                    );
                    Data_Csv::send($arr);
                }

                $start += $step;
            }
            while(count($list) > 0);

            exit;
        }
        else
        {
            //查询
            $data = Coupon_Api::getCouponListByTid($this->tid, $this->searchConf, $this->start, $this->num);
            $this->list = $data['data'];
            $this->total = $data['total'];
        }
    }

    protected function outputBody()
    {
        $app = '/activity/coupon_customer_list.php';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('tid', $this->tid);

        $this->smarty->display('activity/coupon_customer_list.html');
    }
}

$app = new App('pri');
$app->run();