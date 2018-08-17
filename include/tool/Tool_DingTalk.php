<?php
/**
 * Created by PhpStorm.
 * 钉钉接口
 * User: zouliangwei
 * Date: 2017/8/1
 * Time: 下午5:23
 */

class Tool_DingTalk
{
    const TOKENURL = "https://oapi.dingtalk.com/gettoken";
    const DEPARTMENTURL = "https://oapi.dingtalk.com/department/list";
    const DEPARTMENTUSERURL = "https://oapi.dingtalk.com/user/list";
    const SENDMESSAGEURL = "https://eco.taobao.com/router/rest";
    const METHOD = "dingtalk.corp.message.corpconversation.asyncsend";
    const CORPID = "ding957f859f3b2a1bdb";
    const SECRET = "iToOqossR2RB6GVenWZSmbOfqlNn9LNMBetSzOovJsahA1Qufo02YFDXXUJ7Z9_c";
    const AGENTID = "110357613";
    public static function sendAutoOrderMessage($userId, $oid, $note)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $url = 'http://'.ADMIN_HOST.'/order/order_detail.php?oid='.$oid;
        $messageContent = array(
            'messageUrl' => $url,
            'title' => '自动确认订单',
            'picUrl' => '@lALOACZwe2Rk',
            'text' =>  '你的订单（oid：'.$oid.'）自动确认失败，请立即手动处理！'.$note,
        );
        $params = array('session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'link',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode($messageContent));
        $response = Tool_Http::post(self::SENDMESSAGEURL, $params);
        $response = json_decode($response, true);
        $result = $response['dingtalk_corp_message_corpconversation_asyncsend_response'];
        $result = $result['result'];
        return $result['success'];
    }

    public static function sendAutoAllocationSalerMessage($userId, $cid)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $url = 'http://'.ADMIN_HOST.'/crm2/customer_detail.php?cid='.$cid;
        $messageContent = array(
            'messageUrl' => $url,
            'title' => '客户注册自动分配',
            'picUrl' => '@lALOACZwe2Rk',
            'text' =>  '你有系统自动分配的新客户（cid：'.$cid.'），请立即处理！',
        );
        $params = array('session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'link',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode($messageContent));
        $response = Tool_Http::post(self::SENDMESSAGEURL, $params);
        $response = json_decode($response, true);
        $result = $response['dingtalk_corp_message_corpconversation_asyncsend_response'];
        $result = $result['result'];
        return $result['success'];
    }

    public static function sendNotice4LeaderSaleMessage($userId, $cid)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $url = 'http://'.ADMIN_HOST.'/crm2/customer_detail.php?cid='.$cid;
        $messageContent = array(
            'messageUrl' => $url,
            'title' => '客户类型修改',
            'picUrl' => '@lALOACZwe2Rk',
            'text' =>  '你有新的客户类型修改申请（cid：'.$cid.'），请立即处理！',
        );
        $params = array('session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'link',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode($messageContent));
        $response = Tool_Http::post(self::SENDMESSAGEURL, $params);
        $response = json_decode($response, true);
        $result = $response['dingtalk_corp_message_corpconversation_asyncsend_response'];
        $result = $result['result'];
        return $result['success'];
    }

    public static function sendIdentityChangeFailMessage($userId, $cid, $reason = '')
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $url = 'http://'.ADMIN_HOST.'/crm2/customer_detail.php?cid='.$cid;
        $messageContent = array(
            'messageUrl' => $url,
            'title' => '客户类型修改',
            'picUrl' => '@lALOACZwe2Rk',
            'text' =>  '你的客户（cid：'.$cid.'）类型修改申请被驳回，理由：'.$reason.'，请重新修改申请！',
        );
        $params = array('session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'link',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode($messageContent));
        $response = Tool_Http::post(self::SENDMESSAGEURL, $params);
        $response = json_decode($response, true);
        $result = $response['dingtalk_corp_message_corpconversation_asyncsend_response'];
        $result = $result['result'];
        return $result['success'];
    }

    public static function sendCertificateMessage($userId, $cid, $mobile)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $url = 'http://' . ADMIN_HOST . '/crm2/customer_detail.php?cid=' . $cid;
        $messageContent = array(
            'messageUrl' => $url,
            'title' => '用户实名认证',
            'picUrl' => '@lALOACZwe2Rk',
            'text' =>  '你有新的用户实名认证需要处理（cid：'.$cid.'）!',
        );
        $params = array(
            'session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'link',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode($messageContent)
        );
        $response = Tool_Http::post(self::SENDMESSAGEURL, $params);
        $response = json_decode($response, true);
        $result = $response['dingtalk_corp_message_corpconversation_asyncsend_response'];
        $result = $result['result'];

        return $result['success'];
    }


    public static function sendCancelOrderMessage($userId, $oid)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $url = 'http://' . ADMIN_HOST . '/order/order_detail.php?oid=' . $oid;
        $messageContent = array(
            'messageUrl' => $url,
            'title' => '订单超时被取消',
            'picUrl' => '@lALOACZwe2Rk',
            'text' =>  '订单（oid：'.$oid.'）因为超过24小时未确认，已被取消，请与客户确认',
        );
        $params = array(
            'session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'link',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode($messageContent)
        );
        $response = Tool_Http::post(self::SENDMESSAGEURL, $params);
        $response = json_decode($response, true);
        $result = $response['dingtalk_corp_message_corpconversation_asyncsend_response'];
        $result = $result['result'];

        return $result['success'];
    }


    /**
     * 获取部门列表
     * @return array
     */
    public static function getDepartments()
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $response = Tool_Http::get(self::DEPARTMENTURL, array('access_token' => $access_token));
        $dic = json_decode($response, true);
        if(!empty($dic['department']))
        {
            return $dic['department'];
        }else{
            return array();
        }
    }

    /**
     * 获取部门员工列表
     * @param $id
     * @return array
     */
    public static function getDepartmentUsersByDepartmentID($id)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $response = Tool_Http::get(self::DEPARTMENTUSERURL, array('access_token' => $access_token,'department_id' => $id));
        $dic = json_decode($response, true);
        if(!empty($dic['userlist']))
        {
            return $dic['userlist'];
        }else{
            return array();
        }
    }

    public static function sendKjlDesignMessage($userId, $cid)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $url = 'http://' . ADMIN_HOST . '/crm2/customer_detail.php?cid=' . $cid;
        $messageContent = array(
            'messageUrl' => $url,
            'title' => '用户装修提醒',
            'picUrl' => '@lALOACZwe2Rk',
            'text' =>  sprintf('您的客户有新户型装修完毕（cid：%d; 手机号：%s）!', $cid, $mobile),
        );
        $params = array(
            'session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'link',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode($messageContent)
        );
        $response = Tool_Http::post(self::SENDMESSAGEURL, $params);
        $response = json_decode($response, true);
        $result = $response['dingtalk_corp_message_corpconversation_asyncsend_response'];
        $result = $result['result'];

        return $result['success'];
    }
    
    /**
     * text消息
     * @author wangxuemin
     * @param string $userId 接收人钉钉ID[userId1,userId2,...]
     * @param string $content 消息内容
     * @return string
     */
    public static function sendTextMessage($userId, $content)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $params = array(
            'session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'text',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode(array('content' =>  $content))
        );
        return self::sendDingTalkMessage($params);
    }
    
    /**
     * link消息
     * @param string $userId 接收人钉钉ID[userId1,userId2,...]
     * @param string $url 链接地址，必须是标准http链接 例如（http://sa.haocaisong.cn/order/list.php）
     * @param string $title 标题
     * @param string $content 消息内容
     * @return string
     */
    public static function sendLinkMessage($userId, $url, $title, $content)
    {
        $response = Tool_Http::get(self::TOKENURL, array('corpid' => self::CORPID, 'corpsecret' => self::SECRET));
        $dic = json_decode($response, true);
        $access_token = $dic['access_token'];
        $messageContent = array(
            'messageUrl' => $url,
            'title' => $title,
            'picUrl' => '@lALOACZwe2Rk',
            'text' =>  $content,
        );
        $params = array(
            'session' => $access_token,
            'timestamp' => time(),
            'format' => 'json',
            'v' => '2.0',
            'msgtype' => 'link',
            'agent_id' => self::AGENTID,
            'userid_list' => $userId,
            'method' => self::METHOD,
            'msgcontent' => json_encode($messageContent)
        );
        return self::sendDingTalkMessage($params);
    }
    
    /**
     * 发送钉钉消息
     * @author wangxuemin
     * @param array $params 消息参数
     * @return string
     */
    public static function sendDingTalkMessage($params)
    {
        $response = Tool_Http::post(self::SENDMESSAGEURL, $params);
        $response = json_decode($response, true);
        $result = $response['dingtalk_corp_message_corpconversation_asyncsend_response'];
        $result = $result['result'];
        return $result['success'];
    }
}