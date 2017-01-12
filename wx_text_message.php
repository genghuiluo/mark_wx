<?php
/*===============================================================
*   Copyright (C) 2017 All rights reserved.
*   
*   Filename：wx_text_message.php
*   Creator：Mark Luo
*   Created Date：01/12/2017
*   Description：
*
*   Modified History：
*
================================================================*/
class WXTextMessage extends WXMessage
{
    var $format="<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";

    function WXTextMessage($content,$from_wx_id,$to_wx_id)
    {
        $this->plaintext_xml=sprintf($this->format,$to_wx_id,$from_wx_id,time(),$content);
        $this->timestamp=time();
        $this->nonce=parent::genNonce();
    }

    function save($conn,$debug)
    {
        parent::save($conn,$debug,'text');
    }
}

?>

