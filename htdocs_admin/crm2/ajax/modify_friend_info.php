<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    const TYPE_DELETE = 'delete', TYPE_MODIFY = 'modify';
    private $allTypes = array(
            self::TYPE_DELETE,
            self::TYPE_MODIFY,
        );
    private $type;
    private $cid;
    private $crid;
    private $customerInfo;
    private $friendInfo;
    private $ajResponse;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_customer');
    }

    protected function getPara()
    {
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->crid = Tool_Input::clean('r', 'crid', TYPE_UINT);
        $this->friendInfo = array(
            'relation' => Tool_Input::clean('r', 'relation', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'nick_name' => Tool_Input::clean('r', 'nick_name', TYPE_STR),
            'sex' => Tool_Input::clean('r', 'sex', TYPE_UINT),
            'age' => Tool_Input::clean('r', 'age', TYPE_UINT),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'weixin' => Tool_Input::clean('r', 'weixin', TYPE_STR),
            'qq' => Tool_Input::clean('r', 'qq', TYPE_STR),
            'email' => Tool_Input::clean('r', 'email', TYPE_STR),
            'interest' => Tool_Input::clean('r', 'interest', TYPE_STR),
            'shape' => Tool_Input::clean('r', 'shape', TYPE_STR),
            'trade' => Tool_Input::clean('r', 'trade', TYPE_STR),
            'note' => Tool_Input::clean('r', 'note', TYPE_STR)
        );

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
        if($this->type == self::TYPE_MODIFY){
            if (empty($this->cid) || empty($this->friendInfo['relation']) || empty($this->friendInfo['name']))
            {
                $this->ajResponse['st'] = 11;
                $this->ajResponse['msg'] = '参数错误！';
            }
            if (!empty($this->friendInfo['mobile']) && !Str_Check::checkMobile($this->friendInfo['mobile']))
            {
                $this->ajResponse['st'] = 12;
                $this->ajResponse['msg'] = '手机号格式错误';
            }
        }else{
            if (empty($this->cid) || empty($this->crid))
            {
                $this->ajResponse['st'] = 11;
                $this->ajResponse['msg'] = '参数错误！';
            }
        }

    }

    protected function main()
    {
        if ($this->ajResponse['st'] != 0)
        {
            return;
        }

        $this->_getCustomerInfo();

        if (empty($this->customerInfo))
        {
            $this->ajResponse['st'] = 30;
            $this->ajResponse['msg'] = '客户不存在！';

            return;
        }

        switch ($this->type)
        {
            case self::TYPE_DELETE:
                $this->_deleteFriend();
                break;
            case self::TYPE_MODIFY:
                $this->_modifyFriend();
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


    private function _modifyFriend()
    {
        if(empty($this->crid)){
            $this->friendInfo['cid'] = $this->cid;
            Crm2_Relative_Api::add($this->cid, $this->friendInfo);
        }else{
            Crm2_Relative_Api::update($this->crid, $this->friendInfo);
        }
    }

    private function _deleteFriend()
    {

        Crm2_Relative_Api::delete($this->crid);

    }

    private function _getCustomerInfo()
    {
        $ret = Crm2_Api::getCustomerInfo($this->cid, FALSE, FALSE);

        $this->customerInfo = $ret['customer'];
    }
}

$app = new App();
$app->run();