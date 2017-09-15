<?php
$server        = new swoole_websocket_server("0.0.0.0", 9501);
$server->users = [];


function onlineNum($server){
    $x = count($server->users);
    foreach ($server->users as $v) {
        $server->push($v, '当前在线人数：'.$x);
    }
}


$server->on('open', function (swoole_websocket_server $server, $request) {
    $server->users[] = $request->fd;
    echo "server: handshake success with fd{$request->fd}\n";
    onlineNum($server);
});

$server->on('message', function (swoole_websocket_server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    foreach ($server->users as $v) {
        $server->push($v, $frame->data);
    }

});

$server->on('close', function ($server, $fd) {
    unset($server->users[$fd - 1]);
    echo "client {$fd} closed\n";
    onlineNum($server);
});

$server->start();