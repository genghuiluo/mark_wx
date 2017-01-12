<?php
/*===============================================================
*   Copyright (C) 2017 All rights reserved.
*   
*   Filename：wx_message.php
*   Creator：Mark Luo
*   Created Date：01/11/2017
*   Description： 
*   Modified History：
*
================================================================*/

class WXMessage
{
    var $ciphertext_xml; 
    var $plaintext_xml;
    var $msg_sign;
    var $timestamp;
    var $nonce;

    function WXMessage($raw_xml,$msg_sign=false,$timestamp=false,$nonce=false)
    {
        if (!$msg_sign || !$timestamp || !$nonce)
        {
            $this->plaintext_xml=$raw_xml;
        } else {
            $this->ciphertext_xml=$raw_xml;
            $this->msg_sign=$msg_sign;
            $this->timestamp=$timestamp;
            $this->nonce=$nonce;
        }
    }

    function decrypt($encrypt_wiz,$debug)
    {
        if (!$this->msg_sign || !$this->timestamp || !$this->nonce || !$this->ciphertext_xml)
        {
            return false;
        } else {
            $err_code=$encrypt_wiz->decryptMsg($this->msg_sign,$this->timestamp,$this->nonce,$this->ciphertext_xml,$this->plaintext_xml);
            if ($err_code == 0) {
                $debug->appendLog("decrypted plaintext xml:\n$this->plaintext_xml");
                return true;
            } else {
                $debug->appendLog("[Error] decryption failed, error code is $err_code");
                return false;
            }
        }
    }

    function encrypt($encrypt_wiz,$debug)
    {
        if (!$this->timestamp || !$this->nonce || !$this->plaintext_xml)
        {
            return false;
        } else {
            $err_code=$encrypt_wiz->encryptMsg($this->plaintext_xml,$this->timestamp,$this->nonce,$this->ciphertext_xml);
            if ($err_code ==0) {
                $debug->appendLog("encrypted ciphertext xml:\n$this->ciphertext_xml");
                return true;
            } else {
                $debug->appendLog("[Error] encryption failed, error code is:\n$err_code");
                return false;
            }
        }
    }

    function genNonce()
    {
        return rand(100000000,999999999);
    }

    function getPorperty($key)
    {
        if ($this->plaintext_xml)
        {
            $xml=simplexml_load_string($this->plaintext_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xml);
            $arr = json_decode($json,TRUE);
            return array_key_exists($key,$arr)? $arr[$key] : false;
        } else {
            return false;
        }
    }

    function save($conn,$debug,$msg_type=false)
    {
        if (!$msg_type) {
            $msg_type=$this->getPorperty('MsgType');
        }

        switch($msg_type) {
        case 'text':
            $msg_content=$this->getPorperty('Content');
            break;
        default:
            return false;
        }

        $ins_sql=sprintf("INSERT INTO wx_message(from_wx_id,to_wx_id,msg_type,msg_content,msg_content_md5,create_dt) VALUES ('%s','%s','text','%s',md5('%s'),now());",$this->getPorperty('FromUserName'),$this->getPorperty('ToUserName'),addslashes($msg_content),addslashes($msg_content));
        WXDB::execSQL($conn,$ins_sql,$debug);
    }
}

include_once "wx_text_message.php";
?>
