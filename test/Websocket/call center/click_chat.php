<?php
/*
点击选择聊天接口
场景：客服通过左侧游客列表消息通知栏，选择与其对应的游客聊天
作用：主要将客服和游客进行临时会话关联，该接口会将客服的fd加入到游客所在的fd集合中
*/

$uid = $_GET['uid'];
// 会员的fd
$u_fd = $_GET['u_fd'];	// 登录用户fd

// 游客的fd
$t_fd = $_GET['fd'];	// 临时会话fd

// 上一次会话fd
$b_fd = $_GET['b_fd'];

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$chat_fd = 'fd_'.$t_fd;	// 临时会话fd标识
	
$fd_num = $redis->scad($chat_fd);	// 获取集合个数
if(empty($fd_num)){
	echo "redis fd is empty";
}

if($fd_num > 1 && $b_fd) {
	// 清除上一次会话的fd
	$redis->srem($chat_fd, $b_fd);
}elseif($fd_num >= 2){
	// 当前还有会话关联（正常每次结束会话都要清除，需要写日志记录）,这里为了避免无法进行会话，直接删除重建
	$redis->del($chat_fd);
	$redis->sadd($chat_fd, $u_fd);
	// do log
}

// 将客服fd加入到游客会话集合中
$rs = $redis->sadd($chat_fd, $u_fd);
//$rs = $redis->smove('wait_fd',$chat_fd, $fd);
var_dump($rs);
return $chat_fd;	// 返回当前会话标识