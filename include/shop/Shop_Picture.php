<?php

/**
 * 品牌相关业务
 */
class Shop_Picture extends Base_Func
{
    private $pictureDao;

    public function __construct()
    {
        $this->pictureDao = new Data_Dao('t_picture');
        parent::__construct();
    }

    /**
     * 保存图片
     *
     * @param       $name
     * @param       $content
     * @param       $width
     * @param       $height
     * @param array $srcInfo
     * @param       $path
     *
     * @return int
     */
    public function savePic($name, $content, $width, $height, array $srcInfo = array(), $path = '')
    {
        assert(!empty($name));
        assert(!empty($content));

        $info = array(
            'width' => $width,
            'height' => $height,
            'srcinfo' => json_encode($srcInfo),
        );

        $pid = $this->pictureDao->add($info);
        $picTag = Data_Pic::savePic($pid, $name, $content, $path);
        $this->pictureDao->update($pid, array('pictag' => $picTag));

        return $picTag;
    }

    /**
     * 保存sku图片
     *
     * @param       $name
     * @param       $content
     * @param       $width
     * @param       $height
     * @param array $srcInfo
     * @param       $path
     *
     * @return int
     */
    public function saveSkuPic($name, $content, $width, $height, array $srcInfo = array(), $path = OSS_PIC_PATH)
    {
        assert(!empty($name));
        assert(!empty($content));

        $info = array(
            'width' => $width, 'height' => $height, 'srcinfo' => json_encode($srcInfo),
        );

        $pid = $this->pictureDao->add($info);
        $picTag = Data_Pic::saveSkuPic($pid, $name, $content, $path);
        $this->pictureDao->update($pid, array('pictag' => $picTag));

        return $picTag;
    }

    /**
     * 获取图片信息
     *
     * @param $pid
     *
     * @return array
     */
    public function getPicInfo($pid)
    {
        return $this->pictureDao->get($pid);
    }

    public function cropImage($filename, $midImgWidth, $midLeft, $midTop, $midWidth, $midHeight)
    {
        list($picid, $ext) = explode('.', $filename);

        // 获取文件信息
        $srcFile = Data_Pic::getPicPath($filename, $picid);
        $desFile = $this->_getTmpFilename($picid, $ext);
        $imgInfo = Tool_Image::getImageInfo($srcFile);
        if (empty($imgInfo))
        {
            throw new Exception('common:system error');
        }

        // 计算实际尺寸
        $realImgWidth = $imgInfo['width'];
        $ratio = $realImgWidth / $midImgWidth;
        $cropLeft = intval($midLeft * $ratio);
        $cropTop = intval($midTop * $ratio);
        $cropWidth = intval($midWidth * $ratio);
        $cropHeight = intval($midHeight * $ratio);
        // 裁剪图片
        $ret = Tool_Image::cropImage($srcFile, $imgInfo, $desFile, 1, $cropLeft, $cropTop, $cropWidth, $cropHeight);
        if (!$ret)
        {
            throw new Exception('common:system error');
        }

        // 保存
        $content = file_get_contents($desFile);
        $srcInfo = array(
            'pic' => $filename, 'x' => $midLeft, 'y' => $midTop, 'w' => $midWidth, 'h' => $midHeight,
        );
        $tag = $this->saveSkuPic($filename, $content, $cropWidth, $cropHeight, $srcInfo);
        if (empty($tag))
        {
            throw new Exception('common:upload pic error');
        }

        return $tag;
    }

    private function _getTmpFilename($picid, $ext)
    {
        $file = sprintf('%s%d_%d.%s', TMP_PATH, $picid, mt_rand(1000, 9999), $ext);

        return $file;
    }
}
