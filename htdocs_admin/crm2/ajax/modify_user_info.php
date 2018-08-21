<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    const TYPE_BIND = 'bind', TYPE_UNBIND = 'unbind', TYPE_MODIFY = 'modify';
    private $allTypes = array(
            self::TYPE_BIND,
            self::TYPE_UNBIND,
            self::TYPE_MODIFY,
        );
    private $type;
    private $cid;
    private $uid;
    private $name;
    private $mobile;
    private $hometown;
    private $realName;
    private $idCardNo;
    private $isAdmin;
    private $userNumOfCustomer;
    private $customerInfo;
    private $userInfo;
    private $ajResponse;

    protected function checkAuth($permission='')
    {
        parent::checkAuth('/crm2/edit_customer');
    }

    protected function getPara()
    {
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->uid = Tool_Input::clean('r', 'uid', TYPE_UINT);
        $this->name = Tool_Input::clean('r', 'name', TYPE_STR);
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_STR);
        $this->hometown = Tool_Input::clean('r', 'hometown', TYPE_STR);
        $this->isAdmin = Tool_Input::clean('r', 'is_admin', TYPE_UINT);

        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
        );
    }

    protected function checkPara()
    {
        if (!in_array($this->type, $this->allTypes))
        {
            $this->ajResponse['st'] = 10;
            $this->ajResponse['msg'] = '操作类型错误！';
        }

        if (empty($this->cid) || ($this->type != self::TYPE_BIND && empty($this->uid)))
        {
            $this->ajResponse['st'] = 11;
            $this->ajResponse['msg'] = '参数错误！';
        }

        if (!empty($this->mobile) && !Str_Check::checkMobile($this->mobile))
        {
            $this->ajResponse['st'] = 12;
            $this->ajResponse['msg'] = '手机号格式错误';
        }
    }

    protected function main()
    {
        if ($this->ajResponse['st'] != 0)
        {
            return;
        }

        // 取用户信息
        if ($this->type == self::TYPE_BIND)
        {
            $this->_getCustomerInfo();
        }
        else
        {
            $this->_getUserInfo();
        }

        switch ($this->type)
        {
            case self::TYPE_BIND:
                $this->_bindUser();
                break;
            case self::TYPE_UNBIND:
                $this->_unbindUser();
                break;
            case self::TYPE_MODIFY:
                $this->_modifyUser();
                break;

            default :
                $this->ajResponse['st'] = 19;
                $this->ajResponse['msg'] = '未进行任何编辑操作！';
                break;
        }
    }

    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent($this->ajResponse);
        $response->send();

        exit;
    }

    private function _unbindUser()
    {
        if ($this->userNumOfCustomer <= 1)
        {
            $this->ajResponse['st'] = 20;
            $this->ajResponse['msg'] = '客户的用户数不能小于2！';

            return;
        }
        if (empty($this->userInfo) || $this->userInfo['cid'] != $this->cid)
        {
            $this->ajResponse['st'] = 21;
            $this->ajResponse['msg'] = '该用户与客户不是绑定关系';

            return;
        }

        // 临时逻辑，如果uid有订单记录，不允许解绑
        $oo = new Order_Order();
        $where = 'status=0 and uid=' . $this->userInfo['uid'];
        $orderList = $oo->getListRawWhere($where, $total, '');

        if ($total > 0)
        {
            $this->ajResponse['st'] = 23;
            $this->ajResponse['msg'] = '解绑客户有订单，请[临时]联系技术人员处理！！';

            return;
        }

        // 解绑逻辑
        $ret = Crm2_Api::unbindUserFromCustomer($this->cid, $this->userInfo);

        if ($ret < 0)
        {
            $this->ajResponse['st'] = 22;
            $this->ajResponse['msg'] = '解绑失败！请联系管理员！';
        }
    }

    private function _bindUser()
    {
        if (empty($this->customerInfo))
        {
            $this->ajResponse['st'] = 30;
            $this->ajResponse['msg'] = '客户不存在！';

            return;
        }

        $bindUserInfo = array(
            'name' => $this->name,
            'mobile' => $this->mobile,
            'hometown' => $this->hometown,
            'is_admin' => $this->isAdmin,
        );
        $ret = Crm2_Api::bindUserWithCustomer($this->cid, $bindUserInfo);

        if ($ret == -10)
        {
            $this->ajResponse['st'] = 31;
            $this->ajResponse['msg'] = '手机号已经注册！';
        }
        else if ($ret < 0)
        {
            $this->ajResponse['st'] = 32;
            $this->ajResponse['msg'] = '绑定失败！请联系管理员！';
        }
    }

    private function _modifyUser()
    {
        if (empty($this->customerInfo) || empty($this->userInfo))
        {
            $this->ajResponse['st'] = 40;
            $this->ajResponse['msg'] = '客户不存在！';

            return;
        }
        $isMyCustomer = Crm2_Api::isMyCustomer($this->customerInfo, $this->_user);
        if ($this->userInfo['mobile'] != $this->mobile && !$isMyCustomer['can_edit'])
        {
            $this->ajResponse['st'] = 41;
            $this->ajResponse['msg'] = '修改客户手机号，请联系管理员修改！';

            return;
        }

        $modifyUinfo = array(
            'name' => $this->name,
            'mobile' => $this->mobile,
            'hometown' => $this->hometown,
            'is_admin' => $this->isAdmin,
        );
        $ret = Crm2_Api::updateUserInfo($this->uid, $this->cid, $modifyUinfo);

        if ($ret == -10)
        {
            $this->ajResponse['st'] = 41;
            $this->ajResponse['msg'] = '手机号已经注册！';
        }
        else if ($ret < 0)
        {
            $this->ajResponse['st'] = 42;
            $this->ajResponse['msg'] = '更新失败！请联系管理员！';
        }
    }

    private function _getCustomerInfo()
    {
        $ret = Crm2_Api::getCustomerInfo($this->cid, FALSE);
        
        $this->customerInfo = $ret['customer'];
        $this->userInfo = $ret['users'];
    }

    private function _getUserInfo()
    {
        $ret = Crm2_Api::getUserInfo($this->uid);

        $this->customerInfo = $ret['customer'];
        $this->userInfo = $ret['user'];

        $this->userNumOfCustomer = count(explode(Crm2_Customer::REDUNDANT_SEPARATE, $this->customerInfo['all_user_mobiles']));
    }
}

$app = new App();
$app->run();