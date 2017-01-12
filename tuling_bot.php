<?php
/*===============================================================
*   Copyright (C) 2017 All rights reserved.
*   
*   Filename：tuling_bot.php
*   Creator：Mark Luo
*   Created Date：01/11/2017
*   Description：call tuling123 open api
*   Modified History：
*
================================================================*/

function tulingBot($input,$tuling_userid)
{
    $data = array('key' => TULINGBOT_KEY, 'info' => $input,'userid' => $tuling_userid);

    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result_json = file_get_contents(TULINGBOT_URL, false, $context);

    $result_arr=json_decode($result_json,true);
    if ($result_arr['code'] != 100000) {
        return '让我一个人静静 T_T...'; 
    } else {
        return $result_arr['text'];
    }
}

?>
