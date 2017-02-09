# mark的订阅号dev

- Basic framework of a wechat third party service, since lots of limit for non-org subscriber 
- The MIT License (MIT) Copyright (c) 2017 Genghui Luo 
- dependencies:
  - php 5.4
  - mysql 15.1 / MariaDB 5.5

### scan QR code to follow

![](./mark_wx.qrcode)


### set up your own subscription

1. login https://mp.weixin.qq.com/ to apply a subscription
2. enable 'developer' option
3. set up on your box

  ```
  # install a web server, e.g. apache/nginx
  > cd your_web_root_dir
  > git@github.com:genghuiluo/mark_wx.git
  > cd mark_wx
  > cp wx_conf.sample.php wx_conf.php
  > vi wx_conf.php
  ```
4. configure wx_conf.php with infomation on mp.weixin.qq.com
5. uncomment two lines below in wx_interface.php
  
  ```
  // uncomment this only when you try to verify server url at first time
  echo $_GET['echostr'];
  exit;
  ``` 
6. set the server url as *https://\<your_box_ip\>/mark_wx/wx_interface.php*, your can enable your server on mp.weixin.qq.com successfully.
7. re-comment two lines above in wx_interface.php
8. create corresponding database & table in mysql, update in wx_conf.php

  ```
  create table wx_message(
    msg_seq_id int auto_increment, 
    from_wx_id varchar(30), 
    to_wx_id varchar(30), 
    msg_type varchar(10), 
    msg_content varchar(300) character set utf8,
    msg_content_md5 char(32), 
    create_dt datetime, 
  primary key(msg_seq_id));
  ```
