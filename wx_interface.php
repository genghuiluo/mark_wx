<?php
/*===============================================================
*   Copyright (C) 2017 All rights reserved.
*   
*   Filename：wx_interface.php
*   Creator：Mark Luo
*   Created Date：01/10/2017
*   Description：server url's target file which exchange XML data with Wechat platform
*   Modified History：
*
================================================================*/

// uncomment this only when you try to verify server url at first time
//echo $_GET['echostr'];
//exit;

require_once "wx_conf.php";
include_once "wx_debug_log.php";
include_once "wx_db.php";
include_once "tuling_bot.php";
include_once "wx_message.php";
include_once "./wxEncrytLegacy/wxBizMsgCrypt.php";

date_default_timezone_set('Asia/Shanghai');

$raw_post_data=file_get_contents('php://input') or die("<h1>illegal request!</h1>");

$debug=new WXDebugLog();

// received raw data   
$debug->appendLog("received raw data:\n"
        ."\$_GET:\n"
        .var_export($_GET,true)."\n"
        ."\$HTTP_RAW_POST_DATA:\n"
        // $HTTP_RAW_POST_DATA is deprecated in PHP 5.6.0
        .$raw_post_data);

if (ENCRYPTMODE == 'safe') {
    $msg=new WXMessage($raw_post_data,$_GET['msg_signature'],$_GET['timestamp'],$_GET['nonce']);
    $encrypt_wiz=new WXBizMsgCrypt(TOKEN,ENCODINGAESKEY,APPID);
    if(!$msg->decrypt($encrypt_wiz,$debug))
    {
        die("<h1>illegal request!</h1>");
    }
} else {
    $msg=new WXMessage($raw_post_data);
}

$subscriber_wx_id=$msg->getPorperty('FromUserName'); 
$my_wx_id=$msg->getPorperty('ToUserName');
$content=trim($msg->getPorperty('Content'));

$tuling_user_id=preg_replace('/[^A-Za-z0-9]/','',$subscriber_wx_id);
$reply=tulingBot($content,$tuling_user_id);

// send encrypted msg
$reply_msg=new WXTextMessage($reply,$my_wx_id,$subscriber_wx_id);
    
if (ENCRYPTMODE == 'safe') {
    $reply_msg->encrypt($encrypt_wiz,$debug);
    echo $reply_msg->ciphertext_xml; 
} else {
    echo $reply_msg->plaintext_xml;
}

$conn=WXDB::connect($debug);
if ($conn) {
    $msg->save($conn,$debug,$content);
    $reply_msg->save($conn,$debug,$reply);
    WXDB::close($conn,$debug);
}

if (DEBUGMODE) {
    $debug->saveLog();
}

?>
