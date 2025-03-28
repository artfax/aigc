<?php
// index.php-paly

session_start();

// 获取请求的文件名和 Token
$imgName = htmlspecialchars(isset($_GET['video']) ? $_GET['video'] : '') ;
$token = htmlspecialchars(isset($_GET['token']) ? $_GET['token'] : '') ;

// 打印调试信息，检查传递的参数
// 你可以将以下内容暂时开启调试
// echo 'file: ' . $imgName . '<br>';
// echo 'token: ' . $token . '<br>';

if (empty($imgName) || empty($token)) {
    die('Invalid file or token.');
}

// 你的秘密密钥
$secretKey = 'ximi_secret_key';

// 生成动态 Token 的函数（绑定 IP 地址）
function generateToken($fileName) {
    global $secretKey;
    $timestamp = time(); // 当前时间戳
    $randomString = bin2hex(random_bytes(16)); // 生成随机字符串
    $ipAddress = $_SERVER['REMOTE_ADDR']; // 获取用户的 IP 地址
    return hash('sha256', $fileName . $secretKey . $timestamp . $randomString . $ipAddress) . ':' . $timestamp . ':' . $randomString;
}

// 验证动态 Token 的函数
function validateToken($token, $fileName) {
    global $secretKey;
    list($hashedToken, $timestamp, $randomString) = explode(':', $token);
    
    // Token 的有效期（例如 30 分钟）
    $expiryTime = 10;  //30 * 60;  30分钟
    $currentTime = time();
    
    // 检查时间戳是否过期
    if ($currentTime - $timestamp > $expiryTime) {
        return false;
    }
    
    // 重新生成 Token 进行验证
    $ipAddress = $_SERVER['REMOTE_ADDR']; // 获取用户的 IP 地址
    $expectedToken = hash('sha256', $fileName . $secretKey . $timestamp . $randomString . $ipAddress);

    // 如果验证成功
    if ($hashedToken === $expectedToken) {
        return true;
    }
    return false;
}

// 验证 Token 是否有效
if (validateToken($token, $imgName)) {
    // 视频文件路径
    $videoDir = __DIR__ . '/video_date/';
    $videoPath = $videoDir . $imgName;

    // 检查文件是否存在
    if (file_exists($videoPath)) {
        // 获取 MIME 类型
        $mimeType = mime_content_type($videoPath);

        // 设置正确的 MIME 类型
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($videoPath) . '"');
        header('Content-Length: ' . filesize($videoPath));

        // 输出视频内容
        readfile($videoPath);
        exit;
    } else {
        // 如果文件不存在，显示404错误
        header('HTTP/1.0 404 Not Found');
        echo 'File not found.';
        exit;
    }
} else {
    // 如果 Token 无效，返回 403 错误
    header('HTTP/1.0 403 Forbidden');
    echo 'Access forbidden or invalid token';
    exit;
}
?>
