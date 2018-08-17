<?php

class Tool_Lock
{
    public static function lock($name)
    {
        $lockfp = fopen($name . ".lock", "w");
        if (!$lockfp)
        {
            return FALSE;
        }

        if (!flock($lockfp, LOCK_EX | LOCK_NB))
        {
            return FALSE;
        }

        return $lockfp;
    }

    public static function unlock($fp)
    {
        flock($fp, LOCK_UN);
    }

    public static function lockByPid($pidFile)
    {
        if (!strlen($pidFile))
        {
            return FALSE;
        }
        if (is_file($pidFile))
        {
            $pid = intval(trim(file_get_contents($pidFile)));
            if (file_exists("/proc/" . $pid))
            {
                // 进程执行中
                return FALSE;
            }
        }
        $mypid = getmypid();

        return file_put_contents($pidFile, $mypid);
    }
}