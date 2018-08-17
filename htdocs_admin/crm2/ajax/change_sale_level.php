<?php

/**
 * 修改销售对客户的级别标记.
 * 
 * @notice 
 *      - 大客户的数量 20个
 *      - 意向客户的数据 未成交客户客户数*20%
 * 
 * @rule
 *      - 权限：销售自己的客户 or 管理员 or 销售总监
 *      - 规则：
 *          普通客户 -> 大客户
 *          大客户   -> 普通客户
 *          意向客户 -> 待跟进客户
 *          待跟进客户 -> 意向客户
 * 
 * @offline [important] 功能在2016-06-01 下线
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    const BIG_CUSTOMER_MAX_NUM = 50;
    const INTEND_CUSTOMER_MAX_RATE = 0.2;
    private $_allChgSt = array('big', 'common', 'follow', 'intend');
    
    private $cid;
    private $chgSt;
    
    private $isGeneralAdmin = false;    //广义的管理员：管理员or总监
    private $upCustomerInfo = array();
    
    private $customerInfo;
    private $ajResponse;
    
    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_INT);
        $this->chgSt = Tool_Input::clean('r', 'st', TYPE_STR);
        
        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
        );
    }
    
    protected function checkPara()
    {
        if (empty($this->cid) || !in_array($this->chgSt, $this->_allChgSt))
        {
            $this->ajResponse['st'] = 100;
            $this->ajResponse['msg'] = '参数错误， 请联系管理员！！';
        }
        
        // 获取客户信息
        $customerInfo = Crm2_Api::getCustomerInfo($this->cid, false, false);
        $this->customerInfo = $customerInfo['customer'];
        
        if (empty($this->customerInfo))
        {
            $this->ajResponse['st'] = 101;
            $this->ajResponse['msg'] = '客户不存在！';
        }
        
        $this->isGeneralAdmin = Admin_Role_Api::isAdmin($this->_uid, $this->_user)
                            || Admin_Role_Api::isChiefSaler($this->_user);
        
        $this->_checkOpAuth();
    }
    
    protected function main()
    {
        if ($this->ajResponse['st'] != 0)
        {
            return;
        }
        
        // 更改销售级别
        $upSt = Crm2_Api::updateCustomerInfo($this->cid, $this->upCustomerInfo);

        if ($upSt)
        {
            // 记录客户跟踪信息
            $content = '客户销售级别变更：从【'. Conf_User::$Crm_Level_BySaler[$this->customerInfo['level_for_saler']]
                    . '】变更为【'. Conf_User::$Crm_Level_BySaler[$this->upCustomerInfo['level_for_saler']].'】';
            $trackingInfo = array(
                'cid' => $this->cid,
                'edit_suid' => $this->_uid,
                'content' => $content,
                'type' => Conf_User::CT_CHG_SALE_LEVEL,
            );
            Crm2_Api::saveCustomerTracking(0, $trackingInfo);
        }
        else
        {
            $this->ajResponse['st'] = 120;
            $this->ajResponse['msg'] = '更新用户信息失败，请联系管理员！';
        }
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent($this->ajResponse);
		$response->send();
		exit;
    }
    
    
    private function _checkOpAuth()
    {
        switch ($this->chgSt)
        {
            case 'big': //大客户（下过单）
                $this->upCustomerInfo['level_for_saler'] = Conf_User::SALER_LEVEL_BIG;
                
                $this->_chkBig();
                break;
            case 'common':  //普通客户（下过单）
                $this->upCustomerInfo['level_for_saler'] = Conf_User::SALER_LEVEL_COMMON;
                
                $this->_chkCommon();
                break;
            case 'follow':  //待跟进客户（未下过单）
                $this->upCustomerInfo['level_for_saler'] = Conf_User::SALER_LEVEL_FOLLOW;
                
                $this->_chkFollow();
                break;
            case 'intend':  //意向客户（未下过单）
                $this->upCustomerInfo['level_for_saler'] = Conf_User::SALER_LEVEL_INTEND;
                
                $this->_chkIntend();
                break;
            default :
                $this->ajResponse['st'] = 110;
                $this->ajResponse['msg'] = '操作类型错误！';
                
        }
    }
    
    /**
     * 标记为大客户（领取后的下单客户）
     */
    private function _chkBig()
    {
        if ($this->customerInfo['level_for_saler']==Conf_User::SALER_LEVEL_COMMON
            && ($this->_uid == $this->customerInfo['sales_suid'] || $this->isGeneralAdmin))
        {
            // 获取大客户的数量
            $cc2 = new Crm2_Customer;
            $where = 'status=0 and sales_suid='.$this->customerInfo['sales_suid']. ' and level_for_saler='.Conf_User::SALER_LEVEL_BIG;
            $bigTotal = $cc2->getTotalByWhere($where);
            
            if ($bigTotal >= self::BIG_CUSTOMER_MAX_NUM)
            {
                $this->ajResponse['st'] = 112;
                $this->ajResponse['msg'] = '已经达到大客户的标记上限！';
            }
        }
        else
        {
            $this->ajResponse['st'] = 111;
            $this->ajResponse['msg'] = '对不起，没有权限标记为”大客户“！';
        }   
    }
    
    /**
     * 标记为普通客户（领取后的下单客户）
     */
    private function _chkCommon()
    {
        if ($this->customerInfo['level_for_saler']!=Conf_User::SALER_LEVEL_BIG 
           || ($this->_uid!=$this->customerInfo['sales_suid']&&!$this->isGeneralAdmin))
        {
            $this->ajResponse['st'] = 113;
            $this->ajResponse['msg'] = '对不起，没有权限标记为”普通客户“！';
        }
    }
    
    /**
     * 标记为待跟踪客户（领取后的未下单客户）
     */
    private function _chkFollow()
    {
        if ($this->customerInfo['level_for_saler']!=Conf_User::SALER_LEVEL_INTEND
            || ($this->_uid!=$this->customerInfo['sales_suid']&&!$this->isGeneralAdmin))
        {
            $this->ajResponse['st'] = 114;
            $this->ajResponse['msg'] = '对不起，没有权限标记为”待跟踪客户“！';
        }
    }
    
    /**
     * 标记为意向客户（领取后的未下单客户）
     */
    private function _chkIntend()
    {
        if ($this->customerInfo['level_for_saler']==Conf_User::SALER_LEVEL_FOLLOW 
            && ($this->_uid==$this->customerInfo['sales_suid']||$this->isGeneralAdmin))
        {
            $cc2 = new Crm2_Customer();
            $noOrderWhere = sprintf('status=%d and sales_suid=%d and date(chg_sstatus_time)>=date(last_order_date)',
                    Conf_Base::STATUS_NORMAL, $this->customerInfo['sales_suid']);
            $intendWhere = sprintf('status=%d and sales_suid=%d and level_for_saler=%d',
                    Conf_Base::STATUS_NORMAL, $this->customerInfo['sales_suid'], Conf_User::SALER_LEVEL_INTEND);
            $noOrderTotal = $cc2->getTotalByWhere($noOrderWhere);
            $intendTotal = $cc2->getTotalByWhere($intendWhere);
            
            if ($intendTotal >= $noOrderTotal*self::INTEND_CUSTOMER_MAX_RATE)
            {
                $this->ajResponse['st'] = 116;
                $this->ajResponse['msg'] = '已经达到意向客户的标记上限！';
            }
        }
        else
        {
            $this->ajResponse['st'] = 115;
            $this->ajResponse['msg'] = '对不起，没有权限标记为”意向客户“！';
        }
    }
}

$app = new App();
$app->run();