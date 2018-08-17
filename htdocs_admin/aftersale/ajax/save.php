<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/7/13
 * Time: 16:35
 */
include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
	private $info;
	private $orderInfo;
    private $exec;
    private $wxImgMediaIds = array();   //存储通过微信上传的图片的mediaid
    private $picUrls = array();

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->info = array(
			'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'typeid' => Tool_Input::clean('r', 'typeid', TYPE_UINT),
			'contact_name' => Tool_Input::clean('r', 'contact_name', TYPE_STR),
			'contact_mobile' => Tool_Input::clean('r', 'contact_mobile', TYPE_STR),
			'content' => Tool_Input::clean('r', 'content', TYPE_STR),
            'duty_department' => Tool_Input::clean('r', 'exec_role', TYPE_UINT),
			'exec_suid' => Tool_Input::clean('r', 'exec_suid', TYPE_UINT),
			'rid' => Tool_Input::clean('r', 'rid', TYPE_STR),
			'objid' => Tool_Input::clean('r', 'objid', TYPE_STR),
			'fb_type' => Tool_Input::clean('r', 'fb_type', TYPE_UINT),
			'fb_uid' => Tool_Input::clean('r', 'fb_uid', TYPE_UINT),
			'join_suids' => Tool_Input::clean('r', 'copy_uids', TYPE_STR),
			'contact_way' => Tool_Input::clean('r', 'contact_way', TYPE_STR),
            'pic_ids' => Tool_Input::clean('r', 'pic_ids', TYPE_STR),
		);
        $this->wxImgMediaIds = json_decode(Tool_Input::clean('r', 'wx_img_mediaid', TYPE_STR));
	}

    protected function checkAuth()
    {
        parent::checkAuth('/aftersale/edit');
    }

	protected function checkPara()
	{

		if (empty($this->info['content']))
		{
			throw new Exception('aftersale: description empty');
		}
		//检查权限
//		$uid = $this->_uid;
//		$this->isAftersale = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_AFTER_SALE);
//		if (!in_array($uid, Conf_Aftersale::$SALE_EXEC) && !$this->isAftersale)
//		{
//			throw new Exception('aftersale: without authority');
//		}
//		if (in_array($this->info['type'], Conf_Aftersale::$CATE_ORDER))
//		{
		$oids = explode(',',str_replace('，',',',$this->info['objid']));
		$this->info['objid'] = str_replace('，',',',$this->info['objid']);
		$this->info['rid'] = str_replace('，',',',$this->info['rid']);
		if ($this->info['objid']) {
			$this->orderInfo = Order_Api::getListByPk($oids);
		}
		if (!empty($this->orderInfo)) {
			//根据不同的反馈人类型及投诉类型获得订单相关的联系人信息
				switch ($this->info['fb_type']) {
					case 1:
						$cid = $this->orderInfo[$oids[0]]['cid'];
						foreach ( $this->orderInfo as $order) {
							if ($cid != $order['cid'] || $order['status'] != Conf_Base::STATUS_NORMAL)
							{
								throw new Exception('aftersale: must be the same cid');
							}
						}
						$this->exec= Crm2_Api::getCustomerInfo($cid);
						$this->exec = $this->exec['customer'];

						break;
					case 2:
						$dphone = $this->orderInfo[$oids[0]]['driver_phone'];
						foreach ( $this->orderInfo as $order) {
							if ($dphone != $order['driver_phone'] || $order['status'] != Conf_Base::STATUS_NORMAL)
							{
								throw new Exception('aftersale: must be the same did');
							}
						}

						if(empty($dphone))
						{
                            throw new Exception('aftersale: order has no driver');
                        }

						$this->exec = Logistics_Api::getDriverList('mobile='.$dphone,1,1);
						$this->exec = $this->exec['list'][0];
						$this->exec['cid'] = $this->exec['did'];
						break;
					case 3:
						$mphone = $this->orderInfo[$oids[0]]['carrier_phone'];
						foreach ( $this->orderInfo as $order) {
							if ($mphone != $order['carrier_phone'] || $order['status'] != Conf_Base::STATUS_NORMAL)
							{
								throw new Exception('aftersale: must be the same mid');
							}
						}
						$this->exec = Logistics_Api::getCarrierList('mobile='.$mphone ,1,1);
						$this->exec = $this->exec['list'][0];
						break;
					case 4:
						$searchConf = array('name'=>$this->info['contact_name'],'mobile'=>$this->info['contact_mobile']);
						$this->exec= Admin_Api::getStaffList(0, 1000, $searchConf);
						$this->exec = $this->exec['list'][0];
						$this->exec['cid'] = $this->exec['suid'];
						break;
				}
		}else {
		    if($this->info['fb_uid']>0){
                switch ($this->info['fb_type']) {
                    case 1:
                        $this->exec= Crm2_Api::getCustomerInfo($this->info['fb_uid']);
                        $this->exec = $this->exec['customer'];
                        break;
                    case 2:
                        $this->exec = Logistics_Api::getDriver($this->info['fb_uid']);
                        $this->exec['cid'] = $this->exec['did'];
                        break;
                    case 3:
                        $this->exec = Logistics_Api::getCarrier($this->info['fb_uid']);
                        break;
                    case 4:
                        $this->exec= Admin_Api::getStaff($this->info['fb_uid']);
                        $this->exec['cid'] = $this->exec['suid'];
                        break;
                }
            }else{
                $flag_status = false;
                $searchConf = array();
                if (!empty($this->info['contact_name']))
                {
                    $searchConf['name'] = $this->info['contact_name'];
                    $flag_status = true;
                }
                if (!empty($this->info['contact_mobile']))
                {
                    $searchConf['mobile'] = $this->info['contact_mobile'];
                    $flag_status = true;
                }
                if(!$flag_status){
                    throw new Exception('aftersale: 姓名或者电话必填');
                }
                switch ($this->info['fb_type']) {
                    case 1:
                        $this->exec= Crm2_Api::getCustomerListForAdmin($searchConf, $this->_user);
                        $this->exec = reset($this->exec['data']);
                        break;
                    case 2:
                        $this->exec = Logistics_Api::getDriverList($searchConf);
                        $this->exec = reset($this->exec['list']);
                        $this->exec['cid'] = $this->exec['did'];
                        break;
                    case 3:
                        $this->exec = Logistics_Api::getCarrierList($searchConf);
                        $this->exec = reset($this->exec['list']);
                        break;
                    case 4:
                        $this->exec= Admin_Api::getStaffList(0,10,$searchConf);
                        $this->exec = reset($this->exec['list']);
                        $this->exec['cid'] = $this->exec['suid'];
                        break;
                }
            }
		}
//		}
//		else
//		{
//			$this->customer = Crm2_Api::getCustomerInfo($this->info['objid']);
//			if (empty($this->customer['customer']))
//			{
//				throw new Exception('user:user not exist');
//			}
//		}

		$this->info['create_suid'] = $this->_uid;
		if (empty($this->info['duty_department']))
		{
			$this->info['exec_suid'] = $this->_uid;
            $user_info = Admin_Api::getStaff($this->_uid);
            $this->info['duty_department'] = $user_info['role'];
		}
	/*	if (empty($this->info['join_suids'])) {
			$this->info['join_suids'] = $this->info['create_suid'].','.$this->info['exec_suid'];
		}else {
			$this->info['join_suids'] = $this->info['create_suid'].','.$this->info['exec_suid'].','.$this->info['join_suids'];
		}*/

	}

	protected function main()
	{
		if (empty($this->info['contact_name']))
		{
			$this->info['contact_name'] = $this->exec['name'];
		}
		if (empty($this->info['contact_mobile']))
		{
			$this->info['contact_mobile'] = $this->exec['mobile'];
		}
		if (empty($this->info['fb_uid']) && $this->exec['cid'])
		{
			$this->info['fb_uid'] = $this->exec['cid'];
		}
		if (empty($this->id))
		{
            if (!empty($this->wxImgMediaIds))
            {
                $this->_downImageFromWX2HC();
                $this->info['pic_ids'] = implode(',', $this->picUrls);
            }

			$this->info['exec_status'] = Conf_Aftersale::STATUS_AFTER_CREATE;
            $this->id = Aftersale_Api::add($this->info);
			//在工单流程单里写入数据，创建
			$logCreate= array(
				'sid' => $this->id,
				'exec_suid' => $this->_uid,
				'action' => Conf_Aftersale_Log::ACTION_NEW,
				'after_step' => Conf_Aftersale::STATUS_CREATE,
			);

			//在工单流程中写入数据，指派
			$logAssign= array(
				'sid' => $this->id,
				'exec_suid' => $this->info['exec_suid'],
				'action' => Conf_Aftersale_Log::ACTION_UNDEAL,
				'after_step' => Conf_Aftersale::STATUS_AFTER_CREATE,
			);
            $user_info = Admin_Api::getStaff($this->_uid);
			$logCreate['exec_department'] = $user_info['role'];
            $logAssign['exec_department'] = $this->info['duty_department'];

			Aftersale_Log_Api::add($logCreate);
			Aftersale_Log_Api::add($logAssign);
		}
		else
		{
			Aftersale_Api::update($this->id, $this->info);
		}
		if($this->info['exec_suid']>0 && $this->info['exec_suid'] != $this->_uid)
        {
            $after_info = Aftersale_Api::getDetail($this->id);
            $messageData = array(
                'm_type' => 2,
                'typeid' => $this->id,
                'content' => '（【类型】'.$after_info['_type'].'；【状态】'.$after_info['_exec_status'].'）需要处理。',
                'send_suid' => $this->_uid,
                'receive_suid' => $this->info['exec_suid']
            );
            Admin_Message_Api::create($messageData);
        }
	}

	protected function outputPage()
	{
		$result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}

    private function _downImageFromWX2HC()
    {
        foreach($this->wxImgMediaIds as $mediaId)
        {
            $pic = WeiXin_Api::downloadImageFromWX2HC($mediaId);
            $this->picUrls[] = 'http://'.PIC_HOST.'/wx_pic/'.substr($pic, 0, 8).'/'.$pic;
        }
    }
}

$app = new App('pri');
$app->run();