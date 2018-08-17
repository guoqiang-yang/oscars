<?php
include_once ('../global.php');



class App extends App_Cli
{
    public static $A_a = array('a'=>1);
    
    protected function getPara()
    {
       if (ENV == 'online')
        {
            echo "Sorry! My Baby!!\n"; exit;
        }
        
        //echo "Hello, Welcome To Itest ...\n"; 
    }
    protected  function isNewerVersion($curVersion, $cmpVersion)
    {
        if (empty($curVersion)) return false;
        
        return str_replace('.', '', $curVersion)>= str_replace('.', '', $cmpVersion)? true: false;
    }

    protected function main()
    {
        echo ip2long('39.105.106.135'); exit;
    }
    
    public function myfunc($key, $val)
    {
        echo "$key -> $val\n";
    }


    
    private function _drinkSoda($money)
    {   
        if ($money <= 0) return 0;

        $nextMoney = floor($money/2);
        
        return $money + $this->_drinkSoda($nextMoney);
    }  
    
    private function _print($a)
    {
        foreach ($a as $key => &$val) {echo "$key=>$val\n";} // do nothing
        var_dump($a);
        
        
        foreach ($a as $key => $val) {echo "$key=>$val\n";} // do nothing
        var_dump($a);
    }
    
    private function _reFuncnameApi()
    {
        $ff = new Funcname();
        
        $ff->_checkFuncname();
        $ff->_reFuncnameFunc();
    }
    
    // in class: funcname
    private function _checkFuncname()
    {
        
    }
    
    private function _reFuncnameFunc()
    {
        
    }
    
    private function _getPrivilege4SpecProducts($products, $hadPrivilege=0)
    {
        $pprice = 0-$hadPrivilege;
        $specSid = 1234;
        $specSalePrice = 1111;
        $specNum = 0;
        $specSrcPrice = 0;
        foreach($products as $pinfo)
        {
            $pprice += $pinfo['num']*$pinfo['price'];
            if ($specSid == $pinfo['sid'])
            {
                $specNum = $pinfo['num'];
                $specSrcPrice = $pinfo['price'];
            }
        }
        
        $basePrivilege = $specSrcPrice - $specSalePrice;
        $basePrice = 60000;
        $scale = 3;
        $privilege = 0;
        
        $maxPrivNum = min($specNum, floor($pprice/$basePrice)*$scale);
        
        for($i=1; $i<=$maxPrivNum; $i++)
        {
           $compPrice = ceil($i/$scale) * $basePrice;
           
           if ($pprice-$basePrivilege >= $compPrice)
           {
               $pprice -= $basePrivilege;
               $privilege += $basePrivilege;
           }
           else
           {
               break;
           }
        }
        
        return $privilege;
    }
    
    
}

$app = new App();
$app->run();

exit;



Send_Msg::sendMsg('sms');
Send_Msg::sendMsg('push');
Send_Msg::sendMsg('xx');

class Send_Msg
{
    
    public static function sendMsg($flag)
    {
        $className = 'Send_Msg_'. ucfirst(strtolower($flag));
        
        if (!class_exists($className))
        {
            //throw new Exception('...');
        }
        
       $handler = new $className();
        
        $handler->send();
    }
    protected function send()
    {
        echo "sorry! please To Defind!\n";
    }
    
}

class send_Msg_Push extends Send_Msg
{
    protected function __construct()
    {
        echo 'construct-';
    }
    
    protected function send()
    {
        echo "send-push\n";
    }
    
}

class Send_Msg_Sms extends Send_Msg
{
    
    protected function __construct()
    {
        echo "construct-";
    }
    
    protected function send()
    {
        echo "send-SMS\n";
    }
    
}

class Send_Msg_Xx extends Send_Msg
{
    
    protected function __construct()
    {
        echo "construct-";
    }
    
}
