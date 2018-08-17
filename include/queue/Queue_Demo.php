<?php
/**
 * 任务demo
 * @author wangxuemin
 */
class Queue_Demo extends Base_Func
{
    private $table;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = 't_product';
    }
    
    /**
     * 执行任务
     * @author wangxuemin
     * @param array $job
     * @return array
     */
    public function doJob($job)
    {
        if (empty($job)){
            return array(
                'st' => Queue_Task_Base::QUEUE_ITEM_FAILED,
                'msg' => '[Job Info is Empty]',
            );
        }
        $job['info'] = json_decode($job['info'], true);
        $st = Queue_Task_Base::QUEUE_ITEM_FAILED;
        $msg = '';
        try {
            $header = array('pid', '商品描述', '成本价', '价格');
            $res = $this->one->select($this->table, array('pid', 'detail', 'cost', 'price'), "pid < 10015");
            //将数据写入csv文件保存至 multilog/task_queue_download/下
            $filePath = ROOT_PATH . Queue_Task_Base::DOWNLOAD_PATH . $job['info']['file_name'];
            Data_Csv::write($filePath, $header, $res['data']);
            $st = Queue_Task_Base::QUEUE_ITEM_FINISHED;
            $download = new Statistics_File_Download();
            //下载提取码
            $code = $download->getCode();
            //文件过期时间
            $outTiem = time() + 60 * 60 * 24 * Queue_Task_Base::FILE_OUT_TIME;
            //记录文件下载信息
            $download->insert(array(
                'filePath' => $filePath,
                'out_time' => date('Y-m-d H:i:s', $outTiem),
                'code' => $code,
                'suid' => $job['info']['uid']
            ));
            //通知钉钉，没有钉钉再通知短信
            if (!empty($job['info']['ding_id'])){
                $uids[] = $job['info']['ding_id'];
                $result = Tool_DingTalk::sendTextMessage($job['info']['ding_id'], '您申请商的品数据文件已经生成，请到管理后台进行下载。提取码为' .$code. ',有效期为'. Queue_Task_Base::FILE_OUT_TIME .'天请及时提取！');
            } else {
                $para = array(
                    'status' => '导出商品数据文件已经生成',
                    'code' => $code . '请到管理后台进行下载,有效期为'. Queue_Task_Base::FILE_OUT_TIME .'天请及时提取！'
                );
                Data_Sms::sendNew($job['info']['mobile'], Conf_Sms::STAFF_USER_SMS_KEY, $para);
            }
        } catch (Exception $e) {
            //处理失败
            $msg .= sprintf("\tFailed_Record# id:%d\tnote:%s\n", $job['id'], $e->getMessage());
            $adminInfos = Admin_Api::getStaffs(array(1004,1029,1036,1289,1749));
            $userIds = '';
            foreach ($adminInfos as $item)
            {
                if(!empty($item['ding_id']))
                {
                    $userIds .= $item['ding_id'] . ',';
                }
            }
            $userIds = trim($userIds, ',');
            //通知指定的开发人员处理失败
            if(!empty($userIds)){
                Tool_DingTalk::sendTextMessage($userIds, $msg);
            }
            //记录失败日志
            Queue_Task_Base::addQueueFailedLog($msg);
            //钉钉通知操作人处理失败，没有钉钉通知短信
            if (!empty($job['info']['ding_id'])){
                Tool_DingTalk::sendTextMessage($job['info']['ding_id'], '商品数据文件生成失败，请联系开发人员处理');
            } else {
                $para = array(
                    'status' => '导出商品数据文件生成失败，请联系开发人员处理！',
                    'code' => 'none~'
                );
                Data_Sms::sendNew($job['info']['mobile'], Conf_Sms::STAFF_USER_SMS_KEY, $para);
            }
        }
        if ($st == Queue_Task_Base::QUEUE_ITEM_FINISHED){
            $msg .= '[Job Execute Success]';
        } else {
            $msg .= '[Job Execute Failed]';
        }
        return array(
            'st' => $st,
            'msg' => $msg
        );
    }
}
