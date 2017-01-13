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
    public $ciphertext_xml; 
    public $plaintext_xml;
    protected $plaintext_arr;
    protected $msg_sign;
    protected $timestamp;
    protected $nonce;

    function WXMessage($raw_xml,$msg_sign=false,$timestamp=false,$nonce=false)
    {
        if (!$msg_sign || !$timestamp || !$nonce) {
            $this->plaintext_xml=$raw_xml;
            $this->set_plaintext_arr();
        } else {
            $this->ciphertext_xml=$raw_xml;
            $this->msg_sign=$msg_sign;
            $this->timestamp=$timestamp;
            $this->nonce=$nonce;
        }
    }

    public function decrypt($encrypt_wiz,$debug)
    {
        if (!$this->msg_sign || !$this->timestamp || !$this->nonce || !$this->ciphertext_xml) {
            return false;
        } else {
            $err_code=$encrypt_wiz->decryptMsg($this->msg_sign,$this->timestamp,$this->nonce,$this->ciphertext_xml,$this->plaintext_xml);
            if ($err_code == 0) {
                $this->set_plaintext_arr();
                $debug->appendLog("decrypted plaintext xml:\n$this->plaintext_xml");
                return true;
            } else {
                $debug->appendLog("[Error] decryption failed, error code is $err_code");
                return false;
            }
        }
    }

    public function encrypt($encrypt_wiz,$debug)
    {
        if (!$this->timestamp || !$this->nonce || !$this->plaintext_xml) {
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

    public function getPorperty($porperty_name)
    {
        if ($this->plaintext_arr) {
            return array_key_exists($porperty_name,$this->plaintext_arr) ? $this->plaintext_arr[$porperty_name] : false;
        } else {
            return false;
        }
    }

    protected function genNonce()
    {
        return rand(100000000,999999999);
    }

    protected function set_plaintext_arr()
    {
        $tmp_xml=simplexml_load_string($this->plaintext_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $tmp_json=json_encode($tmp_xml);
        $this->plaintext_arr=json_decode($tmp_json,TRUE);
    }

    public function save($conn,$debug,$msg_content)
    {
        if ($this->plaintext_arr) { 
            $ins_sql=sprintf("INSERT INTO wx_message(from_wx_id,to_wx_id,msg_type,msg_content,msg_content_md5,create_dt) VALUES ('%s','%s','%s','%s',md5('%s'),now());",$this->plaintext_arr['FromUserName'],$this->plaintext_arr['ToUserName'],$this->plaintext_arr['MsgType'],addslashes($msg_content),addslashes($msg_content));
            WXDB::execSQL($conn,$ins_sql,$debug);
        } else {
            return false;
        }
    }
}

include_once "wx_text_message.php";
?>
