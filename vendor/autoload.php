<?php

// 简单的自动加载器
function autoload($class)
{
    $class = str_replace('\\', '/', $class);
    $class = preg_replace('/^app\//', '', $class);
    $file = __DIR__ . '/../application/' . $class . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
}

spl_autoload_register('autoload');

// 模拟ThinkPHP的App类
class App
{
    public function http()
    {
        return new Http();
    }
}

// 模拟ThinkPHP的Http类
class Http
{
    public function run()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($path, PHP_URL_PATH);
        $path = trim($path, '/');
        
        $routeConfig = require __DIR__ . '/../application/config/route.php';
        $rules = $routeConfig['rule'] ?? [];
        
        $controller = 'Index';
        $action = 'index';
        $params = [];
        
        if (isset($rules[$path])) {
            $routeParts = explode('/', $rules[$path]);
            $controller = $routeParts[0];
            // 将下划线命名转换为驼峰命名
            $controller = str_replace(' ', '', ucwords(str_replace('_', ' ', $controller)));
            $action = $routeParts[1] ?? 'index';
        } elseif ($path === '' && isset($rules['/'])) {
            $routeParts = explode('/', $rules['/']);
            $controller = $routeParts[0];
            // 将下划线命名转换为驼峰命名
            $controller = str_replace(' ', '', ucwords(str_replace('_', ' ', $controller)));
            $action = $routeParts[1] ?? 'index';
        } else {
            foreach ($rules as $pattern => $route) {
                if (strpos($pattern, ':') !== false) {
                    $patternRegex = '#^' . preg_replace('/:\w+/', '([^/]+)', $pattern) . '$#';
                    if (preg_match($patternRegex, $path, $matches)) {
                        $routeParts = explode('/', $route);
                        $controller = $routeParts[0];
                        // 将下划线命名转换为驼峰命名
                        $controller = str_replace(' ', '', ucwords(str_replace('_', ' ', $controller)));
                        $action = $routeParts[1] ?? 'index';
                        array_shift($matches);
                        $params = $matches;
                        break;
                    }
                }
            }
        }
        
        $controllerClass = "app\\controller\\$controller";
        if (class_exists($controllerClass)) {
            $instance = new $controllerClass();
            if (method_exists($instance, $action)) {
                $result = $instance->$action(...$params);
                return new Response($result);
            } else {
                return new Response('Method not found: ' . $action);
            }
        } else {
            return new Response('Class not found: ' . $controllerClass);
        }
        
        return new Response('404 Not Found');
    }
    
    public function end($response)
    {
    }
}

// 模拟ThinkPHP的Response类
class Response
{
    protected $content;
    
    public function __construct($content)
    {
        $this->content = $content;
    }
    
    public function send()
    {
        if (is_string($this->content)) {
            echo $this->content;
        } elseif (is_array($this->content)) {
            echo json_encode($this->content);
        }
    }
}

// 模拟ThinkPHP的view函数
function view($template, $data = [])
{
    $templateFile = __DIR__ . '/../application/view/' . str_replace('.', '/', $template) . '.html';
    
    if (file_exists($templateFile)) {
        extract($data);
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }
    
    return 'Template not found: ' . $template;
}

// 不再使用session，改为使用cookie存储用户信息

// 加载公共函数
require __DIR__ . '/../application/common.php';