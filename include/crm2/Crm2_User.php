<?php
/**
 * 客户 - 用户相关
 */
class Crm2_User extends Base_Func
{
    private static $_instance = null;
    private $_dao = null;
    
    
    function __construct()
    {
        parent::__construct();
        $this->_dao = new Data_Dao('t_user');
    }
    
    public static function getInstance()
    {
        if (empty(self::$_instance))
        {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    public function add($cid, $uinfo)
    {
        assert( !empty($cid) );
        assert( !empty($uinfo) );
        assert( !empty($uinfo['mobile']) );
        
        $uinfo['cid'] = $cid;
        
        $uinfo['mobile'] = trim($uinfo['mobile']);
        
        //判断mobile是否可以注册
        $oldUser = $this->getByMobile($uinfo['mobile']);
        if (!empty($oldUser))
        {
            throw new Exception('user:mobile occupied');
        }
        
        //初始化密码和salt
		$salt = mt_rand(1000, 9999);
		$password = !empty($uinfo['password'])? $uinfo['password']: substr($uinfo['mobile'], -6, 6);
		$uinfo['password'] = Crm2_Auth_Api::createPasswdMd5($password, $salt);
		$uinfo['salt'] = $salt;
        
        $uinfo['ctime'] = date('Y-m-d H:i:s');
        $uid = $this->_dao->add($uinfo);
        
        $verify = Crm2_Auth_Api::createVerify($uid, $cid, $uinfo['password']);

		return array('uid' => $uid, 'verify' => $verify);
    }
    
    
    public function update($uid, $uinfo)
    {
        $_uid = intval($uid);
        
        assert( $_uid > 0 );
        assert( !empty($uinfo) );
        
        if (!empty($uinfo['mobile']))
		{
            $uinfo['mobile'] = trim($uinfo['mobile']);
            
			$oldUser = $this->getByMobile($uinfo['mobile']);
            
			if (!empty($oldUser) && !array_key_exists($uid, $oldUser))
			{
				throw new Exception('user:mobile occupied');
			}
		}

        $affectedRows = $this->_dao->update($uid, $uinfo);
        
        return $affectedRows;
    }

    public function updateByMobile($mobile, $uinfo)
    {

        // 不允许更新mobile，唯一标示
        if (isset($uinfo['mobile']))
        {
            unset($uinfo['mobile']);
        }

        assert( $mobile>0 );
        assert( !empty($uinfo) );

        $where = 'mobile='.$mobile;
        $affectedRows = $this->_dao->updateWhere($where, $uinfo);

        return $affectedRows;
    }
    
    /**
     * 按cid：更新cid对应的全部user的信息.
     * 
     * @param int $cid
     * @param array $uinfo
     */
    public function updateByCid($cid, $uinfo)
    {
        $_cid = intval($cid);
        
        // 不允许更新mobile，唯一标示
        if (isset($uinfo['mobile']))
        {
            unset($uinfo['mobile']);
        }
        
        assert( $_cid>0 );
        assert( !empty($uinfo) );
        
        $where = 'cid='.$cid;
        $affectedRows = $this->_dao->updateWhere($where, $uinfo);
        
        return $affectedRows;
    }
    
    public function get($uid)
    {
        $_uid = intval($uid);
        assert( $_uid > 0 );
        
        $uinfo = $this->_dao->get($uid);
        
        return $uinfo;
    }
    
    public function getBluk($uids, $field=array('*'))
    {
        assert( !empty($uids) );
        
        $uinfos = $this->_dao->setFields($field)->getList($uids);
        
        return $uinfos;
    }
    
    public function getByMobile($mobile, $getAll=false)
    {
        $_mobile = trim(strval($mobile));
        
        if (empty($_mobile)) return array();
        
        if (!$getAll)
        {
            $where = array('mobile' => $mobile, 'status'=>Conf_Base::STATUS_NORMAL);
        }
        else
        {
            $where = array('mobile' => $mobile);
        }
        
        $userInfo = $this->_dao->getListWhere($where);

		return $userInfo;
    }
    
    public function getUsersOfCustomer($cid, $fields=array('*'))
    {
        assert( !empty($cid) );
        
        $where = array(
            'cid' => $cid, 
            'status'=>  Conf_Base::STATUS_NORMAL,
        );
        $userInfos = $this->_dao->setFields($fields)->getListWhere($where);
        
        return $userInfos;
    }
    
    
    public function getUserByWhere($where, $start=0, $num=20, $field=array('*'))
    {
        return $this->_dao->setFields($field)->limit($start, $num)->getListWhere($where);
    }

    public function getListByCids($cids, $field)
    {
        $where = sprintf('cid in (%s)', implode(',', $cids));
        return $this->_dao->setFields($field)->getListWhere($where,false);
    }
    
    /**
     * 搜索符合条件的User.
     * 
     * @param array $conf
     * @param array $field
     * @param int $start
     * @param int $num
     * @param string $orderBy
     */
    public function search($conf, $field=array('*'), $start=0, $num=20, $orderBy='uid')
    {   
        $where = 'status='.Conf_Base::STATUS_NORMAL;
        
        if (empty($conf['cid']))
        {
            $where .= ' and cid='.$conf['cid'];
        }
        if (empty($conf['mobile']))
        {
            $where .= sprintf(' and mobile like "%%%s%%"', $conf['mobile']);
        }
        if (empty($conf['name']))
        {
            $where  .= sprintf(' and name like "%%%s%%"', $conf['name']);
        }
        if (empty($conf['sales_suid']))
        {
            $salesSuid = is_array($conf['sales_suid'])? implode(',', $conf['sales_suid']): $conf['sales_suid'];
            $where .= sprintf(' and cid in (select cid from t_customer where status=0 and sales_suid in (%s) )', $salesSuid);
        }
        
        $userInfo['total'] = $this->_dao->getTotal($where);
        $userInfo['data'] = array();

        if ($userInfo['total'])
        {
            $userInfo['data'] = $this->_dao
                            ->setFields($field)
                            ->order($orderBy, 'desc')
                            ->limit($start, $num)
                            ->getListWhere($where);
        }
        
        return $userInfo;
    }
    

    /**
     * 获取客户的积分.
     * 
     * @param int $uid
     * @param array $userInfo
     */
    public function getUserPoint($uid, $userInfo=array())
    {
        if (empty($userInfo))
        {
            $field = array('frozen_point', 'vaild_point');
            $userInfo = $this->_dao->setFields($field)->get($uid);
        }
        
        return array(
            'frozen_point' => $userInfo['frozen_point'],
            'vaild_point'  => $userInfo['vaild_point'],
            'total_point'  => $userInfo['vaild_point'] + $userInfo['frozen_point'],
        );
    }
    
    /**
     * 计算客户的等级.
     * 
     * @rule 按照上个月的消费（订单出库&&完全付款）
     * 
     * @param int $uid
     * @param array $userInfo
     */
    public function calUserGrade($uid, $userInfo=array())
    {
        if (empty($userInfo))
        {
            $userInfo = $this->get($uid);
        }
        
        // 异常客户.
        if (empty($userInfo) || $userInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            return false;
        }
        
        $btime = date('Y-m-01', strtotime('-1 month')); //上月第一天
        $etime = date('Y-m-01');    //本月第一天
        
        // 获取客户的消费
        $oo = new Order_Order();
        $field = array('sum(price+freight+customer_carriage-privilege-refund) as real_consume');
        $where = sprintf('status=0 and cid=%d and uid=%d and step>=%d and paid=%d and delivery_date>="%s 00:00:00" and delivery_date<"%s 00:00:00"',
                        $userInfo['cid'], $userInfo['uid'], Conf_Order::ORDER_STEP_PICKED, Conf_Order::HAD_PAID, $btime, $etime);
        
        $orderInfo = $oo->getListRawWhereWithoutTotal($where, '', 0, 0, $field);
        $consumeInfo = current($orderInfo);
        
        $memberGrade = Conf_User::Member_Bronze;
        foreach(Conf_User::consumeInterval4MemberGrade() as $_grade => $interval)
        {
            $memberGrade = $_grade;
            if (($consumeInfo['real_consume']/100) > $interval['max']) 
            {
                continue;
            }
            else
            {
                break;
            }
        }
        
        return $memberGrade;
    }
}
