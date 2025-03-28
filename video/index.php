<?php
// a.php-index

// 视频文件所在目录
$videoDir = __DIR__ . '/video_date/';

// 获取目录下所有 MP4 文件
$mp4Files = glob($videoDir . '*.mp4');

// 如果没有 MP4 文件，退出
if (count($mp4Files) == 0) {
    die('没有找到视频文件');
}

// 随机选择一个视频文件
$randomVideo = $mp4Files[array_rand($mp4Files)];

// 获取文件名
$fileName = basename($randomVideo);

// 生成 Token 的函数
function generateToken($fileName) {
    global $secretKey;
    $timestamp = time(); // 当前时间戳
    $randomString = bin2hex(random_bytes(16)); // 生成随机字符串
    $ipAddress = $_SERVER['REMOTE_ADDR']; // 绑定 IP 地址
    return hash('sha256', $fileName . $secretKey . $timestamp . $randomString . $ipAddress) . ':' . $timestamp . ':' . $randomString;
}

// 你的秘密密钥（需要自己设置一个安全的密钥）
$secretKey = 'ximi_secret_key'; // 替换为你的实际密钥

// 生成 Token
$token = generateToken($fileName);

// 生成 URL
$url = "paly.php?video=" . urlencode($fileName) . "&token=" . urlencode($token);

// 使用 header() 实现跳转到播放页面
header("Location: $url");
exit();
?>
