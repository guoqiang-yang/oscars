<?php
/**
 * 输出csv文件
 */
class Data_Csv
{
    private static $_CNT = 0;
    private static $LIMIT = 1000;

    public static function send($arr)
    {
        if (BASE_HOST != '.haocaisong.cn')
        {
            //exit;
        }

        $fp = fopen('php://output', 'a');

        foreach ($arr as $i => $v)
        {
            $arr[$i] = iconv('utf-8', 'gbk', $v);
        }

        // 将数据通过fputcsv写到文件句柄
        $delimiter = ',';
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos($agent, 'macintosh'))
        {
            $delimiter = ';';
        }

        fputcsv($fp, $arr, $delimiter);

        self::$_CNT++;

        //刷新一下输出buffer，防止由于数据过多造成问题
        if (self::$_CNT >= self::$LIMIT)
        {
            ob_flush();
            flush();
            self::$_CNT = 0;
        }
    }
    
    /**
     * put方式生成csv，多I/O
     * @author wangxuemin
     * @param string $file 文件
     * @param array $arr 内容，一维数组
     * @return void
     */
    public static function put($file, $arr, $delimiter='')
    {
        //打开文件资源，不存在则创建
        $fp = fopen($file, 'a');
        
        //处理编码
        $arr = self::iconv($arr);
        
        //将数据通过fputcsv写到文件句柄
        if (empty($delimiter))
        {
            $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            $delimiter = strpos($agent, 'macintosh')? ';': ',';
        }
        
        //追加资源
        fputcsv($fp, $arr, $delimiter);
        //关闭资源
        fclose($fp);
        self::$_CNT++;
        //刷新一下输出buffer，防止由于数据过多造成问题
        if (self::$_CNT >= self::$LIMIT){
            ob_flush();
            flush();
            self::$_CNT = 0;
        }
    }
    
    /**
     * write方式生成csv，少I/O
     * @param wangxuemin
     * @param string $file 文件
     * @param array $csv_header 标题
     * @param array $arr 内容，二维数组
     * @return void
     */
    public static function write($file, $csv_header = array(), $arr = array())
    {
        //打开文件资源，不存在则创建
        $fp = fopen($file, 'a');
        //处理头部标题
        $csv_header = self::iconv($csv_header);
        $header = implode(',', $csv_header) . PHP_EOL;
        //处理内容
        $content = '';
        foreach ($arr as $key => $val) {
            $arr[$key] = self::iconv($val);
        }
        foreach ($arr as $k => $v) {
            $content .= implode(',', $v) . PHP_EOL;
        }
        //拼接
        $csv = $header.$content;
        //写入资源
        fwrite($fp, $csv);
        //关闭资源
        fclose($fp);
        //刷新一下输出buffer，防止由于数据过多造成问题
        ob_flush();
        flush();
    }
    
    /**
     * 字符转码
     * @author wangxuemin
     * @param array $arr
     * @return array
     */
    public static function iconv($arr = array())
    {
        foreach ($arr as $i => $v)
        {
            $arr[$i] = iconv('utf-8', 'gbk', $v);
        }
        return $arr;
    }
    
    /**
     * 下载csv文件
     * @author wangxuemin
     * @param string $fileName
     * @return void
     */
    public static function download($filePath)
    {
        $fileName = basename($filePath, '.csv');
        header('Content-Type: application/download');
        header("Content-type:text/csv;");
        header('Content-Disposition:attachment;filename=' . $fileName . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        readfile($filePath);
        exit;
    }
    
    /**
     * 读取csv文件
     * @author wangxuemin
     * @param string $filePath 文件路径
     * @param int $length 长度限制默认为0
     * @param string $separator 分隔符，默认为英文逗号
     * @return array
     */
    public static function readCsv($filePath, $length = 0, $separator = ',')
    {
        $file = fopen($filePath, 'r');
        while ($data = fgetcsv($file, $length, $separator)) {
            $list[] = $data;
        }
        fclose($file);
        return $list;
    }
    
}