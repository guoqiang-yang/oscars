<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/1/8
 * Time: 17:22
 */


if (is_file(__DIR__ . '/autoload.php'))
{
    require_once __DIR__ . '/autoload.php';
}
if (is_file(__DIR__ . '/vendor/autoload.php'))
{
    require_once __DIR__ . '/vendor/autoload.php';
}

use OSS\OssClient;
use OSS\Core\OssException;

class Oss_Api extends Base_Api
{
    const OSS_ACCESS_ID = '5Yug91HMB04q6Ljc';
    const OSS_ACCESS_KEY = 'B16gx9wkdyzbgMDknmCycx5WMTAAQs';
    const OSS_ENDPOINT = 'oss-cn-hangzhou.aliyuncs.com';
    const OSS_BUCKET = 'haocaisong';

    /**
     * 根据Config配置，得到一个OssClient实例
     *
     * @return OssClient 一个OssClient实例
     */
    public static function getOssClient()
    {
        try
        {
            $ossClient = new OssClient(self::OSS_ACCESS_ID, self::OSS_ACCESS_KEY, self::OSS_ENDPOINT, false);
        }
        catch (OssException $e)
        {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");

            return null;
        }

        return $ossClient;
    }

    /**
     * @param $fileName
     * @param $filePath
     * @return string
     *
     * 根据路径上传文件
     */
    public static function uploadFileByPath($fileName, $filePath)
    {
        $options = array();

        try
        {
            self::getOssClient()->uploadFile(self::OSS_BUCKET, $fileName, $filePath, $options);
        }
        catch (OssException $e)
        {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return '';
        }

        return $fileName;
    }

    /**
     * 把本地变量的内容到文件
     *
     * 简单上传,上传指定变量的内存值作为object的内容
     *
     * @param OssClient $ossClient OssClient实例
     * @param string $bucket 存储空间名称
     * @return null
     */
    public static function saveFileByContent($fileName, $fileContent)
    {
        $options = array();

        try
        {
            self::getOssClient()->putObject(self::OSS_BUCKET, $fileName, $fileContent, $options);
        }
        catch (OssException $e)
        {
//            printf(__FUNCTION__ . ": FAILED\n");
//            printf($e->getMessage() . "\n");
            return '';
        }

        return $fileName;
    }

    /**
     * @param $fileName
     * @return bool
     *
     * 删除指定的object
     */
    public static function deleteObject($fileName)
    {
        try
        {
            self::getOssClient()->deleteObject(self::OSS_BUCKET, $fileName);
        }
        catch (OssException $e)
        {
//            printf(__FUNCTION__ . ": FAILED\n");
//            printf($e->getMessage() . "\n");
            return false;
        }

        return true;
    }

    /**
     * @param $fileName
     * @return bool
     *
     * 判断ojbect是否存在
     */
    public static function doesObjectExist($fileName)
    {
        try
        {
            $exist = self::getOssClient()->doesObjectExist(self::OSS_BUCKET, $fileName);
        }
        catch (OssException $e)
        {
//            printf(__FUNCTION__ . ": FAILED\n");
//            printf($e->getMessage() . "\n");
            return false;
        }

        return $exist;
    }

    //获取文件URL
    public static function getUrl($object)
    {
        $url = OSS_HOST . $object;

        return $url;
    }

    //获取图片URL，可指定图片的宽，高，质量百分比，类型等
    public static function getImageUrl($object, $width = 0, $height = 0, $e = 1, $c = 0)
    {
        if (strpos($object, OSS_HOST) === false) {
            $url = OSS_HOST . $object;
        } else {
            $url = $object;
        }

        if (!empty($width) && !empty($height))
        {
            $addOn = sprintf('?x-oss-process=image/resize,m_fill,h_%d,w_%d,limit_0', $height, $width);
            $url .= $addOn;
        }


        return $url;
    }
}