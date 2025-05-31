<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API接口文档</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .docs-container { max-width: 1000px; margin: 2rem auto; }
        .code-block { background: #f8f9fa; padding: 1.5rem; border-radius: 8px; }
        pre { margin: 0; }
        h2 { margin-top: 2rem; padding-bottom: 0.5rem; border-bottom: 2px solid #dee2e6; }
        .field-table th { width: 15%; }
        .field-table td:nth-child(2) { width: 10%; }
        .field-table td:nth-child(3) { width: 10%; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="admin.php">ZERO - 应用配置管理中心</a>
            <div class="d-flex">
                <a href="admin.php" class="btn btn-outline-light me-2">返回管理</a>
                <a href="logout.php" class="btn btn-outline-light">退出</a>
            </div>
        </div>
    </nav>

    <div class="docs-container container">
        <div class="card">
            <div class="card-header bg-white">
                <h1 class="h4 mb-0">API接口文档</h1>
            </div>
            <div class="card-body">
                <!-- 接口说明部分 -->
                <div class="mb-5">
                    <h2 class="h5">基础信息</h2>
                    <ul>
                        <li>接口地址：<code>/get_config.php</code></li>
                        <li>请求方式：GET</li>
                        <li>编码格式：UTF-8</li>
                        <li>响应格式：XML</li>
                    </ul>
                </div>

                <!-- 请求参数 -->
                <div class="mb-5">
                    <h2 class="h5">请求参数</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>参数名</th>
                                    <th>必填</th>
                                    <th>类型</th>
                                    <th>说明</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>app_key</code></td>
                                    <td>是</td>
                                    <td>String</td>
                                    <td>应用的唯一标识Key</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-5">
                    <h2 class="h5">响应字段说明</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered field-table">
                            <thead class="table-light">
                                <tr>
                                    <th>字段名称</th>
                                    <th>类型</th>
                                    <th>必填</th>
                                    <th>说明</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>&lt;announcement&gt;</code></td>
                                    <td>String</td>
                                    <td>是</td>
                                    <td>系统公告信息，用于展示给用户的重要通知（HTML转义内容，最大长度2000字符）</td>
                                </tr>
                                <tr>
                                    <td><code>&lt;update_content&gt;</code></td>
                                    <td>String</td>
                                    <td>是</td>
                                    <td>详细更新说明，支持多行文本（使用CDATA包裹，保留原始格式）</td>
                                </tr>
                                <tr>
                                    <td><code>&lt;version&gt;</code></td>
                                    <td>String</td>
                                    <td>是</td>
                                    <td>语义化版本号（格式：x.x.x），示例：2.1.3</td>
                                </tr>
                                <tr>
                                    <td><code>&lt;update_url&gt;</code></td>
                                    <td>URL</td>
                                    <td>是</td>
                                    <td>更新包下载地址（HTTPS协议，最大长度255字符）</td>
                                </tr>
                                <tr>
                                    <td><code>&lt;force_update&gt;</code></td>
                                    <td>Boolean</td>
                                    <td>是</td>
                                    <td>强制更新标识：<br>
                                        <code>true</code> - 必须立即更新<br>
                                        <code>false</code> - 可选更新</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- 调用示例 -->
                <div class="mb-5">
                    <h2 class="h5">调用示例</h2>
                    <!-- CURL示例 -->
                    <div class="mb-4">
                        <h3 class="h6">CURL</h3>
                        <div class="code-block">
                            <pre><code>curl "http://yourdomain.com/get_config.php?app_key=您的应用密钥"</code></pre>
                        </div>
                    </div>
                       <!-- C#示例 -->
                       <div class="mb-4">
                        <h3 class="h6">C#</h3>
                        <div class="code-block">
                            <pre><code>using System;
using System.Net.Http;
using System.Threading.Tasks;

class Program 
{
    static async Task Main(string[] args)
    {
        var client = new HttpClient();
        try 
        {
            var response = await client.GetAsync(
                "http://yourdomain.com/get_config.php?app_key=您的应用密钥");
            
            response.EnsureSuccessStatusCode();
            string responseBody = await response.Content.ReadAsStringAsync();
            Console.WriteLine(responseBody);
        }
        catch (HttpRequestException e)
        {
            Console.WriteLine($"请求错误: {e.Message}");
        }
    }
}</code></pre>
                        </div>
                    </div>
                    <!-- Python示例 -->
                    <div class="mb-4">
                        <h3 class="h6">Python</h3>
                        <div class="code-block">
                            <pre><code>import requests

response = requests.get(
    "http://yourdomain.com/get_config.php",
    params={"app_key": "您的应用密钥"}
)
print(response.text)</code></pre>
                        </div>
                    </div>
                   <!-- JavaScript示例 -->
                    <div class="mb-4">
                        <h3 class="h6">JavaScript</h3>
                        <div class="code-block">
                            <pre><code>fetch('http://yourdomain.com/get_config.php?app_key=您的应用密钥')
  .then(response => response.text())
  .then(data => console.log(data))</code></pre>
                        </div>
                    </div>
                </div>

<div class="mb-4">

                <!-- 响应示例 -->
                <div class="mb-4">
                    <h2 class="h5">响应示例</h2>
                    <div class="code-block">
                        <pre><code>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;software&gt;
    &lt;announcement&gt;本次更新包含重要安全补丁&lt;/announcement&gt;
    &lt;update_content&gt
    <![CDATA[
    1.xxx
    2.xxx
    ]]>
    &lt;/update_content&gt;
    &lt;version&gt;2.1.0&lt;/version&gt;
    &lt;update_url&gt;https://update.example.com/v2.1.0.zip&lt;/update_url&gt;
    &lt;force_update&gt;true&lt;/force_update&gt;
&lt;/software&gt;</code></pre>
                    </div>
                </div>

                <!-- 错误代码 -->
                <div class="mb-4">
                    <h2 class="h5">错误代码</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>状态码</th>
                                    <th>说明</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>404</td>
                                    <td>无效的应用密钥</td>
                                </tr>
                                <tr>
                                    <td>403</td>
                                    <td>访问被拒绝</td>
                                </tr>
                                <tr>
                                    <td>500</td>
                                    <td>服务器内部错误</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


