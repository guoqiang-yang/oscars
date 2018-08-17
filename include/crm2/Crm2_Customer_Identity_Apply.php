<?php
/**
 * Created by PhpStorm.
 * User: liangwei
 * Date: 17/11/02
 * Time: 17:36
 */
class Crm2_Customer_Identity_Apply extends Base_New_Func
{
    public function __construct()
    {
        parent::__construct('t_customer_identity_apply');
    }

    public function getByCid($cid)
    {
        return $this->_dao->get($cid);
    }

    public function checkCanAudit($suid, $cid)
    {
        $info = self::getByCid($cid);
        if(empty($info) || $info['step'] > 1)
        {
            return false;
        }
        if(!Admin_Role_Api::isAdmin($suid) && $info['suid'] != $suid)
        {
            return false;
        }
        return true;
    }

    public function pass($cid)
    {
        $update = array(
            'step' => 2,
        );
        $this->_dao->update($cid, $update);
        $info = self::getByCid($cid);
        if($info['step'] == 2)
        {
            Crm2_Api::updateCustomerInfo($cid, array('identity' => $info['identity']));
            $data = Crm2_Api::getCustomerInfo($cid, false, false);
            $mobile = $data['user']['mobile'];
//            Data_Sms::send($mobile, '您的客户类型已修改，可以显示价格了，快去买您想要的材料吧！');
            Data_Sms::sendNew($mobile, Conf_Sms::CERTIFICATE_IDENTITY_CHANGED_KEY, array());
        }
    }
    
    public function deny($suid, $cid, $reason = '')
    {
        $update = array(
            'step' => 3,
        );
        $this->_dao->update($cid, $update);
        $info = self::getByCid($cid);
        if($info['step'] == 3)
        {
            $cc = new Crm2_Certification();
            $where = sprintf(' cid=%d and status=0 ', $cid);
            $cc->updateWhere($where, array('step' => Conf_User::CERTIFICATE_DENY));
            $saleInfo = Admin_Api::getStaff($suid);
            if(!empty($saleInfo['ding_id']))
            {
                Tool_DingTalk::sendIdentityChangeFailMessage($saleInfo['ding_id'], $cid, $reason);
            }
            $arr = array(
                'm_type' => Conf_Message::MESSAGE_TYPE_SYS,
                'typeid' => 0,
                'content' => sprintf("客户（cid:%d）类型修改被驳回，理由：%s", $cid, $reason),
                'url' => '/crm2/customer_detail.php?cid='.$cid,
                'receive_suid' => $suid,
            );
            Admin_Message_Api::create($arr);
        }
    }

    public function add($info)
    {
        return $this->_dao->add($info, array('identity', 'step'));
    }

    public function updateInfo($id, $update)
    {
        return $this->_dao->update($id, $update);
    }
}