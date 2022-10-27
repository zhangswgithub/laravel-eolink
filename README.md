## 文档说明

> 该SDK是基于laravel框架开发

> 该SDK的作用：调用SDK方法直接把数据更新到eolink文档

### Laravel 应用：

1. 包安装方式：
* composer require redam/laravel-eolink
2. 发布配置文件

   > php artisan vendor:publish  --provider=Redam\Eolink\EolinkServiceProvider
   >
   > 发布之后在 config/eolink.php 中配置

3. 在 config/database.php 中配置数据库驱动，然后再 env中配置数据库

   
4. 使用

   ```
    use Redam\Eolink\Eolink;
   
    $eolink = new Eolink();  
   
    // 请求参数和响应数据已经格式化
    $eolink->addApiInterface('创建者', '文档名称', 'url地址', '请求方式' , '分组名称', '项目名称', '请求参数', '响应');    
    // 请求参数和响应数据没有格式化
    $eolink->addNotFormatApiInterface('创建者', '文档名称', 'url地址', '请求方式' , '分组名称', '项目名称', '请求参数', '响应');
    // 添加项目
    $eolink->addProject('创建者','项目名称','空间名称','项目描述');
    // 添加分组
    $eolink->addGroup('分组名称', '项目名称', '上级分组名称', '空间名称');
   ```

5. 格式化数据示例

   ```
   // 请求参数
   $requestData = [
    [
        "key"=> "user_name",
        "must"=> "是",
        "desc"=> "",
        "value"=> "chegr"
    ],
    [
        "key"=> "security_one",
        "must"=> "是",
        "desc"=> "",
        "value"=> "1"
    ]
   ];
   
   // 响应
   $responseData = [
    [
        "key"=> "code",
        "desc"=> "",
        "value"=> "200"
    ],
    [
        "key"=> "data",
        "desc"=> "",
        "value"=> ""
    ],
    [
        "key"=> "message",
        "desc"=> "",
        "value"=> "success"
    ]
   ];
   ```
