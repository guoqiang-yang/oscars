<?php
include_once('../../../global.php');
/**
 * 消息通知demo
 * @author wangxuemin
 */
class App extends App_Admin_Ajax
{
    private $type;
    private $channel;
    
    private $mobile;
    private $ding_id;
    private $title;
    private $content;
    
    private $desc;
    private $package;
    
    private $openid;
    private $oid;
    private $amount;
    private $delivery_date;
    private $address;
    private $carry;
    private $fee;
    private $line_id;
    private $user_mobile;
    private $rid;
    private $activity_name;
    private $reward_name;
    private $url;

    /**
     * (non-PHPdoc)
     * @see Base_App::getPara()
     */
    protected function getPara()
    {
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
        $this->channel = Tool_Input::clean('r', 'channel', TYPE_STR);
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_STR);
        $this->ding_id = Tool_Input::clean('r', 'ding_id', TYPE_STR);
        $this->title = Tool_Input::clean('r', 'title', TYPE_STR);
        $this->content = Tool_Input::clean('r', 'content', TYPE_STR);
        
        $this->desc = Tool_Input::clean('r', 'desc', TYPE_STR);
        $this->package = Tool_Input::clean('r', 'package', TYPE_INT);
        
        $this->openid = Tool_Input::clean('r', 'openid', TYPE_STR);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_INT);
        $this->amount = Tool_Input::clean('r', 'amount', TYPE_INT);
        $this->delivery_date = Tool_Input::clean('r', 'delivery_date', TYPE_STR);
        $this->address = Tool_Input::clean('r', 'address', TYPE_STR);
        $this->carry = Tool_Input::clean('r', 'carry', TYPE_STR);
        $this->fee = Tool_Input::clean('r', 'fee', TYPE_INT);
        $this->line_id = Tool_Input::clean('r', 'line_id', TYPE_INT);
        $this->user_mobile = Tool_Input::clean('r', 'user_mobile', TYPE_INT);
        $this->rid = Tool_Input::clean('r', 'rid', TYPE_INT);
        $this->activity_name = Tool_Input::clean('r', 'activity_name', TYPE_STR);
        $this->reward_name = Tool_Input::clean('r', 'reward_name', TYPE_STR);
        $this->url = Tool_Input::clean('r', 'url', TYPE_STR);
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::main()
     */
    protected function main()
    {   
        header("Content-type: text/html; charset=utf-8");
        if ($this->type == 'text'){
            $params = array(
                //'ding_id' => '164232451129650738,123527332026089258',
                'ding_id' => $this->ding_id,
                'content' => $this->content
            );
            var_dump(Notify_Api::sendDingDingPush($this->type, $params));exit;
        } elseif ($this->type == 'link') {
            $params = array(
                //'ding_id' => '164232451129650738,123527332026089258',
                'ding_id' => $this->ding_id,
                'title' => $this->title,
                'url' => $this->url,
                'content' => $this->content
            );
            var_dump(Notify_Api::sendDingDingPush($this->type, $params));exit;
        } elseif ($this->channel == 'aliyun' || $this->channel == 'yixinxi') {
            $params = array(
                'mobile' => $this->mobile,
                'template_id' => Conf_Sms::COUPON_REMIND_KEY,
                'params' => array('amount' => 99999)
            );
            var_dump(Notify_Api::sendSms($this->channel, $params));exit;
        } elseif ($this->channel == 'xiaomi_android_push') {
            if ($this->type == 'one') {
                $params = array(
                    'reg_id' => 'vajBO+pn6X9TH3JqGREW8IhcbM2BiHpUvfZuqK4mm1o=',
                    'title' => $this->title,
                    'desc' => $this->desc,
                    'package' => $this->package,
                    'payload' => array(
                        'name' => 'xionghu',
                        'url' => 'http://www.baidu.com',
                        'msgtype' => 2
                    )
                );
            } elseif ($this->type == 'all') {
                $params = array(
                    'title' => $this->title,
                    'desc' => $this->desc,
                    'package' => 1,
                    'payload' => array('name' => 'xionghu')
                );
            }
            var_dump(Notify_Api::sendXiaomiPush($this->channel, $this->type, $params));exit;
        }  elseif ($this->type == 'pay_notice') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
                'amount' => $this->amount
            );
        } elseif ($this->type == 'alloc_notice') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
                'delivery_date' => $this->delivery_date,
                'address' => $this->address,
                'carry' => $this->carry,
                'fee' => $this->fee,
                'line_id' => $this->line_id,
            );
        } elseif ($this->type == 'back_notice') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
                'fee' => $this->fee,
            );
        } elseif ($this->type == 'order_canceld') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
            );
        } elseif ($this->type == 'order_pay_succ') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
                'user_mobile' => $this->user_mobile
            );
        } elseif ($this->type == 'order_create_succ') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
            );
        } elseif ($this->type == 'order_set_out') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
            );
        } elseif ($this->type == 'refund_pay_succ') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
                'rid' => $this->rid
            );
        } elseif ($this->type == 'order_unpaid') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
            );
        } elseif ($this->type == 'order_complate') {
            $params = array(
                'openid' => $this->openid,
                'oid' => $this->oid,
            );
        } elseif ($this->type == 'lottery_succ') {
            $params = array(
                'openid' => $this->openid,
                'activity_name' => $this->activity_name,
                'reward_name' => $this->reward_name,
                'url' => $this->url
            );
        }
        var_dump(Notify_Api::sendWeixinPush($this->type, $params));exit;
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