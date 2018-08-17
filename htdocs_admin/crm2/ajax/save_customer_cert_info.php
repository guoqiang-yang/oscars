<?php
/**
 * Created by PhpStorm.
 * User: baolong
 * Date: 2018/3/22
 * Time: 上午9:57
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $customerType;
    private $identity;
    private $msg;
    private $oldInfo;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_customer');
    }

    protected function getPara()
    {
        $this->customerType = Tool_Input::clean('r', 'identity', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $cc = new Crm2_Certification();
        $this->oldInfo = $cc->getByCid($this->cid);

        if ($this->customerType == Conf_User::CRM_IDENTITY_NEW && !empty($this->oldInfo['type']))
        {
            $this->customerType = $this->oldInfo['type'];
        }

        if ($this->customerType == Conf_User::CRM_IDENTITY_PERSONAL) {
            $this->identity['real_name'] = Tool_Input::clean('r', 'real_name', TYPE_STR);
            $this->identity['id_number'] = Tool_Input::clean('r', 'id_number', TYPE_STR);
            $this->identity['band_card_number'] = Tool_Input::clean('r', 'band_card_number', TYPE_STR);
            $this->identity['mobile'] = Tool_Input::clean('r', 'identity_mobile', TYPE_STR);
        } elseif ($this->customerType == Conf_User::CRM_IDENTITY_COMPANY)
        {
            $this->identity['company_name'] = Tool_Input::clean('r', 'company_name', TYPE_STR);
            $this->identity['legal_person_name'] = Tool_Input::clean('r', 'legal_person_name', TYPE_STR);
            $this->identity['legal_person_id_number'] = Tool_Input::clean('r', 'legal_person_id_number', TYPE_STR);
            $this->identity['social_credit_number'] = Tool_Input::clean('r', 'social_credit_number', TYPE_STR);
        }
    }

    protected function checkPara()
    {
        if ($this->customerType == Conf_User::CRM_IDENTITY_PERSONAL && (empty($this->identity['real_name']) || empty($this->identity['id_number']) || empty($this->identity['mobile'])))
        {
            throw new Exception('common:params error');
        }

        if ($this->customerType == Conf_User::CRM_IDENTITY_COMPANY && (empty($this->identity['company_name']) || empty($this->identity['legal_person_name']) || empty($this->identity['legal_person_id_number']) || empty($this->identity['social_credit_number'])))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
    {
        if((empty($this->oldInfo) || $this->oldInfo['step'] == Conf_User::CERTIFICATE_DENY)
            && ((!empty($this->identity['real_name']) && !empty($this->identity['id_number']) && !empty($this->identity['mobile']))
                || (!empty($this->identity['company_name']) && !empty($this->identity['legal_person_name']) && !empty($this->identity['legal_person_id_number']) && !empty($this->identity['social_credit_number']))))
        {
            Crm2_Certification_Api::certificate($this->cid, 0, $this->customerType, $this->identity);
            $this->msg = '保存成功！';
        }

        if ($this->customerType == Conf_User::CRM_IDENTITY_PERSONAL && $this->oldInfo['step'] == Conf_User::CERTIFICATE_PASS)
        {
            $result = Crm2_Certification_Api::personal_auth($this->identity['real_name'], $this->identity['id_number'], $this->identity['mobile'], $this->identity['band_card_number']);

            if ($result['result'])
            {
                $cc = new Crm2_Certification();
                $cc->update($this->oldInfo['id'], $this->identity);
                $this->msg = '保存成功';
            } else {
                $this->msg = $result['reason'];
            }
        }
    }

    protected function outputPage()
    {
        $result = array('msg' => $this->msg);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}
$app = new App('pri');
$app->run();