<?php

/**
 * 修改销售对客户的标记.
 * 
 * @rule
 *      1 电销标记为【已外呼】将客户修改为1类客户，并更新时间 chg_sstatus_time
 *      2 电销标记为【2，3类】判断客户是否下过单
 *          如果没有下过单标记为2，3类客户，并更新时间 chg_sstatus_time，mark_intend_time
 *          如果下过单标记为4类客户，并更新时间 chg_sstatus_time，mark_intend_time
 *      3 电销标记为【4类】判断是否下过单，更新时间 chg_sstatus_time，mark_intend_time
 *          如果下过单标记为：4类
 *          如果没有下过统一标记为：2类
 *      4 BDS 标记2，3类客户 （主要是2类，3类客户转换，提示自己客户级别）
 *          如果 客户已经下单，直接标记为4类客户
 *          如果 客户没有下单，标记为2类或3类客户
 * 
 *      5 BDS，BMS 系统自动标记（脚本标记）
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $_allChgSt = array('mark', 'has_call');
    
    private $cid;
    private $chgSt;
    private $toLevel;
    
    private $chgStDesc = '';
    
    private $upCustomerInfo = array();
    private $ajResponse;
    private $customerInfo;

    private $isGeneralAdmin;
    
    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->chgSt = Tool_Input::clean('r', 'st', TYPE_STR);
        $this->toLevel = Tool_Input::clean('r', 'to_level', TYPE_UINT);
        
        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
        );
        
        $this->isGeneralAdmin = Admin_Role_Api::isAdmin($this->_uid, $this->_user)
                            || Admin_Role_Api::isChiefSaler($this->_user);
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
        
        $this->_checkOpAuth();
    }
    
    protected function checkAuth()
    {
        parent::checkAuth();
		if (!Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW)
            && !Admin_Role_Api::isAdmin($this->_uid, $this->_user))
		{
			throw new Exception('common:permission denied');
		}
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
            $content = '客户销售级别变更：从【'. Conf_User::$Crm_Sales_Levels[$this->customerInfo['level_for_saler']]
                        .'（'.$this->customerInfo['level_for_saler'].'类）】'
                        . '变更为【'. Conf_User::$Crm_Sales_Levels[$this->upCustomerInfo['level_for_saler']]
                        .'（'.$this->upCustomerInfo['level_for_saler'].'类）】';
            $trackingInfo = array(
                'cid' => $this->cid,
                'edit_suid' => $this->_uid,
                'content' => $content,
                'type' => Conf_User::CT_CHG_SALE_LEVEL,
                'from_status' => $this->customerInfo['level_for_saler'],
                'to_status' => $this->upCustomerInfo['level_for_saler'],
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
    
    
    protected function _checkOpAuth()
    {
        switch($this->chgSt)
        {
            // 电销 标记为【已外呼，并标记为1类客户】
            case 'has_call':
                $this->_hasCall();
                break;
            case 'mark':
                $this->_mark();
                break;
            default :
                $this->ajResponse['st'] = 110;
                $this->ajResponse['msg'] = '操作类型错误！';
        }
    }
    
    // 电销 标记外呼
    private function _hasCall()
    {
        if (!$this->customerInfo['level_for_saler']==Conf_User::SALES_LEVEL_NOCALL
            && !$this->customerInfo['level_for_saler']==Conf_User::SALES_LEVEL_NOINTEND)
        {
            $this->ajResponse['st'] = 111;
            $this->ajResponse['msg'] = '客户的销售状态错误，不能标记【已外呼】！';
        }
        else if(!($this->_uid=$this->customerInfo['sales_suid']&&$this->_user['kind']==Conf_Admin::JOB_KIND_TELE_SALE )
                && !$this->isGeneralAdmin)
        {
            $this->ajResponse['st'] = 112;
            $this->ajResponse['msg'] = '权限错误，不能标记【已外呼】！';
        }
        else
        {
            $this->upCustomerInfo['level_for_saler'] = Conf_User::SALES_LEVEL_NOINTEND;
            $this->upCustomerInfo['chg_sstatus_time'] = date('Y-m-d H:i:s');
        }
        
        $this->chgStDesc = '电销外呼';
    }
    
    /**
     * 修改客户的营销类别.
     * 
     *  1. 电销修改
     *      0，1 -> 2,3,4类：修改客户销售类型，进公海，修改chg_sstatus_time，mark_intend_time
     *  2. BDS修改
     *      0，1，2，3 -> 2,3：修改客户销售类型
     *      0，1，2，3 -> 4：修改客户销售类型，进公海，修改chg_sstatus_time
     *          
     */
    private function _mark()
    {
        if ($this->isGeneralAdmin)
        {
            $this->_markByAdmin();
            return;
        }
        
        switch($this->_user['kind'])
        {
            case Conf_Admin::JOB_KIND_TELE_SALE: //电销
                $this->_markByTeleSaler();
                break;
            
            case Conf_Admin::JOB_KIND_BDS_SALE: //BDS
                $this->_markByBDS();
                break;
            
            default:
                $this->ajResponse['st'] = 113;
                $this->ajResponse['msg'] = '权限错误，不能标记，流转客户！';
        }
        
        $this->chgStDesc = '客户流转';
    }
    
    // 管理员标记; 管理员标记的客户，一律进入公海
    private function _markByAdmin()
    {
        // 已下单客户, 只能标记为4类客户
        if ($this->customerInfo['order_num'] > 0)
        {
            if ($this->toLevel != Conf_User::SALES_LEVEL_HADORDER)
            {
                $this->ajResponse['st'] = 114;
                $this->ajResponse['msg'] = '【操作失败】已下单客户，只能标记为4类客户！';
            }
        }
        else
        {
            if ($this->toLevel != Conf_User::SALES_LEVEL_INTEND
                || $this->toLevel != Conf_User::SALES_LEVEL_WILLORDER)
            {
                $this->ajResponse['st'] = 115;
                $this->ajResponse['msg'] = '【操作失败】未下单客户，只能标记为2,3类客户！';
            }
            $this->upCustomerInfo['mark_intend_time'] = date('Y-m-d H:i:s');
        }
        
        $this->upCustomerInfo['chg_sstatus_time'] = date('Y-m-d H:i:s');
        $this->upCustomerInfo['sale_status'] = Conf_User::CRM_SALE_ST_PUBLIC;
        $this->upCustomerInfo['level_for_saler'] = $this->toLevel;
        $this->upCustomerInfo['sales_suid'] = 0;
    }
    
    private function _markByTeleSaler()
    {
        if ($this->_uid!=$this->customerInfo['sales_suid'])
        {
            $this->ajResponse['st'] = 114;
            $this->ajResponse['msg'] = '不是本人客户，不能标记！';
        }
        
        if ($this->customerInfo['order_num']>0 && $this->toLevel!=Conf_User::SALES_LEVEL_HADORDER)
        {
            $this->toLevel = Conf_User::SALES_LEVEL_HADORDER;
        }
        
        $this->upCustomerInfo['mark_intend_time'] = date('Y-m-d H:i:s'); 
        $this->upCustomerInfo['chg_sstatus_time'] = date('Y-m-d H:i:s');
        $this->upCustomerInfo['sale_status'] = Conf_User::CRM_SALE_ST_PUBLIC;
        $this->upCustomerInfo['level_for_saler'] = $this->toLevel;
        $this->upCustomerInfo['sales_suid'] = 0;
    }
    
    private function _markByBDS()
    {
        if ($this->_uid!=$this->customerInfo['sales_suid'])
        {
            $this->ajResponse['st'] = 115;
            $this->ajResponse['msg'] = '【操作失败】不是本人客户，不能标记！';
        }
        
        // 标记为2，3类，不进入公海，只修改客户销售类型
        if ($this->toLevel==Conf_User::SALES_LEVEL_INTEND|| $this->toLevel==Conf_User::SALES_LEVEL_WILLORDER)
        {
            $this->upCustomerInfo['level_for_saler'] = $this->toLevel;
        }
        else
        {
            $this->ajResponse['st'] = 116;
            $this->ajResponse['msg'] = '【操作失败】系统自动标记！';
            
//            $this->upCustomerInfo['chg_sstatus_time'] = date('Y-m-d H:i:s');
//            $this->upCustomerInfo['sale_status'] = Conf_User::CRM_SALE_ST_PUBLIC;
//            $this->upCustomerInfo['level_for_saler'] = $this->toLevel;
//            $this->upCustomerInfo['sales_suid'] = 0;
        }
    }
}

$app = new App();
$app->run();