<?php
/*===============================================================
*   Copyright (C) 2017 All rights reserved.
*   
*   Filename：wx_debug_log.php
*   Creator：Mark Luo
*   Created Date：01/11/2017
*   Description：write verbose log into debugLog/wx_debug_yyyymmdd.log
*
*   Modified History：
*
================================================================*/

class WXDebugLog
{
    var $logfile_path='./debugLog';
    var $logfile_name;
    var $detail;

    function WXDebugLog()
    {
        $this->logfile_name='wx_debug_'.date('Ymd',time()).'.log';
        $this->detail='';
    }

    function appendLog($str)
    {
        $this->detail=$this->detail.'['.date('Y-m-d H:i:s.u', time())."]\t".$str."\n";
    }

    function saveLog()
    {
        $logfile=$this->logfile_path.'/'.$this->logfile_name;
        if ($f = fopen($logfile,'a+')) {
            fputs($f,$this->detail);
            fclose($f);
        }
        $this->detail='';
    }
}
?>
