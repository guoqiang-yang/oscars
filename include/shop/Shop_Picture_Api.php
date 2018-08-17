<?php
/**
 * 用户图片相关接口
 */
class Shop_Picture_Api  extends Base_Api
{

	public static function cropImage($filename, $midImgWidth , $midLeft , $midTop , $midWidth , $midHeight)
	{
		assert(!empty($filename));
		assert(!empty($midImgWidth));

		$sp = new Shop_Picture();
		$ret = $sp->cropImage($filename, $midImgWidth , $midLeft , $midTop , $midWidth , $midHeight);
		return $ret;
	}

	public static function getPicInfo($pic)
	{
		list($pid, $ext) = explode('.', $pic);

		$sp = new Shop_Picture();
		$pic = $sp->getPicInfo($pid);
		if (!empty($pic))
		{
			$pic['srcinfo'] = json_decode($pic['srcinfo'], true);
		}

		return $pic;
	}
}
