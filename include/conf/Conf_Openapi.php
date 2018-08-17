<?php

/**
 * 开放平台使用.
 */

class Conf_Openapi
{
    
    const Three_Space_Appid = 10001;		//三空间
    const Shandian_Refresh_Appid = 20001;   //闪电刷新
    //const Sina_Qiang_Gongzhang_Appid = 20002;   //新浪抢工长
    //const Haogongren_Appid = 20003;     //好工人
    const JiaShiFen_Appid = 20004;          //家十分


    /**
     * app secret.
     * 
     *  eg: md5(HC:Haogongren:20003)
     */
	public static $Openapi_Secret_Key = array(
		self::Three_Space_Appid => 'e720d63eb2d0063eed5a7bfc230cc0d0',
        self::Shandian_Refresh_Appid => '315aaf3efda233b5e27d8552829bbc0f',
        //self::Sina_Qiang_Gongzhang_Appid => '3cdc8d0637003922ecd5cbcaddf5b08a',
        //self::Haogongren_Appid  => 'cd9e4edda4df9847d354bca86b635f2c',
        self::JiaShiFen_Appid => '4b840a58a0a7c0041dda2d8fe553328d',
	);
    
    /**
     * app name.
     */
    public static $Appid2Name = array(
        self::Three_Space_Appid => '三空间',
        self::Shandian_Refresh_Appid => '闪电刷新',
        //self::Sina_Qiang_Gongzhang_Appid => '新浪抢工长',
        //self::Haogongren_Appid => '好工人',
        self::JiaShiFen_Appid => '家十分',
    );
    
    public static $onlineAppId = array(
        self::Three_Space_Appid => 1,
        self::Shandian_Refresh_Appid => 1,
        self::JiaShiFen_Appid => 1,
    );
    
    /**
     * 合伙人定义的code
     */
    public static $Appid2PartnerCode = array(
        self::Three_Space_Appid => 'f6497e04d7894f8e48f82c021292f60c',
    );
    
    /**
     * 需要通知合作者，调用接口的配置
     */
    public static $partnerCallback = array(
        self::Three_Space_Appid => array(
            'update_order' => 'http://api.3kongjian.com/accessory_mall/updateOrder',
        ),
    );
}