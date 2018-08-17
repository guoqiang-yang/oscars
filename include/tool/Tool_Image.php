<?php
/**
 * 图片处理类
 * 说明：从平台拿过来,还未详细整理。
 */

class Tool_Image
{
    static $goodpictype = array(
        "jpeg" => true,
        "jpg" => true,
        "gif"  => true,
        "png" => true,
        "bmp" => true,
        "pjpeg" => true,
    );

    static $pictype2extension = array(
        "jpeg" => "jpg",
    );

    static $nopicallowtype = array(
        "swf" => true,
    );

    public static function getImageInfo($filepath)
    {
        if(!is_file($filepath))
        {
            return false;
        }
        $arr = pathinfo($filepath);
        if(self::$nopicallowtype[strtolower($arr["extension"])])
        {
            $info['type'] = strtolower($arr["extension"]);
            $info['width'] = 65;
            $info['height'] = 65;
            return $info;
        }
        $image = new Imagick($filepath);
        if(strtolower($image->getImageFormat()) == "gif")
        {
            $nframes = 0;
            //取第一帧
            foreach($image as $frame)
            {
                if ($nframes == 0)
                {
                    $info['width']  = $frame->getImageWidth();
                    $info['height'] = $frame->getImageHeight();
                }
                $nframes++;
            }
            $info['nframes'] = $nframes;
        }

        $info['type']   = strtolower($image->getImageFormat());
        if (isset(self::$pictype2extension[$info["type"]]))
        {
            $info['type'] = self::$pictype2extension[$info["type"]];
        }
        if ($info["type"] != "gif")
        {
            $info['width']  = $image->getImageWidth();
            $info['height'] = $image->getImageHeight();
        }

        $image->clear();
        $image->destroy();
        return empty(self::$goodpictype[$info['type']]) ? false : $info;
    }

    public static function copyImage($src, &$des, $strip=false, $firstgif=false)
    {
        $image = new Imagick($src);
        $type = strtolower($image->getImageFormat());
        if($type == 'gif' && $firstgif)
        {
            foreach($image as $frame)
            {
                $new_image = new Imagick();
                $new_image->addImage($frame->getImage());
                $new_image->setImageFormat('jpg');
                $image = $new_image->clone();
                $new_image->destroy();
                break;
            }
        }

        $cs = $image->getImageColorspace();
        $needrewrite = false;
        if (($cs == Imagick::COLORSPACE_CMYK)
            &&($type =="jpeg"
            ||$type =="jpg"
            ||$type =="pjpeg"))
        {
            $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
            $needrewrite = true;
        }
        else if($strip)
        {
            $needrewrite = true;
        }

        if($needrewrite)
        {
            if($strip)
            {
                $image->stripImage();
            }
            if(!empty($des))
            {
                $image->writeImages($des, true);
            }
            else
            {
                $des = $image->getImagesBlob();
            }
        }
        else
        {
            if(!empty($des))
            {
                copy($src, $des);
            }
            else
            {
                $des = file_get_contents($src);
            }
        }
        $image->clear();
        $image->destroy();
    }

    public static function rotateImage($src, $des, $rotate)
    {
        $arr = pathinfo($src);
        if(self::$nopicallowtype[strtolower($arr["extension"])])
        {
            copy($src, $des);
            return is_file($des);
        }
        switch ($rotate)
        {
        case 1:
        case 2:
        case 3:
            $protate = (int)$rotate * 90;
            $image = new Imagick($src);
            if(strtolower($image->getImageFormat()) == "gif")
            {
                //取第一帧
                foreach($image as $frame)
                {
                    $frame->rotateImage(new ImagickPixel(), $protate);
                }
            }
            else
            {
                $image->rotateImage(new ImagickPixel(), $protate);

                $cs = $image->getImageColorspace();
                if ($cs == Imagick::COLORSPACE_CMYK) {
                    $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
                }
            }
            $image->writeImages($des, true);
            $image->clear();
            $image->destroy();
            break;
        default:
            if ($src != $des)
            {
                self::copyImage($src, $des);
                //copy($src, $des);
            }
        }
        return is_file($des);
    }

    public static function cropImageEx($src, $info, $des, $width , $height)
    {
        $r1 = $info["width"] / $info["height"];
        $r2 = $width / $height;

        if ($r1 > $r2)
        {    //原图长了
            if ($info["width"] > $width)
            {
                $zoom = min(1, $height / $info["height"]);
                $left = floor(($zoom * $info["width"] - $width) / 2);
                $top = 0;
                return self::cropImage($src, $info, $des, $zoom, $left, $top, $width, $height, false);
            }
        }
        else if ($r1 < $r2)
        {    //原图高了
            if ($info["height"] > $height)
            {
                $zoom = min(1, $width / $info["width"]);
                $top = floor(($zoom * $info["height"] - $height) / 2);
                $left = 0;
                return self::cropImage($src, $info, $des, $zoom, $left, $top, $width, $height, false);
            }
        }
        return self::convertImage($src, $info, $des, $width, $height, 0, false);
    }

    public static function cropImage($src , $info , $des , $zoom , $left , $top , $width , $height , $fillblank = true)
    {
        $arr = pathinfo($src);
        if(self::$nopicallowtype[strtolower($arr["extension"])])
        {
            copy($src, $des);
            return is_file($des);
        }

        if ($zoom <= 1)
        {
            $geowidth = ceil($zoom*$info["width"]);
            $geoheight = ceil($zoom*$info["height"]);

            $image = new Imagick();
            try
            {
                $image->SetOption("size", $geowidth."X".$geoheight);
                $image->readImage($src);
                if(strtolower($image->getImageFormat()) == "gif")
                {
                    //取第一帧
                    foreach($image as $frame)
                    {
                        $image = $frame;
                        break;
                    }
                }
                $image->scaleImage($geowidth,$geoheight, true);
                $image->setCompressionQuality(80);
            }
            catch(Exception $ex)
            {
            }

            $left2 = $left>=0?"+".$left:$left;
            $top2 = $top>=0?"+".$top:$top;
            try
            {
                $image->cropImage($width, $height, $left2, $top2);
                if(strtolower($image->getImageFormat()) == "gif")
                {
                    $image->setImagePage(0, 0, 0, 0);
                }
            }
            catch(Exception $ex)
            {
                //TODO: 记日志
            }
            $cs = $image->getImageColorspace();
            if ($cs == Imagick::COLORSPACE_CMYK) {
                $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
            }
            $image->stripImage();
            $image->writeImage($des);
            $image->clear();
            $image->destroy();
            if ($left >=0 && $top >= 0 && ($left + $width) <= $geowidth && ($top + $height) <= $geoheight)
            {
                return is_file($des);
            }
        }
        else
        {
            $left2 = floor($left / $zoom);
            $top2 = floor($top / $zoom);
            $left2 = $left2>=0?"+".$left2:$left2;
            $top2 = $top2>=0?"+".$top2:$top2;
            $image = new Imagick($src);
            $cwidth = ceil($width/$zoom);
            $cheight = ceil($height/$zoom);
            try
            {
                if(strtolower($image->getImageFormat()) == "gif")
                {
                    //取第一帧
                    foreach($image as $frame)
                    {
                        $image = $frame;
                        break;
                    }
                }
                $image->cropImage($cwidth, $cheight,$left2,$top2);
            }
            catch(Exception $ex)
            {
                //TODO:记日志
            }

            $geowidth = ceil($zoom*$info["width"]);
            $geoheight = ceil($zoom*$info["height"]);

            $width2 = $width;
            if ($left < 0)
            {
                $width2 += $left;
            }
            if (($left + $width) > $geowidth)
            {
                $width2 -= ($left + $width) - $geowidth;
            }
            $height2 = $height;
            if ($top < 0)
            {
                $height2 += $top;
            }
            if (($top + $height) > $geoheight)
            {
                $height2 -= ($top + $height) - $geoheight;
            }

            try
            {
                $image->scaleImage($width2, $height2);
                $image->setCompressionQuality(80);
            }
            catch(Exception $ex)
            {
                //TODO: 记日志
            }
            $cs = $image->getImageColorspace();
            if ($cs == Imagick::COLORSPACE_CMYK) {
                $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
            }
            $image->stripImage();
            $image->writeImage($des);
            $image->clear();
            $image->destroy();
            if ($width2 == $width && $height2 == $height)
            {
                return is_file($des);
            }
        }
        if (!$fillblank)
        {
            return is_file($des);
        }

        $x = "+0";
        $y = "+0";
        if($left < 0)
        {
            $x = "+".(0-$left);
        }
        if($top < 0)
        {
            $y = "+".(0-$top);
        }

        $oimage = new Imagick($des);

        $image = new Imagick(IMAGE_BLANK);
        try
        {
            $image->compositeImage($oimage, Imagick::COMPOSITE_DEFAULT, $x, $y);
        }
        catch(Exception $ex)
        {
            //TODO: 记日志
        }
        try
        {
            $image->cropImage($width, $hight, 0, 0);
        }
        catch(Exception $ex)
        {
            //TODO: 记日志
        }
        $cs = $image->getImageColorspace();
        if ($cs == Imagick::COLORSPACE_CMYK) {
            $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
        }
        $image->setCompressionQuality(80);
        $image->stripImage();
        $image->writeImage($des);
        $image->clear();
        $image->destroy();
        return is_file($des);
    }

    // 计算裁剪后的图片尺寸
    public static function getCropInfo($info, $width, $height)
    {
        if (! $info["height"])
        {
            return $info;
        }
        $r1 = $info["width"] / $info["height"];
        $r2 = $width / $height;

        if ($r1 > $r2)
        {
            if ($info["width"] > $width)
            {
                return array("width" => $width, "height" => min($info["height"], $height));
            }
            else
            {
                return array("width" => $info["width"], "height" => $info["height"]);
            }
        }
        else if ($r1 < $r2)
        {
            if ($info["height"] > $height)
            {
                return array("width" => min($info["width"], $width), "height" => $height);
            }
            else
            {
                return array("width" => $info["width"], "height" => $info["height"]);
            }
        }
        else
        {
            return self::getConvertInfo($info, $width, $height, 1);
        }
    }

    // 计算转换后的图片尺寸
    public static function getConvertInfo($info, $width, $height, $force)
    {
        if ($force == 1)//满足宽
        {
            if ($width >= $info["width"])
            {
                return array("width" => $info["width"], "height" => $info["height"]);
            }
            else
            {
                return array("width" => $width, "height" => floor($width * $info["height"] / $info["width"]));
            }
        }
        else if ($force == 2)//满足高
        {
            if ($height >= $info["height"])
            {
                return array("width" => $info["width"], "height" => $info["height"]);
            }
            else
            {
                return array("width" => floor($height * $info["width"] / $info["height"]), "height" => $height);
            }
        }
        else    //宽高都满足
        {
            if ($width >= $info["width"] && $height >= $info["height"])
            {
                return array("width" => $info["width"], "height" => $info["height"]);
            }

            $h = ceil($width * $info["height"] / $info["width"]);
            if ($h > $height)   //
            {
                $w = floor($height * $info["width"] / $info["height"]);
                return array("width" => $w, "height" => $h);
            }
            else
            {
                return array("width" => $width, "height" => $h);
            }
        }
    }

    //$force 1 满足宽度即可
    //       2 满足高度即可
    //       其他 同时满足高度和宽度
    // 增加对gif图片只显示第一帧
    public static function convertImage($src , $info , &$des , $width, $height, $force , $bsharpen, $strip = true, $rotate=0, $firstgif=false)
    {
        if ($rotate)
        {
            return self::_convertRotateImage($src , $info , $des , $width, $height, $force , $bsharpen, $strip, $rotate,$firstgif);
        }

        $arr = pathinfo($src);

        if(self::$nopicallowtype[strtolower($arr["extension"])])
        {
            if(!empty($des))
            {
                copy($src, $des);
            }
            else
            {
                $des = file_get_contents($src);
            }
            return is_file($des);
        }
        if ($force == 1)
        {
            if ($width >= $info["width"])
            {
                self::copyImage($src, $des, $strip, $firstgif);
                //copy($src, $des);
                return is_file($des);
            }

            $h = floor($width * $info["height"] / $info["width"]);

            $size = max($h, $width);
        }
        else if ($force == 2)
        {
            if ($height >= $info["height"])
            {
                self::copyImage($src, $des, $strip, $firstgif);
                //copy($src, $des);
                return is_file($des);
            }

            $w = floor($height * $info["width"] / $info["height"]);

            $size = max($w, $height);
        }
        else if ($force == 4)//短边压缩
        {
            if ($width >= $info["width"] || $height >= $info["height"])
            {
                self::copyImage($src, $des, $strip, $firstgif);
                //copy($src, $des);
                return is_file($des);
            }

            $h = ceil($width * $info["height"] / $info["width"]);
            if ($h > $height)	//偏高，要按宽度压缩
            {
                $size = min($h, $info["width"]);
            }
            else	//偏宽，要按高度压缩
            {
            	$w = floor($height * $info["width"] / $info["height"]);
                $size = min($w, $info["height"]);
            }
        }
        else
        {
            if ($width >= $info["width"] && $height >= $info["height"])
            {
                self::copyImage($src, $des, $strip, $firstgif);
                //copy($src, $des);
                return is_file($des);
            }

            $h = ceil($width * $info["height"] / $info["width"]);
            if ($h > $height)
            {
                $w = floor($height * $info["width"] / $info["height"]);
                $size = max($height, $w);
            }
            else
            {
                $size = max($width, $h);
            }
        }

        $image = new Imagick();
        if($bsharpen)
        {
            $image->readImage($src);
            try
            {
                $image->sharpenImage(2, 10);
            }
            catch(Exception $ex)
            {
                //TODO: 记日志
            }
        }
        else
        {
            $image->SetOption("size", $size."X".$size);
            $image->readImage($src);
        }
        try
        {
            if(strtolower($image->getImageFormat()) == "gif")
            {
                if($firstgif)
                {
                    foreach($image as $frame)
                    {
                        $new_image = new Imagick();
                        $frame->scaleImage($size, $size, true);
                        $new_image->addImage($frame->getImage());
                        $new_image->setImageFormat('jpg');
                        $image = $new_image->clone();
                        $new_image->destroy();
                        break;
                    }
                }
                else
                {
                    //取第一帧
                    foreach($image as $frame)
                    {
                              $frame->scaleImage($size, $size, true);
                    }
                }
            }
            else
            {
                $image->scaleImage($size, $size, true);
                $cs = $image->getImageColorspace();
                if ($cs == Imagick::COLORSPACE_CMYK) {
                        $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
                }
            }
        }
        catch(Exception $ex)
        {
            //TODO: 记日志
        }

        if($strip)
        {
            $image->stripImage();
        }

        if(!empty($des))
        {
            $image->writeImages($des, true);
        }
        else
        {
            $des = $image->getImagesBlob();
        }

        $image->clear();
        $image->destroy();
        return is_file($des);
    }




    //$force 1 满足宽度即可
    //       2 满足高度即可
    //       其他 同时满足高度和宽度
    //rotate 图片旋转 1--顺时针90度，2--顺时针180度，3--顺时针270度
    public static function _convertRotateImage($src , $info , &$des , $width, $height, $force , $bsharpen, $strip, $rotate=0,$firstgif = false)
    {
        $arr = pathinfo($src);
        if(self::$nopicallowtype[strtolower($arr["extension"])])
        {
            copy($src, $des);
            return is_file($des);
        }
        if ($force == 1)
        {
            if ($width >= $info["width"])
            {
                $width = $info["width"];
                $h = floor($width * $info["height"] / $info["width"]);
                $size = max($h, $width);
            }
            else
            {
                $h = floor($width * $info["height"] / $info["width"]);
                $size = max($h, $width);
            }
        }
        else if ($force == 2)
        {
            if ($height >= $info["height"])
            {
                $height = $info["height"];
                $w = floor($height * $info["width"] / $info["height"]);
                $size = max($w, $height);
            }
            else
            {
                $w = floor($height * $info["width"] / $info["height"]);
                $size = max($w, $height);
            }
        }
        else
        {
            if ($width >= $info["width"]
                && $height >= $info["height"])
            {
                $width =  $info["width"];
                $height = $info["height"];
                $size = max($height, $width);
            }
            else
            {
                $h = ceil($width * $info["height"] / $info["width"]);
                if ($h > $height)
                {
                    $w = floor($height * $info["width"] / $info["height"]);
                    $size = max($height, $w);
                }
                else
                {
                    $size = max($width, $h);
                }
            }
        }

        if ($rotate >= 1 && $rotate <=3)
        {
            $protate = (int)90 * $rotate;
        }
        else
        {
            $protate = 0;
        }

        $image = new Imagick();
        if($bsharpen)
        {
            $image->readImage($src);
            try
            {
                $image->sharpenImage(2, 10);
            }
            catch(Exception $ex)
            {
                //TODO: 记日志
            }
        }
        else
        {
            $image->SetOption("size", $size."X".$size);
            $image->readImage($src);
        }
        try
        {
            if(strtolower($image->getImageFormat()) == "gif")
            {
                if($firstgif)
                {
                    foreach($image as $frame)
                    {
                        $new_image = new Imagick();
                        $frame->scaleImage($size, $size, true);
                        $new_image->addImage($frame->getImage());
                        $new_image->setImageFormat('jpg');
                        $image = $new_image->clone();
                        $new_image->destroy();
                        break;
                    }
                }
                else
                {
                    //取第一帧
                    foreach($image as $frame)
                    {
                        $frame->scaleImage($size, $size, true);
                    }
                }
            }
            else
            {
                $image->scaleImage($size, $size, true);
            }
        }
        catch(Exception $ex)
        {
            //TODO: 记日志
        }

        if($protate > 0)
        {
            if(strtolower($image->getImageFormat()) == "gif")
            {
                //取第一帧
                foreach($image as $frame)
                {
                    $frame->rotateImage(new ImagickPixel(), $protate);
                }
            }
            else
            {
                $image->rotateImage(new ImagickPixel(), $protate);
                $cs = $image->getImageColorspace();
                if ($cs == Imagick::COLORSPACE_CMYK) {
                    $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
                }
            }
        }

        if($strip)
        {
            $image->stripImage();
        }
        if(!empty($des))
        {
            $image->writeImages($des, true);
        }
        else
        {
            $des = $image->getImagesBlob();
        }
        $image->clear();
        $image->destroy();
        return is_file($des);
    }

    /**
     * 通过色相(hue), 饱和度(saturation), 明亮(brightness), 对比度(contrast) 调整图片效果
     */
    public static function imgEffect($src, $hue, $saturation, $brightness, $contrast)
    {
        if (empty($src))
        {
            return false;
        }
        $image = new Imagick();
        $image->readimageblob($src);

        $type = strtolower($image->getImageFormat());
        if($type == 'gif')
        {
            foreach($image as $frame)
            {
                $new_image = new Imagick();
                $new_image->addImage($frame->getImage());
                $new_image->setImageFormat('jpg');
                $image = $new_image->clone();
                $new_image->destroy();
                break;
            }
        }

        $ret = $image->modulateImage($brightness, $saturation, $hue);
        if(!empty($contrast))
        {
            $contrast = intval($contrast);
            if($contrast > 0)
            {
                for($i = 0 ; $i < abs(intval($contrast)) ; $i++)
                {
                    $image->contrastImage(1);
                }
            }
            else
            {
                for($i = 0 ; $i < abs(intval($contrast)) ; $i++)
                {
                    $image->contrastImage(0);
                }
            }
        }

        $res = $image->getImagesBlob();
        $image->clear();
        $image->destroy();
        return $res;
    }

    /**
     * 通过加带有透明度的蒙版 调整图片效果(暂不使用)
     */
    public static function imgEffectEx($src, $colorize, $opacity)
    {
        $image = new Imagick($src);

        $type = strtolower($image->getImageFormat());
        if($type == 'gif')
        {
            foreach($image as $frame)
            {
                $new_image = new Imagick();
                $new_image->addImage($frame->getImage());
                $new_image->setImageFormat('jpg');
                $image = $new_image->clone();
                $new_image->destroy();
                break;
            }
        }

        $image->colorizeImage($colorize, $opacity);
        $res = $image->getImagesBlob();
        $image->clear();
        $image->destroy();
        return $res;
    }

    public static function getImageType($content)
    {
        if($content{0}.$content{1} == "\xff\xd8")
        {
             return 'jpeg';
        }
        else if($content{0}.$content{1}=="\x89\x50")
        {
             return 'png';
        }
        else if($content{0}.$content{1}=="\x42\x4d")
        {
        	return 'bmp';
        }
        else if($content{0}.$content{1}.$content{2} == "\x47\x49\x46")
        {
            //gif87, gif89
            if($content{4} == "\x37" || $content{4} == "\x39")
            {
                return 'gif';
            }
        }
        return 'jpg';
    }
}