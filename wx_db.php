<?php
/*===============================================================
*   Copyright (C) 2017 All rights reserved.
*   
*   Filename：wx_db.php
*   Creator：Mark Luo
*   Created Date：01/12/2017
*   Description：
*
*   Modified History：
*
================================================================*/
class WXDB
{
    static function connect($debug)
    {
        // 5.5.44-MariaDB can't connect with mysqli_connect()
        // Error: can't locate default socket
        // When I specifed socket location, Error: can't open this path  
        $mysql_conn=mysql_connect(HOST,DBUSER,DBPASSWD,DB);
        if (!$mysql_conn) {
            $debug->appendLog("[Error] failed to connect MYSQL:".mysql_error());
            return false;
        } else {
            mysql_set_charset('utf8',$mysql_conn);
            mysql_select_db('mark_wechat',$mysql_conn);
            $debug->appendLog("successed to connect MYSQL:".mysql_error());
            return $mysql_conn;
        }
    }
 
    static function execSQL($conn,$sql,$debug)
    {
        $result=mysql_query($sql,$conn);
        if (!$result) {
            $debug->appendLog("[Error] failed to excute SQL: $sql\n"
                .mysql_error());
            return false;
        } else {
            $debug->appendLog("successed to excute SQL: $sql");
            return true;
        }
    }

    static function close($conn,$debug)
    {
        mysql_close($conn);
        $debug->appendLog("disconnect from MYSQL");
    }
}


?>
