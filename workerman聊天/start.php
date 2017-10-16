<?php
/**
 * Created by PhpStorm.
 * User: raid
 * Date: 2016/8/2
 * Time: 11:03
 */
use Workerman\Worker;

require_once './Autoloader.php';

$global_uid = 2;

// 当客户端连上来时分配uid，并保存连接，并通知所有客户端  
function handle_connection($connection)
{
    global $text_worker, $global_uid;
    // 为这个链接分配一个uid  
    $connection->uid = ++$global_uid;
    foreach ($text_worker->connections as $conn) {
        $conn->send(json_encode(['newsType' => 'online', 'data' => $connection->uid . '上线了']));
    }
}

// 当客户端发送消息过来时，转发给所有人  
function handle_message($connection, $data)
{
    global $text_worker;
    foreach ($text_worker->connections as $conn) {
        $conn->send(json_encode(['newsType' => 'news', 'userId' => $connection->uid, 'data' => $data]));
    }
}

// 当客户端断开时，广播给所有客户端  
function handle_close($connection)
{
    global $text_worker;
    foreach ($text_worker->connections as $conn) {
        $conn->send(json_encode(['newsType' => 'offLine', 'data' => $connection->uid . '下线了']));
    }
}


$text_worker = new Worker("websocket://127.0.0.1:1234");

$text_worker->count = 1;

$text_worker->onConnect = 'handle_connection';
$text_worker->onMessage = 'handle_message';
$text_worker->onClose = 'handle_close';

Worker::runAll();  