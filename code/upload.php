<?php
    //使用七牛进行文件上传
    include_once 'autoload.php';
    include_once 'config1.php';
    include_once 'class.PDO.php';
   header('content-type:text/html;charset=utf-8');
     //鉴权类
    use Qiniu\Auth;
    //文件上传类
    use Qiniu\Storage\UploadManager;
   use Qiniu\Config;
    // 用于签名的公钥和私钥
    $accessKey = AK;
    $secretKey = SK;
    // 初始化签权对象
    $auth = new Auth($accessKey, $secretKey);
  // var_dump($auth);exit;
    //定义要上传的空间
   $bucket = '';
    // 生成上传Token
    $token = $auth->uploadToken($bucket);
    // 要上传文件的本地路径
        //上传文件
        $files=$_FILES['img'];
       if($files['error']!=0 || !is_array($files)){
            die('上传文件失败');
       }
      $filePath =$files['tmp_name'];
// 上传到七牛后保存的文件名,不能重复,可以不用原有的后缀名
    $qiniu_name=time().uniqid().rand(100000,999999);

    // 初始化 UploadManager 对象并进行文件的上传
    $uploadMgr = new UploadManager();
    // 调用 UploadManager 的 putFile 方法进行文件的上传
     $arr= $uploadMgr->putFile($token, $qiniu_name, $filePath);
     //var_dump($arr);exit;
    list($ret, $err) = $uploadMgr->putFile($token, $qiniu_name, $filePath);
    if ($err !== null) {
         //打印错误信息
       // var_dump($err);
    } else {
         //打印成功后的结构
        var_dump($ret);die;
        //  //上传到七牛成功后的文件名
        // $key=$ret['key'];
        // $data=['img'=>$key];
        // $pdo=new PDO_MYSQL();
        // if($pdo->db_insert($data,'image')){
        //     echo '上传成功';
        // }
    }



