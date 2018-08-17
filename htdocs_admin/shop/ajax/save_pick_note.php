<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/4/12
 * Time: 下午3:19
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $pickNote;
    private $msg;

    protected function getPara()
    {
        $this->pickNote = Tool_Input::clean('r', 'pick_note',TYPE_STR);
        $this->pid = Tool_Input::clean('r', 'pid',TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->pid))
        {
            throw new Exception('缺少参数！');
        }
    }

    protected function main()
    {
        $sp = new Shop_Product();
        $res = $sp->update($this->pid, array('picking_note' => $this->pickNote));
        if ($res)
        {
            $changed = "商品pid:{$this->pid} 的包装含量修改为：" . $this->pickNote;
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->pid,
                'obj_type' => Conf_Admin_Log::OBJTYPE_PRODUCT,
                'action_type' => 2,
                'params' => json_encode(array('pid' => $this->pid, 'changed' => $changed)),
            );
            Admin_Common_Api::addAminLog($info);
            $this->msg = '修改成功！';
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

