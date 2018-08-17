<?php
/**
 * 任务队列：时效性不敏感的队列.
 * 
 * @author wangxuemin
 */
class Data_Task_Queue  extends Base_Func
{
    private $table = 't_queue_insensitive';
    
    /**
     * 任务状态.
     */
    const QUEUE_ITEM_PENDING = 0,   //待执行
          QUEUE_ITEM_FINISHED = 1,  //执行成功
          QUEUE_ITEM_FAILED = 2;    //执行失败
    
    const FILE_TIMEOUT_DAYS = 7;    //有效期
    
    public function enqueue($type, $infos, $suid, $isDownload=false)
    {
        $data = array(
            'type' => $type,
            'info' =>is_array($infos)? json_encode($infos): $infos,
            'suid' => $suid,
            'ctime' => date('Y-m-d H:i:s'),
            'status' => self::QUEUE_ITEM_PENDING,
            'code' => '',
        );
        
        if ($isDownload)
        {
            $fileInfo = $this->genDownLoadFile($type, $infos);
            $data['file_path'] = Queue_Task_Api::getFullFileName($fileInfo['file_name']);
            $data['code'] = $fileInfo['fetch_code'];
            $data['out_time'] = date('Y-m-d H:i:s', strtotime('+'. self::FILE_TIMEOUT_DAYS. ' days'));
            //$data['status'] = self::QUEUE_ITEM_FINISHED;
        }
        
        $ret = $this->one->insert($this->table, $data);
        
        return $ret['insertid'];
    }
    
    public function update4Download($id, $taskInfo)
    {
        $fileInfo = $this->genDownLoadFile($taskInfo['type'], $taskInfo['info']);
        
        $upData = array(
            'file_path' => Queue_Task_Api::getFullFileName($fileInfo['file_name']),
            'code' => $fileInfo['fetch_code'],
            'out_time' => date('Y-m-d H:i:s', strtotime('+'. self::FILE_TIMEOUT_DAYS. ' days')),
            'status' => self::QUEUE_ITEM_FINISHED,
        );
        
        $ret = $this->one->update($this->table, $upData, array(), 'id='. $id);
        
        return $ret['affectedrows'];
    }

    public function getDownloadFile($suid, $fetchCode)
    {
        $where = sprintf('suid=%d and code="%s"', $suid, $fetchCode);
        $order = 'order by id desc';
        
        $ret = $this->one->select($this->table, array('status', 'out_time', 'file_path'), $where, $order, 0, 1);
        
        return $ret['data'][0];
    }

    public function dequeue($count=10)
    {
        $where = array(
            'status' => self::QUEUE_ITEM_PENDING,
            //'code' => '',
        );
        
        $res = $this->one->select($this->table, array('*'), $where, 'order by id asc', 0, $count);
        
        if($res['rownum'] == 0)
        {
            return array();
        }

        $tasks = $res['data'];
        foreach($tasks as &$item)
        {
            $item['info'] = json_decode($item['info'], true);
        }
        
        return $tasks;
    }
    
    public function markItem($id, $status)
    {
        if(empty($id))
        {
            return 0;
        }
        
        $upData = array(
            'status' => $status,
        );
        
        return $this->one->update($this->table, $upData, array(), array('id'=>intval($id)));
    }
    
    
    public static function genDownLoadFile($type, $infos)
    {
        $_fileParams = is_array($infos)? http_build_query($infos): $infos;
        $_fileNameParam = md5($_fileParams);
        
        return array(
            'file_name' => $type. '.'. substr($_fileNameParam, 0, 10). '.csv',
            'fetch_code' => strtoupper(substr($_fileNameParam, -4)),
        );
    }
}