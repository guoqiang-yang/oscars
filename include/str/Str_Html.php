<?php

/**
 * 获取翻页块html的类
 */
class Str_Html
{
    public static function getSimplePage($start, $num, $total, $app)
    {
        $pagehtml = "";

        $app = strpos($app, '?') ? ($app . '&start=') : ($app . '?start=');
        $pagenum = 7;
        $curpage = floor($start / $num) + 1;
        $totalpage = ceil($total / $num);
        $minpage = max($curpage - round($pagenum / 2) + 1, 1);
        $maxpage = min($minpage + $pagenum - 1, $totalpage);
        $minpage = max($maxpage - $pagenum + 1, 1);

        if ($totalpage <= 1)
        {
            return "";
        }
        $preHtml = '<li %s><a href="%s" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
        if ($curpage > 1)
        {
            $pagehtml .= sprintf($preHtml, '', $app . ($curpage - 2) * $num);
        }
        else
        {
            $pagehtml .= sprintf($preHtml, 'class="disabled"', '#');
        }

        for ($i = $minpage; $i <= $maxpage; $i++)
        {
            $tmpHtml = '<li %s><a href="%s">%d</a></li>';
            $href = $app . (($i - 1) * $num);
            if ($i != $curpage)
            {
                $pagehtml .= sprintf($tmpHtml, '', $href, $i);
            }
            else
            {
                $pagehtml .= sprintf($tmpHtml, 'class="active"', $href, $i);
            }
        }

        $nextHtml = '<li %s><a href="%s" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        if ($curpage < $totalpage)
        {
            $pagehtml .= sprintf($nextHtml, '', $app . ($curpage * $num));
        }
        else
        {
            $pagehtml .= sprintf($nextHtml, 'class="disabled"', '#');
        }

        return $pagehtml;
    }

    public static function getSimplePage4Corp($start, $num, $total, $app)
    {
        $app = strpos($app, '?') ? ($app . '&start=') : ($app . '?start=');
        $pagenum = 7;
        $curpage = floor($start / $num) + 1;
        $totalpage = ceil($total / $num);
        $minpage = max($curpage - round($pagenum / 2) + 1, 1);
        $maxpage = min($minpage + $pagenum - 1, $totalpage);
        $minpage = max($maxpage - $pagenum + 1, 1);

        if ($totalpage <= 1)
        {
            return "";
        }

        $pagehtml = '<div class="trun_page">';
        $firstHtml = '<a href="%s" class="first" title="首页">首页</a>';
        $preHtml = '<a href="%s" class="prev">上一页</a>';
        if ($curpage > 1)
        {
            $pagehtml .= sprintf($firstHtml, $app . 0);
            $pagehtml .= sprintf($preHtml, $app . ($curpage - 2) * $num);
        }
        else
        {
        }

        for ($i = $minpage; $i <= $maxpage; $i++)
        {
            if ($i != $curpage)
            {
                $tmpHtml = '<a href="%s">%d</a>';
                $href = $app . (($i - 1) * $num);
                $pagehtml .= sprintf($tmpHtml, $href, $i);
            }
            else
            {
                $tmpHtml = '<span class="curr">%d</span>';
                $pagehtml .= sprintf($tmpHtml, $i);
            }
        }

        $finalHtml = '<a href="%s" class="last" title="尾页">尾页</a>';
        $nextHtml = '<a href="%s" class="next">下一页</a>';
        if ($curpage < $totalpage)
        {
            $pagehtml .= sprintf($nextHtml, $app . ($curpage * $num));
            $pagehtml .= sprintf($finalHtml, $app . ($curpage * $num));
        }
        else
        {
        }

        $totalHtml = '<span>共%d页</span></div>';
        $pagehtml .= sprintf($totalHtml, $totalpage);

        return $pagehtml;
    }

    function getJsPagehtml2($start, $num, $total, $class)
    {
        $pagehtml = "";

        $pagenum = 5;
        $curpage = floor($start / $num) + 1;
        $totalpage = ceil($total / $num);
        $minpage = max($curpage - round($pagenum / 2) + 1, 1);
        $maxpage = min($minpage + $pagenum - 1, $totalpage);
        $minpage = max($maxpage - $pagenum + 1, 1);

        if ($totalpage <= 1)
        {
            return "";
        }
        $preHtml = '<li %s><a href="javascript:;" aria-label="Previous" class="%s" data-start="%d"><span aria-hidden="true">&laquo;</span></a></li>';
        if ($curpage > 1)
        {
            $pagehtml .= sprintf($preHtml, '', $class, ($curpage - 2) * $num);
        }
        else
        {
            $pagehtml .= sprintf($preHtml, 'class="disabled"', $class, '#');
        }

        for ($i = $minpage; $i <= $maxpage; $i++)
        {
            $tmpHtml = '<li %s><a href="javascript:;" class="%s" data-start="%d">%d</a></li>';

            if ($i != $curpage)
            {
                $pagehtml .= sprintf($tmpHtml, '', $class, ($i - 1) * $num, $i);
            }
            else
            {
                $pagehtml .= sprintf($tmpHtml, 'class="active"', $class, ($i - 1) * $num, $i);
            }
        }

        $nextHtml = '<li %s><a href="javascript:;" aria-label="Next" class="%s" data-start="%d"><span aria-hidden="true">&raquo;</span></a></li>';
        if ($curpage < $totalpage)
        {
            $pagehtml .= sprintf($nextHtml, '', $class, ($curpage * $num));
        }
        else
        {
            $pagehtml .= sprintf($nextHtml, 'class="disabled"', '#');
        }

        return $pagehtml;
    }

    function getJsPagehtml($start, $num, $total)
    {
        $pagenum = 5;
        $curpage = floor($start / $num) + 1;
        $totalpage = ceil($total / $num);
        $minpage = max($curpage - round($pagenum / 2) + 1, 1);
        $maxpage = min($minpage + $pagenum - 1, $totalpage);
        $minpage = max($maxpage - $pagenum + 1, 1);

        if ($totalpage <= 1)
        {
            return "";
        }

        $pagehtml = "";
        if ($curpage > 1)
        {
            $pagehtml .= "<a href='javascript;' class=\"_j_page\" data-start=" . (($curpage - 2) * $num) . " onfocus=\"this.blur();\" class=\"same_page\"><strong>上一页</strong></a>";
        }

        for ($i = $minpage; $i <= $maxpage; $i++)
        {
            if ($i != $curpage)
            {
                $pagehtml .= "<a href='javascript;' class=\"_j_page\" data-start=" . (($i - 1) * $num) . " onfocus=\"this.blur();\">" . $i . "</a>";
            }
            else
            {
                $pagehtml .= "<a href='javascript;' class=\"active\">" . $i . "</a>";
            }
        }

        if ($curpage < $totalpage)
        {
            $pagehtml .= "<a href='javascript;' class=\"_j_page\" data-start=" . ($curpage * $num) . " onfocus=\"this.blur();\" class=\"same_page\"><strong>下一页</strong></a>";
        }

        return '<div class="clearfix fr">' . $pagehtml . '</div>';
    }

    static function addLink($src, $withimg)
    {
        $res = array();
        if (0 == preg_match("/mms:\/\/|http:\/\/|ftp:\/\/|https:\/\/|www\./i", $src, $res, PREG_OFFSET_CAPTURE))
        {
            return Str_Html::space2nbsp($src);
        }

        $len = strlen($src);
        $start = $res[0][1];
        for ($end = $start; $end < $len; $end++)
        {
            $vchr = $src[$end];
            $ov = ord($vchr);

            if ($end + 6 < $len)
            {
                $fourchar = substr($src, $end, 4);
                $sixchar = substr($src, $end, 6);
                if ($fourchar == "&lt;" || $fourchar == "&gt;")
                {
                    break;
                }
                if ($sixchar == "&quot;")
                {
                    break;
                }
            }
            else if ($end + 4 < $len)
            {
                $fourchar = substr($src, $end, 4);
                if ($fourchar == "&lt;" || $fourchar == "&gt;")
                {
                    break;
                }
            }

            if ($ov <= 32 || $vchr == "'" || $vchr == '"' || $vchr == '<' || $vchr == '>' || $ov >= 128)
            {
                break;
            }
        }

        $url = substr($src, $start, $end - $start);
        $posgt = strpos($src, ">", $end);
        $poslt = strpos($src, "<", $end);
        if (($posgt !== FALSE && $poslt !== FALSE && $poslt > $posgt) || ($posgt !== FALSE && $poslt === FALSE))
        {
            return Str_Html::space2nbsp(substr($src, 0, $start)) . $url . Str_Html::addLink(substr($src, $end), $withimg);
        }
        else if ($withimg && Str_Html::IsImage($url))
        {
            return Str_Html::space2nbsp(substr($src, 0, $start)) . "<img src=\"" . (strtolower(substr($url, 0, 4)) == "www." ? "http://" . $url : $url) . "\" border=0>" . Str_Html::addLink(substr($src, $end), $withimg);
        }

        // 去除url尾部的空格
        $nbsp = "";
        while (substr($url, -6, 6) == "&nbsp;")
        {
            $nbsp .= "&nbsp;";
            $url = substr($url, 0, strlen($url) - 6);
        }

        return Str_Html::space2nbsp(substr($src, 0, $start)) . "<a href=\"" . (strtolower(substr($url, 0, 4)) == "www." ? "http://" . $url : $url) . "\" target=_blank onclick=\"event.cancelBubble=true;\" onmousedown=\"javascript:event.cancelBubble=true;\">" . $url . "</a>" . Str_Html::addLink($nbsp . substr($src, $end), $withimg);
    }

    static function space2nbsp($str)
    {
        return str_replace("\n ", "\n&nbsp;", str_replace("  ", "&nbsp; ", $str));
    }

    static function IsImage($url)
    {
        $imgfile = array(".gif", ".png", ".x-png", ".jpg", ".jpeg", "pjpeg");
        foreach ($imgfile as $tmp)
        {
            $len = strlen($tmp);
            if (0 == strncasecmp($tmp, substr($url, strlen($url) - $len), $len))
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    static function getUserLogo($filename, $size = '')
    {
        $filename = trim($filename);
        if (empty($filename))
        {
            list($size, $tmp) = explode('x', $size);
            $url = 'http://' . PIC_HOST . '/images/dft/head_' . $size . '.jpg';
        }
        else
        {
            $filename = basename($filename);
            list($token, $type) = explode('.', $filename);
            list($picid, $tmp) = explode('_', $token);
            $filename = $picid . '.' . $type;
            $url = Data_Pic::getHeadUrl($filename, $size);
        }

        return $url;
    }

    static function getVoteLogo($filename, $size = '')
    {
        $filename = trim($filename);
        if (empty($filename))
        {
            list($size, $tmp) = explode('x', $size);
            $url = 'http://' . PIC_HOST . '/images/dft/vote_' . $size . '.jpg';
        }
        else
        {
            $filename = basename($filename);
            list($token, $type) = explode('.', $filename);
            list($picid, $tmp) = explode('_', $token);
            $filename = $picid . '.' . $type;
            $url = Data_Pic::getPicUrl($filename, $size);
        }

        return $url;
    }

    static function getPicUrl($filename, $size = '')
    {
        $filename = trim($filename);
        $filename = basename($filename);
        list($token, $type) = explode('.', $filename);
        list($picid, $tmp) = explode('_', $token);
        $filename = $picid . '.' . $type;
        $url = Data_Pic::getPicUrl($filename, $size);

        return $url;
    }

    static function getUserLink($uid)
    {
        $url = '/u/' . $uid;

        return $url;
    }

    static function getFriendButton($fuid, $relation)
    {
        if (3 == $relation)
        {
            $class = 'mutual _j_follow_user';
            $name = '互为关注';
        }
        elseif (1 == $relation)
        {
            $class = 'finish _j_follow_user';
            $name = '已关注';
        }
        else
        {
            $class = 'unfinished _j_follow_user';
            $name = '关注';
        }

        $html = sprintf('<a href="javascript:;" class="%s" data-fuid="%d">%s</a>', $class, $fuid, $name);

        return $html;
    }
}