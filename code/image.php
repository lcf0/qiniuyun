<?php
/**
 * Created by PhpStorm.
 * User: hasee
 * Date: 2017/6/19
 * Time: 16:00
 */

    require 'autoload.php';
    require_once 'config1.php';
    include_once  'class.PDO.php';
    // 引入鉴权类
    use Qiniu\Auth;
    // 需要填写你的 Access Key 和 Secret Key
    $accessKey = AK;
    $secretKey = SK;
    // 构建鉴权对象
    $auth = new Auth($accessKey, $secretKey);
    //baseUrl构造成私有空间的域名/key的形式   key是名字
    $domain="";
    $pdo=new PDO_MYSQL();
    $data=$pdo->db_select('image');
   foreach($data as $key=>$v){
       $baseUrl = "http://".$domain."/".$v['img'].'_thumbimage';
       $urls[$key]['authUrl'] = $auth->privateDownloadUrl($baseUrl);
   }
    include_once 'image.html';






  include_once 'image.html';