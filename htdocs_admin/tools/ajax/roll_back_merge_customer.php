<?php
/**
 * Created by 李昆伟
 *
 * Date: 2018/8/10 17:32
 */
include_once('../../../global.php');

/**
 * 回滚客户合并操作
 *
 * Class App
 */
class App extends App_Cli
{
    /**
     * 合并后的cid
     *
     * @var
     */
    private static $cid;

    /**
     * 不需要传递参数得域名
     *
     * @var array
     */
    private static $filterHost = array(
        'sa.haocaisong.cn'
    );

    protected function main()
    {
        self::initCid();

        $cc = new Crm2_Customer();
        $cu = new Crm2_User();
        $customerInfo = Crm2_Api::getCustomerInfo(self::$cid, TRUE, FALSE);

        if (empty($customerInfo['customer']) || empty($customerInfo['users'])) {
            echo '数据不存在';
            return;
        }

        //判断是否已拆分
        $all_user_names = $customerInfo['customer']['all_user_names'];
        $info = explode(',', $all_user_names);
        if (count($info) <= 1) {
            echo '该数据已拆分';
            return;
        }

        //组装主cid需要回滚的customer数据
        $masterCustomerData = self::assembleMasterCustomerData($customerInfo['customer']);
        $cc->update(self::$cid, $masterCustomerData);

        //组装副cid需要回滚的customer,user数据
        $slaverUpdateData = self::assembleSlaverData($customerInfo, $cc);
        $cc->updateByWhere($slaverUpdateData['ccData']['updateData'], array(), $slaverUpdateData['ccData']['condition']);
        $cu->updateByMobile($slaverUpdateData['cuData']['mobile'], $slaverUpdateData['cuData']['updateData']);

        echo '操作成功';
    }

    /**
     * 初始化cid
     */
    private static function initCid()
    {
        if (in_array($_SERVER['HTTP_HOST'], self::$filterHost)) {
            self::$cid = 82228;
            return;
        }
        if (empty($_GET['cid'])) {
            echo '参数缺省';
            exit();
        }
        self::$cid = intval($_GET['cid']);
    }

    /**
     * 组装主cid需要回滚的customer数据
     *
     * @param $data array 待组装数据
     * @return array
     */
    private static function assembleMasterCustomerData($data)
    {
        $names = explode(',', $data['all_user_names']);
        $mobile = explode(',', $data['all_user_mobiles']);
        return array(
            'all_user_names' => $names[1], 'all_user_mobiles' => $mobile[1]
        );
    }

    /**
     * 组装副cid需要回滚的customer数据
     *
     * @param $data array 待组装数据
     * @return array
     */
    private static function assembleSlaverData($data, $cc)
    {
        $customerInfo = $data['customer'];
        $userInfo = $data['users'];
        $mobile = explode(',', $customerInfo['all_user_mobiles']);
        //合并的时候从id的电话是all_user_mobiles的第一个
        $userInfo[0]['mobile'] == $mobile[0] ? $info = $userInfo[0] : $info = $userInfo[1];

        //根据电话获取待分离的cid
        $salverCustomerInfo = $cc->searchCustomerWithMobiles(array($mobile[0]));
        $salverCustomerInfo = reset($salverCustomerInfo);
        $salverCid = $salverCustomerInfo['cid'];

        //组装待回滚的customer的数据和user的数据
        $ccCondition = array('all_user_mobiles' => $mobile[0]);
        $ccUpdateData = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'first_order_date' => '0000-00-00',    //首单日期
            'second_order_date' => '0000-00-00',    //第二次下单日期
            'last_order_date' => '0000-00-00',    //最后下单时间/最后订单时间
            'order_num' => 0,               //总单数
            'online_order_num' => 0,               //在线订单数
            'order_amount' => 0,               //总购买额:订单商品总金额
            'total_amount' => 0,               //总消费额:货款+运费+搬运费-优惠（订单应收）
            'total_privilege' => 0,               //总优惠
            'refund_amount' => 0,         //总退款额
            'refund_num' => 0,      //总退款数
            'account_amount' => 0,       //客户总余额
            'perpay_amount' => 0,        //总预付
        );
        $cuUpdateData = array('cid' => $salverCid);
        return array(
            'ccData' => array(
                'condition' => $ccCondition, 'updateData' => $ccUpdateData
            ),
            'cuData' => array(
                'mobile' => $mobile[0], 'updateData' => $cuUpdateData
            )
        );
    }
}

$app = new App();
$app->run();