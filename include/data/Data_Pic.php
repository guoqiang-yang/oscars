<?php

/**
 * 图片
 * 文件名生成规则：$picid.type
 */
class Data_Pic
{
    /**
     * $force   1 满足宽度即可
     *          2 满足高度即可
     *          3 同时满足高度和宽度
     */
    private static $picConf = array(
        '' => array('width' => 2280, 'height' => 2280, 'force' => 1), 'small' => array('width' => 120, 'height' => 120, 'force' => 3), 'normal' => array('width' => 200, 'height' => 200, 'force' => 3), 'middle' => array('width' => 480, 'height' => 480, 'force' => 3), 'detail' => array('width' => 420, 'height' => 280, 'force' => 3), 'big' => array('width' => 750, 'height' => 750, 'force' => 1),
    );

    public static function getPicConf()
    {
        return self::$picConf;
    }

    /**
     * @picid 图片id
     */
    public static function savePic($picid, $name, $content, $basePath = OSS_PIC_PATH)
    {
        $picid = intval($picid);
        $name = trim(strval($name));
        assert($picid > 0);
        assert(!empty($name));
        assert(!empty($content));

        //path
        $path = self::getPathById($picid);
        $desPath = PIC_FILE_PATH . $path;
        $desPath = str_replace('/', DS, $desPath);
        self::_checkMakeDir($desPath);

        //file name
        $pathParts = pathinfo($name);
        $ext = $pathParts['extension']; //todo: if ext is null

        $filename = $picid . '.' . $ext;

        //save to disk
        $desFile = $desPath . $filename;
        $ret = file_put_contents($desFile, $content);
        if (!$ret)
        {
            Tool_Log::addFileLog('data_pic', 'file_put_contents error ' . $desFile);

            return FALSE;
        }

        $name = $basePath . $path . $filename;
        if (BASE_HOST != '.haocaisong.cn')
        {
            $name = 'test_pic/' . $path . $filename;
        }

        Oss_Api::uploadFileByPath($name, $desFile);

        return $name;
    }

    /**
     * @picid 图片id
     */
    public static function saveSkuPic($picid, $name, $content, $basePath = OSS_PIC_PATH)
    {
        $picid = intval($picid);
        $name = trim(strval($name));
        assert($picid > 0);
        assert(!empty($name));
        assert(!empty($content));

        //path
        $path = self::getPathById($picid);
        $desPath = PIC_FILE_PATH . $path;
        $desPath = str_replace('/', DS, $desPath);
        self::_checkMakeDir($desPath);

        //file name
        $pathParts = pathinfo($name);
        $ext = $pathParts['extension']; //todo: if ext is null

        $filename = $picid . '.' . $ext;

        //save to disk
        $desFile = $desPath . $filename;
        $ret = file_put_contents($desFile, $content);
        if (!$ret)
        {
            Tool_Log::addFileLog('data_pic', 'file_put_contents error ' . $desFile);

            return FALSE;
        }

        $name = $basePath . $path . $filename;
        if (BASE_HOST != '.haocaisong.cn')
        {
            $name = 'test_pic/' . $path . $filename;
        }

        Oss_Api::uploadFileByPath($name, $desFile);

        return $filename;
    }

    /**
     * 获取文件内容
     *
     * @param $url
     * @param $picid
     *
     * @return bool|string
     */
    public static function getPic($url, &$picid)
    {
        // 从url中提取参数
        $filename = basename($url);
        list($token, $type) = explode('.', $filename);
        list($picid, $size) = explode('_', $token);
        $size = strval($size);

        // 从本地文件中读数据
        $path = self::getPathById($picid);
        $localFile = PIC_FILE_PATH . $path . $filename;
        $localFile = str_replace('/', DS, $localFile);
        if (file_exists($localFile))
        {
            $content = file_get_contents($localFile);
            if (FALSE != $content)
            {
                return $content;
            }
        }

        // 读取不缩放的源文件
        $filename = $picid . '.' . $type;
        $desPath = PIC_FILE_PATH . $path;
        $desPath = str_replace('/', DS, $desPath);
        self::_checkMakeDir($desPath);
        $srcFile = $desPath . $filename;
        $srcFile = str_replace('/', DS, $srcFile);
        if (!file_exists($srcFile))
        {
            return FALSE;
        }

        // 缩略
        $info = Tool_Image::getImageInfo($srcFile);
        if (!empty($info))
        {
            $picConf = self::$picConf;
            $ret = Tool_Image::convertImage($srcFile, $info, $localFile, $picConf[$size]['width'], $picConf[$size]['height'], $picConf[$size]['force'], FALSE, TRUE, 0, TRUE);
            if ($ret)
            {
                $content = file_get_contents($localFile);

                return $content;
            }
        }

        return FALSE;
    }

    /**
     * 获取文件本地地址
     *
     * @param url 文件名或文件url
     */
    public static function getPicPath($url, &$picid)
    {
        // 从url中提取参数
        $filename = basename($url);
        list($token, $type) = explode('.', $filename);
        list($picid, $size) = explode('_', $token);
        $size = strval($size);

        // 从本地文件中读数据
        $path = self::getPathById($picid);
        $localFile = PIC_FILE_PATH . $path . $filename;
        $localFile = str_replace('/', DS, $localFile);
        if (file_exists($localFile))
        {
            return $localFile;
        }

        // 读取不缩放的源文件
        $filename = $picid . '.' . $type;
        $desPath = PIC_FILE_PATH . $path;
        $desPath = str_replace('/', DS, $desPath);
        self::_checkMakeDir($desPath);
        $srcFile = $desPath . $filename;
        $srcFile = str_replace('/', DS, $srcFile);
        if (!file_exists($srcFile))
        {
            return FALSE;
        }

        // 缩略
        $info = Tool_Image::getImageInfo($srcFile);
        if (!empty($info))
        {
            $picConf = self::$picConf;
            $ret = Tool_Image::convertImage($srcFile, $info, $localFile, $picConf[$size]['width'], $picConf[$size]['height'], $picConf[$size]['force'], FALSE, TRUE, 0, TRUE);
            if ($ret)
            {
                return $localFile;
            }
        }

        return FALSE;
    }

    public static function getPicFilename($url)
    {
        // 从url中提取参数
        $filename = basename($url);
        list($token, $type) = explode('.', $filename);
        list($picid, $size) = explode('_', $token);
        if ($picid && $type)
        {
            return $picid . '.' . $type;
        }

        return FALSE;
    }

    /**
     * 输入: 图片filename(picid . type), 图片规格('20x20')
     * 输出: 图片的url
     */
    public static function getPicUrl($filename, $size = '')
    {
        list($picid, $type) = explode('.', $filename);

        if (empty($size))
        {
            return 'http://' . PIC_HOST . '/pic/' . $picid . '.' . $type;
        }
        else
        {
            return 'http://' . PIC_HOST . '/pic/' . $picid . '_' . $size . '.' . $type;
        }
    }

    public static function isValidPictag($pictag)
    {
        if (preg_match('@^\d+.\w+$@', $pictag) || preg_match('@' . PIC_HOST . '\/pic\/@', $pictag))
        {
            return TRUE;
        }

        return FALSE;
    }

    public static function isValidUrl($pictag)
    {
        if (preg_match('@^\d+.\w+$@', $pictag) || preg_match('@' . PIC_HOST . '\/pic\/@', $pictag))
        {
            return TRUE;
        }

        return FALSE;
    }

    public static function getPicTag($url)
    {
        // 从url中提取参数
        $filename = basename($url);
        list($token, $type) = explode('.', $filename);
        list($picid, $size) = explode('_', $token);

        return $picid . '.' . $type;
    }

    private static function getPathById($id)
    {
        $path = (floor($id / 10000) % 100) . DS;
        $path .= (floor($id / 100) % 100) . DS;

        return $path;
    }

    private static function _checkMakeDir($desPath)
    {
        if (!file_exists($desPath))
        {
            mkdir($desPath, 0777, TRUE);
            while (strlen($desPath) > 20)
            {
                chmod($desPath, 0777);
                $desPath = dirname($desPath);
            }
        }
    }

    /**
     * 输入: 图片filename(picid . type), 图片规格('20x20')
     * 输出: 图片的url
     */
    public static function getPicUrlFromOss($filename, $size = '')
    {
        list($picid, $type) = explode('.', $filename);
        $path = self::getPathById($picid);

        $ossPath = OSS_PIC_PATH;
        if ($picid >= 17078 && BASE_HOST != '.haocaisong.cn' && BASE_HOST != '.v2.haocaisong.cn')
        {
            $ossPath = 'pic/';
        }

        if (empty($size))
        {
            return OSS_HOST . OSS_PIC_PATH . $path . $filename;
        }
        else
        {
            $width = self::$picConf[$size]['width'];
            $height = self::$picConf[$size]['height'];

            return OSS_HOST . $ossPath . $path . $filename . "?x-oss-process=image/resize,m_pad,h_{$height},w_{$width}";
        }
    }

    public static function getUrlFromOss($filename, $size = '')
    {
        if (empty($size))
        {
            return OSS_HOST . $filename;
        }
        else
        {
            $width = self::$picConf[$size]['width'];
            $height = self::$picConf[$size]['height'];

            return OSS_HOST . $filename . "?x-oss-process=image/resize,m_pad,h_{$height},w_{$width}";
        }
    }
}