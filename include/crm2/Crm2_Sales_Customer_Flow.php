<?php

/**
 * title 销售客户转接
 * @author wangxuemin
 *
 */
class Crm2_Sales_Customer_Flow extends Base_New_Func
{
    protected $_one;
    protected $sales_table;
    protected $customer_table;
    protected $base_new_func;
    
    function __construct()
    {
        $this->sales_table = "t_staff_user";
        $this->customer_table = "t_customer";
        parent::__construct("t_staff_user");
        $this->base_new_func = new Data_Dao($this->customer_table);
    }
    /**
     * title 水平划分
     * @author wangxuemin
     * @param $total int 客户总人数
     * @param $sales_num int 销售人数
     * @return Array
     */
    public function levelCustomer($total, $sales_num) 
    {
        $result = array(
            "type" => 0,					// 状况
            "sales_total" => 0,				// 可转销售总人数
            "sales_customer_num" => 0,		// 每位销售所分得的客户数量
        );
        // 总人数不为0且少于等于销售人数
        if ($total <= $sales_num && $total !== 0) {
            $result["type"] = 1;
            $result["sales_total"] = $total;
            $result["sales_customer_num"] = 1;
        }
        // 总人数大于销售人数
        if ($total > $sales_num) {
            $result["type"] = 2;
            $result["sales_total"] = $sales_num;
            // 取模
            $remainder = $total % $sales_num;
            // 计算每位销售平均分得客户
            $result["sales_customer_num"] = ($total - $remainder) / $sales_num;
        }
        return $result;
    }
    
    /**
     * title 获取销售信息
     * @author wangxuemin
     * @param $suid int 销售suid
     * @return Array 销售信息
     */
    public function getSalesOneBySuid($suid)
    {
        return $this->get($suid);
    }
    
    /**
     * title 获取多销售信息
     * @author wangxuemin
     * @param $suid_array array 销售suid数组
     * @return Array 销售信息二维数组
     */
    public function getSalesBySuid($array)
    {
        $result = array();
        for ($i = 0; $i < count($array); $i++) {
            $result[$i]["suid"] = $array[$i];
            $result[$i]["data"] = $this->get($array[$i]);
        }
        return $result;
    }
    
    /**
     * title 获取销售名下全部客户
     * @author wangxuemin
     * @param $suid int 销售suid
     * @return Array 全部客户数组信息
     */
    public function getCustomersBySuid($suid)
    {
        return $this->base_new_func->setFields(array('cid', 'name'))->getListWhere(array('sales_suid' => $suid));
    }
    
    /**
     * title 客户划分信息
     * @author wangxuemin
     * @param $sales_suid int 转出销售信息suid
     * @param $sales_flow_suid_array array 转入销售suid
     * @return Array 客户流转相信信息
     */
    public function getUpdateCustomer($sales_suid, $sales_flow_suid_array)
    {
        /* 处理后返回数据 */
        $result = array();
        /* 写进日志的数据 */
        $data = "";
        /* 转出销售信息 */
        $sales = $this->getSalesOneBySuid($sales_suid);
        /* 转入销售信息 */
        $sales_flow = $this->getSalesBySuid($sales_flow_suid_array);
        /* 获取需要转移的客户 */
        $customers = array();
        $customers = $this->getCustomersBySuid($sales_suid);
        /* 客户信息 */
        $customers["data"] = $customers;
        /* 客户数量 */
        $customers["total"] = count($customers["data"]);
        /* 获取水平分割 */
        $level_customer = $this->levelCustomer($customers["total"], count($sales_flow_suid_array));
        $tmp = array_chunk($customers["data"], $level_customer["sales_customer_num"]);
        /* 合并末尾分割数据 */
        $count_t = count($tmp);
        /* 转出销售信息 */
        $result["sales_user"]["suid"] = $sales["suid"];
        $result["sales_user"]["name"] = $sales["name"];
        $result["sales_user"]["customer_num"] = $customers["total"];
        /* 流入时间 */
        $chg_sstatus_time = date("Y-m-d H:i:s");
        $result["date"] = $chg_sstatus_time;
        /* 临时数组 */
        $t = array();
        /* 取模后没有分配的客户，按照顺序能分给销售确保每位销售分得客户数量均匀 */
        if ($count_t > $level_customer["sales_total"]) {
            for ($i = 0; $i < $count_t - $level_customer["sales_total"]; $i++) {
                $t = array_merge($t, $tmp[$level_customer["sales_total"] + $i]);
            }
            for ($i = 0; $i < count($t); $i++) {
                array_push($tmp[$i], $t[$i]);
            }
        }
        if ($level_customer["type"] == 0) {
            $result["code"] = 0;
            $result["data"] = array();
        } elseif ($level_customer["type"] > 0) {
            $result["code"] = 1;
            for ($i = 0; $i < $level_customer["sales_total"]; $i++) {
                /* 转入销售信息 */
                $result["sales_user_flow"][$i]["suid"] = $sales_flow[$i]["suid"];
                $result["sales_user_flow"][$i]["name"] = $sales_flow[$i]["data"]["name"];
                $result["sales_user_flow"][$i]["data"] = $tmp[$i];
                $result["sales_user_flow"][$i]["customer_num"] = count($tmp[$i]);
            }
        }
        return $result;
    }
    
    /**
     * title 客户划分及更新customer
     * @author wangxuemin
     * @param $sales_suid int 转出销售信息suid
     * @param $sales_flow_suid_array array 转入销售suid
     * @param $chg_sstatus_time 转出时间
     * @return Array 客户流转相信信息
     */
    public function executeUpdateCustomer($sales_suid, $sales_flow_suid_array, $chg_sstatus_time)
    {
        /* 处理后返回数据 */
        $result = array();
        /* 写进日志的数据 */
        $data = "";
        /* 转出销售信息 */
        $sales = $this->getSalesOneBySuid($sales_suid);
        /* 转入销售信息 */
        $sales_flow = $this->getSalesBySuid($sales_flow_suid_array);
        /* 获取需要转移的客户 */
        $customers = array();
        $customers = $this->getCustomersBySuid($sales_suid);
        /* 客户信息 */
        $customers["data"] = $customers;
        /* 客户数量 */
        $customers["total"] = count($customers["data"]);
        /* 获取水平分割 */
        $level_customer = $this->levelCustomer($customers["total"], count($sales_flow_suid_array));
        $tmp = array_chunk($customers["data"], $level_customer["sales_customer_num"]);
        /* 合并末尾分割数据 */
        $count_t = count($tmp);
        /* 转出销售信息 */
        $result["sales_user"]["suid"] = $sales["suid"];
        $result["sales_user"]["name"] = $sales["name"];
        $result["sales_user"]["customer_num"] = $customers["total"];
        $result["date"] = $chg_sstatus_time;
        $result["flow_num"] = 0;
        /* 临时数组 */
        $t = array();
        /* 取模后没有分配的客户，按照顺序能分给销售确保每位销售分得客户数量均匀 */
        if ($count_t > $level_customer["sales_total"]) {
            for ($i = 0; $i < $count_t - $level_customer["sales_total"]; $i++) {
                $t = array_merge($t, $tmp[$level_customer["sales_total"] + $i]);
            }
            for ($i = 0; $i < count($t); $i++) {
                array_push($tmp[$i], $t[$i]);
            }
        }
        if ($level_customer["type"] == 0) {
            $result["code"] = 0;
            $result["data"] = array();
        } elseif ($level_customer["type"] > 0) {
            $result["code"] = 1;
            for ($i = 0; $i < $level_customer["sales_total"]; $i++) {
                /* 转入销售信息 */
                $result["sales_user_flow"][$i]["suid"] = $sales_flow[$i]["suid"];
                $result["sales_user_flow"][$i]["name"] = $sales_flow[$i]["data"]["name"];
                $result["sales_user_flow"][$i]["data"] = $tmp[$i];
                $result["sales_user_flow"][$i]["customer_num"] = count($tmp[$i]);
                /* 记录更新数据 */
                foreach ($tmp[$i] as $key => $val) {
                    /* 拼接需要修改销售的客户cid串 */
                    $tmp[$i]["cids"] .= $val["cid"] . ",";
                    $data .= $sales["suid"] . "---" . $sales["name"] . "---" . $val["cid"] . "---" . $val["name"] . "---" . $sales_flow[$i]["suid"] ."---" . $sales_flow[$i]["data"]["name"] . "---" . $chg_sstatus_time . "\n";
                } 
            }
            /* 执行更新 */
            foreach ($tmp as $key => $val) {
                $tmp[$key]["cids"] = trim($tmp[$key]["cids"], ",");
                $tmp[$key]["cids"] = explode(",", $tmp[$key]["cids"]);
                $resNum = $this->base_new_func->updateWhere(array("cid" => $tmp[$key]["cids"]), array("sales_suid" => $sales_flow[$key]["suid"], "chg_sstatus_time" => $chg_sstatus_time));
                /* 统计最终转出成功客户人数 */
                if ($resNum > 0) {
                    $result["flow_num"] = $result["flow_num"] + $resNum;
                }
            }
        }
        /* 写进日志 */
        $this->writeLog($data);
        return $result;
    }
    
    /**
     * title 将销售客户流转信息记录log日志，日志文件记录当天。日志目录 --- multilog/sales_customer_log/
     * @author wangxuemin
     * @param $data String 写入日志的数据
     */
    public function writeLog($data)
    {
        $file_name = "sales_customer_" . date('Y-m-d', time()) . ".log";
        file_put_contents(ROOT_PATH . "multilog/sales_customer_log/" .$file_name, $data, FILE_APPEND);
    }
    
}
