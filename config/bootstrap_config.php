<?php
// Bootstrap CDN 配置文件
// 统一管理Bootstrap版本和CDN地址

class BootstrapConfig {
    // Bootstrap版本配置
    const VERSION = '5.3.3';
    const CDN_BASE = 'https://cdn.jsdelivr.net/npm/bootstrap@';
    
    // 获取CSS CDN链接
    public static function getCssLink() {
        return self::CDN_BASE . self::VERSION . '/dist/css/bootstrap.min.css';
    }
    
    // 获取JS CDN链接
    public static function getJsLink() {
        return self::CDN_BASE . self::VERSION . '/dist/js/bootstrap.bundle.min.js';
    }
    
    // 获取完整HTML引用代码
    public static function getCssHtml() {
        return '<link href="' . self::getCssLink() . '" rel="stylesheet">';
    }
    
    public static function getJsHtml() {
        return '<script src="' . self::getJsLink() . '"></script>';
    }
    
    // 检查CDN可用性
    public static function checkCdnAvailability() {
        $cssUrl = self::getCssLink();
        $headers = @get_headers($cssUrl);
        return $headers && strpos($headers[0], '200') !== false;
    }
}

// 如果直接访问此文件，显示配置信息
if (basename($_SERVER['SCRIPT_NAME']) === 'bootstrap_config.php') {
    header('Content-Type: application/json');
    echo json_encode([
        'version' => BootstrapConfig::VERSION,
        'css_link' => BootstrapConfig::getCssLink(),
        'js_link' => BootstrapConfig::getJsLink(),
        'cdn_available' => BootstrapConfig::checkCdnAvailability()
    ], JSON_PRETTY_PRINT);
}
?>