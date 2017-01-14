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
    // decrypted msg
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
$msg_type=trim($msg->getPorperty('MsgType'));

// your response rules
switch($msg_type) {
case 'text':
    $content=trim($msg->getPorperty('Content'));
    $tuling_user_id=preg_replace('/[^A-Za-z0-9]/','',$subscriber_wx_id);
    $reply=TulingBot::reply($content,$tuling_user_id,$debug);
    $reply_msg=new WXTextMessage($reply,$my_wx_id,$subscriber_wx_id);
    break;
case 'event':
    $content=$msg->getPorperty('Event');
    switch($content) {
    case 'subscribe':
        $reply="欢迎关注我的公众号/:heart,这个公众号主要被我用于学习NLP(自然语言处理),内置了一个图灵AI可以进行对话,开始调戏它吧/:B-)";
        $reply_msg=new WXTextMessage($reply,$my_wx_id,$subscriber_wx_id);
    case 'unsubscribe':
        break; 
    default:
        break;
    }
    break;
default:
    $content="unkown";
    $reply="我还没想好怎么处理这个/:P-(";
    $reply_msg=new WXTextMessage($reply,$my_wx_id,$subscriber_wx_id);
}

if ($reply_msg) {
    if (ENCRYPTMODE == 'safe') {
        // send encrypted msg
        $reply_msg->encrypt($encrypt_wiz,$debug);
        echo $reply_msg->ciphertext_xml; 
    } else {
        echo $reply_msg->plaintext_xml;
    }
}

$conn=WXDB::connect($debug);
if ($conn) {
    $msg->save($conn,$debug,$content);
    if ($reply_msg)
        $reply_msg->save($conn,$debug,$reply);
    WXDB::close($conn,$debug);
}

if (DEBUGMODE) {
    $debug->saveLog();
}

?>
