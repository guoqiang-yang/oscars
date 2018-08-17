<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/9/13
 * Time: 17:36
 */

define("company_auth_url", "https://v.apistore.cn/api/a28/v4");
define("company_auth_key", "b36c7d6f3da3101a51c4512375f8623f");
define("mobile_auth_url", "https://v.apistore.cn/api/a3");
define("mobile_auth_key", "6f393ecae147667b84cfd5cfce2f4f21");
define("bankcard_auth_url", "https://v.apistore.cn/api/bank/v4");
define("bankcard_auth_key", "a91bd528b8c829cdc147be392c1ff545");
class Crm2_Certification_Api extends Base_Api
{
    public static function isShowPrice($cid)
    {
        /*  实名认证及用户类型价格显示暂时注释
        $customer = Crm2_Api::getCustomerInfo($cid, false, false);
        
        $cc = new Crm2_Certification();
        $certificate = $cc->getByCid($cid);
        if ($certificate['step'] == Conf_User::CERTIFICATE_PASS)
        {
            return true;
        }
        
        if ($customer['customer']['identity'] == Conf_User::CRM_IDENTITY_NEW)
        {
            return false;
        }
        */
        return true;
    }

    public static function isShowCertificateDlg($cid)
    {
        $customer = Crm2_Api::getCustomerInfo($cid, false, false);

        $cc = new Crm2_Certification();
        $certificate = $cc->getByCid($cid);
        if ($certificate['step'] == Conf_User::CERTIFICATE_PASS)
        {
            return false;
        }

        if ($customer['customer']['identity'] != Conf_User::CRM_IDENTITY_NEW)
        {
            return true;
        }

        return false;
    }

    public static function getByCid($cid)
    {
        $cc = new Crm2_Certification();

        return $cc->getByCid($cid);
    }
    /**
     * 认证
     * @param $cid      int
     * @param $uid      int
     * @param $type     int
     * @param $info     array
     *
     * @return bool
     * @throws exception
     */
    public static function certificate($cid, $uid, $type, $info)
    {
        $info['step'] = Conf_User::CERTIFICATE_NEW;
        $data = array();
        $cc = new Crm2_Certification();
        if ($uid == 0)
        {
            $mobile = $info['mobile'];
        }
        else
        {
            $data = Crm2_Api::getUserInfo($uid, true, false);
            $user = $data['user'];
            $mobile = $user['mobile'];
            $info['mobile'] = $mobile;
        }

        if (!empty($info['id_number']))
        {
            $where = sprintf('id_number="%s"', $info['id_number']);
            $existInfos = $cc->limit(0, 1)->getList($where);
            $existInfo = array_shift($existInfos);
            if (!empty($existInfo) && $existInfo['cid'] != $cid && $existInfo['step'] != Conf_User::CERTIFICATE_RESULT_DENY)
            {
                throw new Exception('该身份证已经被认证，不能重复认证！');
            }
        }
        if (!empty($info['social_credit_number']))
        {
            $where = sprintf('social_credit_number="%s"', $info['social_credit_number']);
            $existInfos = $cc->limit(0, 1)->getList($where);
            $existInfo = array_shift($existInfos);
            if (!empty($existInfo) && $existInfo['cid'] != $cid && $existInfo['step'] != Conf_User::CERTIFICATE_RESULT_DENY)
            {
                throw new Exception('该企业已经被认证，不能重复认证！');
            }
        }

        //调用给第三方接口认证
        if ($type == Conf_User::CRM_IDENTITY_PERSONAL)
        {
            $result = self::personal_auth($info['real_name'], $info['id_number'], $mobile, $info['band_card_number']);
        }
        else
        {
            $result = self::company_auth($info['company_name'], $info['social_credit_number'], $info['legal_person_name'], $info['legal_person_id_number']);
        }

        if ($result['result'])
        {
            $info['step'] = Conf_User::CERTIFICATE_IN_PROCESS;
        }
        else
        {
            throw new Exception($result['reason']);
        }

        $oldInfo = $cc->getByCid($cid);
        if (!empty($oldInfo))
        {
            $info['type'] = $type;
            $cc->update($oldInfo['id'], $info);
        }
        else
        {
            $info['cid'] = $cid;
            $info['type'] = $type;
            $cc->add($info);
        }

        if ($uid > 0)
        {
            //指派处理给销售
            $ccr = new Crm2_Certification_Request();
            $suid = $data['customer']['sales_suid'];
            if ($suid == 0)
            {
                $suid = Conf_Admin::getCertificateDealSuid($data['customer']['city_id']);
            }
            $info = array(
                'cid' => $cid,
                'suid' => $suid,
                'result' => Conf_User::CERTIFICATE_RESULT_UNDEAL,
            );
            $ccr->add($info);

            //发钉钉给销售
            $suer = Admin_Api::getStaff($suid);
            Tool_DingTalk::sendCertificateMessage($suer['ding_id'], $cid);
        }
        else
        {
            //指派处理给销售
            $ccr = new Crm2_Certification_Request();
            $customerInfo = Crm2_Api::getCustomerInfo($cid);
            $suid = $customerInfo['customer']['sales_suid'];
            if ($suid == 0)
            {
                $suid = Conf_Admin::getCertificateDealSuid($customerInfo['customer']['city_id']);
            }
            $ccr_info = $ccr->getUndealItem($suid, $cid);
            if(empty($ccr_info))
            {
                $info = array(
                    'cid' => $cid,
                    'suid' => $suid,
                    'result' => Conf_User::CERTIFICATE_RESULT_UNDEAL,
                );
                $ccr->add($info);
            }
        }

        return $result['result'];
    }

    public static function update($cid, $info)
    {
        $where = sprintf('cid=%d', $cid);
        $cc = new Crm2_Certification();

        return $cc->updateWhere($where, $info);
    }

    public static function getUndealItem($suid, $cid)
    {
        $ccr = new Crm2_Certification_Request();

        return $ccr->getUndealItem($suid, $cid);
    }

    public static function salerPass($suid, $cid)
    {
        $cc = new Crm2_Certification();
        $oldInfo = $cc->getByCid($cid);
        if($oldInfo['step'] == Conf_User::CERTIFICATE_PASS)
        {
            throw new Exception('该客户已经认证过，请不要重复认证!');
        }
        $ccr = new Crm2_Certification_Request();

        $data = Crm2_Api::getCustomerInfo($cid, false, false);
        $suser = Admin_Api::getStaff($suid);

        $ccr->pass($suid, $cid);
        if ($suid == $data['customer']['sales_suid'])   //销售
        {
            //转给组长处理
            $leaderSuid = $suser['leader_suid'];
            if ($leaderSuid != $suid)
            {
                if (empty($suser['leader_suid']))
                {
                    self::_leaderPass($cid, $data['user']['mobile']);
                }
                else
                {
                    $leader = Admin_Api::getStaff($leaderSuid);
                    $info = array(
                        'cid' => $cid,
                        'suid' => $leaderSuid,
                        'result' => Conf_User::CERTIFICATE_RESULT_UNDEAL,
                    );
                    $ccr->add($info);

                    //发钉钉给组长
                    Tool_DingTalk::sendCertificateMessage($leader['ding_id'], $cid);
                }
            }
            else
            {
                self::_leaderPass($cid, $data['user']['mobile']);
            }
        }
        else
        {
            self::_leaderPass($cid, $data['user']['mobile']);
        }
    }

    private static function _leaderPass($cid, $mobile)
    {
        //更改审核状态
        $cc = new Crm2_Certification();
        $update = array(
            'step' => Conf_User::CERTIFICATE_PASS,
        );
        $cc->updateByCid($cid, $update);

        $oldInfo = $cc->getByCid($cid);
        $cc = new Crm2_Customer();
        $update = array(
            'identity' => $oldInfo['type'],
        );
        $cc->update($cid, $update);

//        //发优惠券
//        $cc = new Coupon_Coupon();
//        if($oldInfo['type'] == Conf_User::CRM_IDENTITY_PERSONAL)
//        {
//            if(!empty($oldInfo['id_number']))
//            {
//                $cc->addPromotionCoupon($cid, 67);
//            }
//            if(!empty($oldInfo['band_card_number']))
//            {
//                $cc->addPromotionCoupon($cid, 67);
//            }
//        }else{
//            $cc->addPromotionCoupon($cid, 67);
//        }
        //短信通知用户
//        Data_Sms::send($mobile, '您的实名认证已通过，快去买您想要的材料吧！');
        Data_Sms::sendNew($mobile, Conf_Sms::CERTIFICATE_PASS_KEY, array());
    }

    public static function salerDeny($suid, $cid, $reason = '')
    {
        $ccr = new Crm2_Certification_Request();

        $data = Crm2_Api::getCustomerInfo($cid, false, false);


        $ccr->deny($suid, $cid, $reason);
        $dealSuid = Conf_Admin::getCertificateDealSuid($data['customer']['city_id']);
        if ($suid == $dealSuid)   //特定的人
        {
            //更改审核状态
            $cc = new Crm2_Certification();
            $update = array(
                'step' => Conf_User::CERTIFICATE_DENY,
                'real_name' => '',
                'mobile' => '',
                'id_number' => '',
                'band_card_number' => '',
                'company_name' => '',
                'legal_person_name' => '',
                'legal_person_id_number' => '',
                'social_credit_number' => ''
            );
            $cc->updateByCid($cid, $update);

            //短信通知用户
            $mobile = $data['user']['mobile'];
//            Data_Sms::send($mobile, '很遗憾！您的实名认证未通过！');
            Data_Sms::sendNew($mobile, Conf_Sms::CERTIFICATE_DENY_KEY, array());
        }
        else
        {
            //转给特定人处理
            $leaderSuid = Conf_Admin::getCertificateDealSuid($data['customer']['city_id']);
            $leader = Admin_Api::getStaff($leaderSuid);
            $info = array(
                'cid' => $cid,
                'suid' => $leaderSuid,
                'result' => Conf_User::CERTIFICATE_RESULT_UNDEAL,
            );
            $ccr->add($info);

            //发钉钉给特定的人
            Tool_DingTalk::sendCertificateMessage($leader['ding_id'], $cid);
        }
    }

    public static function personal_auth($realName, $cardNo, $mobile, $bankcardNo)
    {
        $params = array(
            'realName' => $realName,
            'cardNo' => $cardNo,
        );
        if(!empty($bankcardNo))
        {
            $params['Mobile'] = $mobile;
            $params['bankcard'] = $bankcardNo;
            $params['key'] = bankcard_auth_key;
            $url = bankcard_auth_url;
        }
        else
        {
            $params['mobile'] = $mobile;
            $params['key'] = mobile_auth_key;
            $url = mobile_auth_url;
        }
        $responseStr = Tool_Http::get($url, $params);
        $response = json_decode($responseStr, true);
        $log = var_export($params, true) . "\tret=" . $responseStr;
        Tool_Log::addFileLog('auth/log_' . date('Ym'), $log);
        if($response['error_code'] == 0)
        {
            return array('result' => true, 'reason' => $response['reason']);
        }
        else
        {
            return array('result' => false, 'reason' => $response['reason']);
        }
    }

    public static function company_auth($companyName, $companyNo, $realName, $cardNo)
    {
        $params = array(
            'key' => company_auth_key,
            'com' => $companyName,
            'realName' => $realName,
            'cardNo' => $cardNo,
            'no' => $companyNo
        );
        $responseStr = Tool_Http::get(company_auth_url, $params);
        $response = json_decode($responseStr, true);
        $log = var_export($params, true) . "\tret=" . $responseStr;
        Tool_Log::addFileLog('auth/log_' . date('Ym'), $log);
        if($response['error_code'] == 0)
        {
            return array('result' => true, 'reason' => $response['reason']);
        }
        else
        {
            return array('result' => false, 'reason' => $response['reason']);
        }
    }
}