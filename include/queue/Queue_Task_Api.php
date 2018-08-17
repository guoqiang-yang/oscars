<?php

/**
 * 任务队列（时效性不敏感）API类.
 * 
 * 
 */

class Queue_Task_Api
{
    /**
     * 目录文件.
     * 
     * @todo 应该移除项目
     */
    const DOWNLOAD_PATH = 'multilog/queue_task/';  //下载目录
    
    /**
     * 任务集合.
     * 
     * @demo
     *      'task_name' => {'ttype'=>'任务类型', 'name'=>'task_desc', 'deal_num'=>'每次处理记录数'}    //指定任务类型；默认类型：download
     */
    private static $TaskSets = array(
        'sku_sales_detail' => array('ttype'=>'download', 'desc'=>'sku销售明细', 'deal_num'=>2000),
    );
    
    
    private static $staffInfo = null;
    
    /**
     * 创建任务.
     * 
     * @param int $type     任务类型
     * @param int $infos    执行任务的内容：eg：download-查询数据参数
     * @param int $suid     创建者ID
     */
    public static function enqueue($type, $infos, $suid)
    {
        self::_check4Enqueue($type, $infos, $suid);
        
        $dtq = new Data_Task_Queue();
        $infos['is_mac'] = strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'macintosh')? 1: 0;
                
        $fileInfo = $dtq->genDownLoadFile($type, $infos);
        
        $_isDownload = self::_downloadFileIsExist($fileInfo['file_name'])? true: false;
        
        $enqueueRet = $dtq->enqueue($type, $infos, $suid, $_isDownload);
        
        if ($enqueueRet && $_isDownload)
        {
            self::_notify($type, $suid, $fileInfo);
        }
    }
    
    
    public static function dequeue4Dojob($taskInfo)
    {
        $response = array(
           'st' => Data_Task_Queue::QUEUE_ITEM_FAILED,
           'msg' => '[Job Failed] Task Info Empty',
        );
        
        $dtq = new Data_Task_Queue();
        $fileInfo = $dtq->genDownLoadFile($taskInfo['type'], $taskInfo['info']);
        
        if (! self::_downloadFileIsExist($fileInfo['file_name']))
        {
            list($className, $funcName) = self::_genClassNameAndFuncName($taskInfo['type']);

            $start = 0;
            $dealNum = self::$TaskSets[$taskInfo['type']]['deal_num'];
            $isWriteHeader = false;
            $csvDelimiter = $taskInfo['info']['is_mac']? ';': ',';
            while (true)
            {
                $taskDatas = $className::$funcName($taskInfo['info'], $start, $dealNum);

                if (empty($taskDatas['data']))  break;

                if (!isset($taskDatas['data']) || !isset($taskDatas['head']))
                {
                    $response['msg'] = '[Job Failed] Sub Task Response Error';
                    return $response;
                }

                if (!$isWriteHeader)
                {
                    Data_Csv::put(self::getFullFileName($fileInfo['file_name']), $taskDatas['head'], $csvDelimiter);
                    $isWriteHeader = true;
                }

                foreach($taskDatas['data'] as $_item)
                {
                    Data_Csv::put(self::getFullFileName($fileInfo['file_name']), $_item, $csvDelimiter);
                }

                $start += $dealNum;
            }
        }
        
        //更新下载信息
        $affectRow = $dtq->update4Download($taskInfo['id'],  $taskInfo);
        
        //消息推送
        if ($affectRow)
        {
            self::_notify($taskInfo['type'], $taskInfo['suid'], $fileInfo);
            
            $response['st'] = Data_Task_Queue::QUEUE_ITEM_FINISHED;
            $response['msg'] = '[Job Succ]';
        }
        else
        {
            $response['st'] = Data_Task_Queue::QUEUE_ITEM_PENDING;
            $response['msg'] = '[Job Task Deal Done But Status Error]';
        }
        
        return $response;
    }
    
    public static function getDownloadFile($suid, $fetchCode)
    {
        if (empty($suid) || empty($fetchCode))
        {
            throw new Exception('下载文件失败：参数异常');
        }
        
        $dtq = new Data_Task_Queue();
        $taskInfo = $dtq->getDownloadFile($suid, $fetchCode);
        
        if ($taskInfo['status'] != Data_Task_Queue::QUEUE_ITEM_FINISHED)
        {
            throw new Exception('下载文件失败：任务状态异常');
        }
        
        if (strtotime($taskInfo['out_time'])-time() < 0)
        {
            //throw new Exception('文件已过期，请重新下载');
        }
        
        if (!is_file($taskInfo['file_path']))
        {
            throw new Exception('文件不存在，请重新下载');
        }
        
        return $taskInfo['file_path'];
    }
    
    private static function _notify($type, $suid, $fileInfo)
    {
        $fetchCode = $fileInfo['fetch_code'];
        
        $taskDesc = self::$TaskSets[$type]['desc'];
        
        $hStaff = new Data_Dao('t_staff_user');
        $staffWhere = sprintf('status=0 and suid=%d', $suid);
        $_staffInfo = $hStaff->setFields(array('suid', 'mobile', 'ding_id'))
                                  ->getListWhere($staffWhere);
        $staffInfo = current($_staffInfo);
        
        if (!empty($staffInfo['ding_id']))
        {
            $content = sprintf('您申请的【%s】已生成，提取码：%s，有效期：%d天 [%s]', $taskDesc, $fetchCode, Data_Task_Queue::FILE_TIMEOUT_DAYS,date('Ymd'));
            
            Tool_DingTalk::sendTextMessage($staffInfo['ding_id'], $content);
        }
        else if (!empty($staffInfo['mobile']))
        {
            $para = array(
                'status' => "【$taskDesc】已生成",
                'code' => "$fetchCode， 有效期：". Data_Task_Queue::FILE_TIMEOUT_DAYS. '天',
            );
            Data_Sms::sendNew($staffInfo['mobile'], Conf_Sms::STAFF_USER_SMS_KEY, $para);
        }
        else
        {
            throw new Exception('获取信息失败：人员信息为空');
        }
    }
    
    private static function _check4Enqueue($type, $infos, $suid)
    {
        if (empty($type) || empty($infos) || empty($suid))
        {
            throw new Exception('创建任务失败：参数异常');
        }
        
        if (!array_key_exists($type, self::$TaskSets))
        {
            throw new Exception('创建任务失败：任务类型未定义');
        }
        
        list($className , $funcName) = self::_genClassNameAndFuncName($type);
        if (!class_exists($className))
        {
            throw new Exception('创建任务失败：类未定义：'. $className);
        }
        $hClass = new $className;
        if (!method_exists($hClass, $funcName))
        {
            throw new Exception('创建任务失败：类方法未定义：'. $funcName);
        }
        
        $hStaff = new Data_Dao('t_staff_user');
        $staffWhere = sprintf('status=0 and suid=%d', $suid);
        $staffInfo = $hStaff->setFields(array('suid', 'mobile', 'ding_id'))
                                  ->getListWhere($staffWhere);
        if (empty($staffInfo))
        {
            throw new Exception('创建任务失败：请求者不存在');
        }
       
    }
    
    private static function _downloadFileIsExist($fileName)
    {
        return is_file(self::getFullFileName($fileName))? true: false;
    }
    
    public static function getFullFileName($fileName)
    {
        return LOG_PATH. self::DOWNLOAD_PATH. $fileName;
    }


    private static function _genClassNameAndFuncName($taskName)
    {
        $taskType = !empty(self::$TaskSets[$taskName])? self::$TaskSets[$taskName]['ttype']: 'download';
        $className = 'Queue_Task_'. ucfirst($taskType);
        
        $partNames = str_replace('_', ' ', strtolower($taskName));
        $funcName = lcfirst(str_replace(' ', '', ucwords($partNames)));
        
        return array($className, $funcName);
    }
    
}