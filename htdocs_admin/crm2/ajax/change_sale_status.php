<?php

/**
 * 更改客户的销售状态的.
 */

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    
    const PRI_TELE_MAX_NUM = 1000;  //电销上限
    const PRI_BMS_MAX_NUM = 1000;   //BMS上限
    const PRI_BDS_MAX_NUM = 500;    //BDS上限
    
    private $_allChgSt = array('private', 'public', 'inner', 'invalid');
    
    private $cid;
    private $chgSt;
    
    private $upCustomerInfo = array();
    
    private $customerInfo;
    private $ajResponse;
    
    private $hasSuperAuth = false;    //超级权限，任意执行公私海客户
    
    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->chgSt = Tool_Input::clean('r', 'st', TYPE_STR);
        
        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
        );
        
        $this->_isHasSuperAuth();
    }
    
    protected function checkAuth()
    {
        $chgSt = isset($_REQUEST['st'])? $_REQUEST['st']: '';
        
        switch($chgSt)
        {
            case 'public':  //公海
                parent::checkAuth(array('hc_crm2_into_public', 'hc_crm2_ppii_super')); break;
            
            case 'private': //私海
                parent::checkAuth(array('hc_crm2_into_private', 'hc_crm2_ppii_super')); break;
            case 'inner':   //内海
                parent::checkAuth(array('hc_crm2_into_inner', 'hc_crm2_ppii_super')); break;
            case 'invalid':
                parent::checkAuth(array('hc_crm2_into_invalid', 'hc_crm2_ppii_super')); break;
            default:
                throw new Exception('common:permission denied');
        }
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
    
    
    protected function main()
    {
        if ($this->ajResponse['st'] != 0)
        {
            return;
        }
        
        // 更改销售状态
        $this->upCustomerInfo['chg_sstatus_time'] = date('Y-m-d H:i:s');
        
        $upSt = Crm2_Api::updateCustomerInfo($this->cid, $this->upCustomerInfo);

        if ($upSt)
        {
            // 记录客户跟踪信息
            $content = '客户销售状态变更：从【'. Conf_User::$Customer_Sale_Status[$this->customerInfo['sale_status']]
                    . '】变更为【'. Conf_User::$Customer_Sale_Status[$this->upCustomerInfo['sale_status']].'】';
            $trackingInfo = array(
                'cid' => $this->cid,
                'edit_suid' => $this->_uid,
                'content' => $content,
                'type' => Conf_User::CT_CHG_SALE_ST,
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
    
    
    // 查看权限.
    private function _checkOpAuth()
    {
        switch($this->chgSt)
        {
            case 'public':  //公海
                $this->upCustomerInfo['sales_suid'] = 0;
                $this->upCustomerInfo['sale_status'] = Conf_User::CRM_SALE_ST_PUBLIC;
                
                $this->_chkPublic();break;
            case 'private': //私海
                $this->upCustomerInfo['sales_suid'] = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) ? $this->_uid : 0;
                $this->upCustomerInfo['sale_status'] = Conf_User::CRM_SALE_ST_PRIVATE;
                
                $this->_chkPrivate();break;
            case 'inner':   //内海
                $this->upCustomerInfo['sales_suid'] = 0;
                $this->upCustomerInfo['sale_status'] = Conf_User::CRM_SALE_ST_INNER;
                
                $this->_chkInner();break;
            case 'invalid':
                $this->upCustomerInfo['sales_suid'] = 0;
                $this->upCustomerInfo['sale_status'] = Conf_User::CRM_SALE_ST_ABANDON;
                
                $this->_chkInvalid();break;
            default:
                $this->ajResponse['st'] = 110;
                $this->ajResponse['msg'] = '操作类型错误！';
        }
    }
    
    /**
     * 将客户 放回公海.
     * 
     *  - 销售自己的私海 -> 公海
     *  - 广义管理员 任意海 -> 公海
     */
    private function _chkPublic()
    {
//        $this->ajResponse['st'] = 111;
//        $this->ajResponse['msg'] = '对不起，即日起，暂不能再扔公海！';
//        return;
        
        // 销售自己的私海 -> 公海
        if ( ($this->customerInfo['sale_status']==Conf_User::CRM_SALE_ST_PRIVATE
                && $this->_uid==$this->customerInfo['sales_suid']) ||$this->hasSuperAuth )
        {
            $this->ajResponse['st'] = 0;
        }
        else
        {
            $this->ajResponse['st'] = 111;
            $this->ajResponse['msg'] = '对不起，没有权限变更”公海“！';
        }
        
        if ($this->_user['kind'] == Conf_Admin::JOB_KIND_BDS_SALE)
        {
            $oo = new Order_Order();
            $orderNum = $oo->getSummaryOfCustomer($this->cid, '', Conf_Order::ORDER_STEP_NEW);
            
            if ($orderNum >0 )
            {
                $this->ajResponse['st'] = 120;
                $this->ajResponse['msg'] = '对不起，BDS的下单客户不能扔回公海！';
            }
        }
        
        // 余额大于49元不能进入公海
        if ($this->customerInfo['account_amount'] > 4900)
        {
            $this->ajResponse['st'] = 116;
            $this->ajResponse['msg'] = '客户有余额不能进入“公海”！';
        }
    }
    
    /**
     * 将客户 收入私海.
     * 
     *  - 公海&&销售->私海
     *  - 管理员任意海->私海
     */
    private function _chkPrivate()
    {
        // 公海&&销售->私海
        if ( ($this->customerInfo['sale_status']==Conf_User::CRM_SALE_ST_PUBLIC
            && Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW)) ||$this->hasSuperAuth )
        {
            $this->_canFetch2PrivateBySalerKind();
        }
        else
        {
            $this->ajResponse['st'] = 112;
            $this->ajResponse['msg'] = '对不起，没有权限变更”私海“！';
        }
        
        // 兼职不能捡公海客户；from 任慧娟 所有兼职不能捡公海客户（2016-06-01）
        if ($this->_user['kind'] == Conf_Admin::JOB_KIND_PARTTIME)
        {
            $this->ajResponse['st'] = 118;
            $this->ajResponse['msg'] = '不能收入到私海，兼职！';
        }
        
        // 判断之前是不是自己的客户？如果是自己客户 3天内不能捡回
        if ($this->ajResponse['st']==0)
        {
            $searchTracking = array(
                'cid' => $this->cid,
                'edit_suid' => $this->_uid,
                'type' => Conf_User::CT_CHG_SALE_ST,
                'from_date' => date('Y-m-d H:i:s', time()-3*24*3600),
            );
            $trackingInfo = Crm2_Api::getTrackingsBaseInfo($searchTracking, 0, 0);
            
            if ($trackingInfo['total']!=0)
            {
                $this->ajResponse['st'] = 117;
                $this->ajResponse['msg'] = '该客户是您在3天内放入“公海”， 3天后可捡入私海！';
            }
        }
    }
    
    /**
     * 是否可以捡公海客户 - 根据销售的角色判断.
     * 
     * 电销：0类，1类客户
     * BDS：2类，3类客户
     * BMS：4类客户
     * 
     * 其他销售没有限制
     */
    private function _canFetch2PrivateBySalerKind()
    {
        $saleLevel = $this->customerInfo['level_for_saler'];
        
        $this->ajResponse['st'] = 0;
        
        // 私海客户数
        $cc2 = new Crm2_Customer();
        $where = sprintf('status=%d and sales_suid=%d', Conf_Base::STATUS_NORMAL, $this->_uid);
        $total = $cc2->getTotalByWhere($where);
        
        switch($this->_user['kind'])
        {
            case Conf_Admin::JOB_KIND_TELE_SALE:
                if (0&&!in_array($saleLevel, Conf_User::$Grouped_Sales_Levels[Conf_Admin::JOB_KIND_TELE_SALE]))
                {
                    $this->ajResponse['st'] = 130;
                    $this->ajResponse['msg'] = '电销只能捡去0类，1类公海客户！';
                }
                else if ($total > self::PRI_TELE_MAX_NUM)
                {
                    $this->ajResponse['st'] = 131;
                    $this->ajResponse['msg'] = '您的私海客户已经达到上限（私海数量：'. $total.'）';
                }
                break;
            case Conf_Admin::JOB_KIND_BDS_SALE:
                if (0&&!in_array($saleLevel, Conf_User::$Grouped_Sales_Levels[Conf_Admin::JOB_KIND_BDS_SALE]))
                {
                    $this->ajResponse['st'] = 132;
                    $this->ajResponse['msg'] = 'BDS只能捡去2类，3类公海客户！';
                }
                else if ($total > self::PRI_BDS_MAX_NUM)
                {
                    $this->ajResponse['st'] = 133;
                    $this->ajResponse['msg'] = '您的私海客户已经达到上限（私海数量：'. $total.'）';
                }
                break;
            case Conf_Admin::JOB_KIND_BMS_SALE:
                if (0&&!in_array($saleLevel, Conf_User::$Grouped_Sales_Levels[Conf_Admin::JOB_KIND_BMS_SALE]))
                {
                    $this->ajResponse['st'] = 134;
                    $this->ajResponse['msg'] = 'BDM只能捡去4类公海客户！';
                }
                else if ($total > self::PRI_BMS_MAX_NUM)
                {
                    $this->ajResponse['st'] = 135;
                    $this->ajResponse['msg'] = '您的私海客户已经达到上限（私海数量：'. $total.'）';
                }
                break;
            default:
                break;
        }
    }
    
    /**
     * 将客户 收回内海.
     * 
     *  - 任意客户  广义管理
     */
    private function _chkInner()
    {
        if (!$this->hasSuperAuth)
        {
            $this->ajResponse['st'] = 112;
            $this->ajResponse['msg'] = '对不起，没有权限变更”内海“！';
        }
    }
    
    /**
     * 将客户 标记为无效客户.
     * 
     *  - 私海客户  销售自己的客户 or 广义管理员
     *  - 公海/内海客户     广义管理员
     */
    private function _chkInvalid()
    {
        if ( ($this->customerInfo['sale_status']==Conf_User::CRM_SALE_ST_PRIVATE
              && $this->_uid==$this->customerInfo['sales_suid']) ||$this->hasSuperAuth)
        {
            $this->ajResponse['st'] = 0;
        }
        else
        {
            $this->ajResponse['st'] = 114;
            $this->ajResponse['msg'] = '对不起，没有权限变更”无效客户“！';
        }
    }
    
    /**
     * 超级权限：将任意客户放入的公私海.
     */
    private function _isHasSuperAuth()
    {
        $isAdmin = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW);
        
        $this->hasSuperAuth = $isAdmin? true: false;
        
    }
}

$app = new App();
$app->run();