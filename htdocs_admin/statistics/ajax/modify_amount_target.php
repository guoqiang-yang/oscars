<?php
include_once('../../../global.php');
/**
 * Created by PhpStorm.
 * User: jslsxu
 * Date: 2017/7/25
 * Time: 下午2:29
 */

class App extends App_Admin_Ajax{
    private $suid;
    private $floor_target;
    private $challenge_target;
    private $month_target;
    protected function getPara()
    {
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_INT);
        $this->floor_target = Tool_Input::clean('r', 'floor_target', TYPE_INT);
        $this->challenge_target = Tool_Input::clean('r', 'challenge_target', TYPE_INT);
        $this->month_target = Tool_Input::clean('r', 'month_target', TYPE_INT);
    }

    protected function checkPara()
    {

    }

    protected function main()
    {
        $salesStatistics = new Statistics_Sales();
        if($this->month_target > 0){
            $salesStatistics->updateIndividualSalesTarget($this->suid, $this->month_target);
        }
        else if($this->floor_target > 0 || $this->challenge_target > 0){
            $salesStatistics->updateGroupSalesTarget($this->suid, $this->floor_target, $this->challenge_target);
        }
    }

    protected function outputPage()
    {
        $response = new Response_Ajax();
        $response->setError(array('errno' => 1, 'errmsg' => '成功'));
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();