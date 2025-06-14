## ZERO - 应用配置管理中心

当前版本：V1.0.0

#### 系统简介：

ZERO - 应用配置管理中心是一款基于PHP+MySQL开发的应用配置信息云管理系统，功能简单易上手，界面简约清晰，易于部署，甚至只需要一台虚拟主机，就可以直接部署使用。


更多其他信息请前往博客查看：[ZERO - 应用配置管理中心](https://yizero.top/index.php/2025/05/31/zero-app-configuration-management-center/)

#### 系统截图：
##### 管理界面：
![Snipaste_2025-05-31_10-32-04](https://github.com/user-attachments/assets/551f7010-ed5b-45e2-9278-f301e5ef98af)

##### 配置信息界面：
![Snipaste_2025-05-31_10-57-11](https://github.com/user-attachments/assets/fb0d95e5-a338-48f4-b42d-612bebfe7746)


#### 部署要求：

- PHP环境：7.4+

- MySQL版本：5.7+

#### 响应示例：

注：当前仅支持返回XML格式的数据，暂不支持JSON格式，后续可能会支持返回该格式。详细的接口文档在部署后可在管理页面点击 “接口文档” 按钮后查看。

```
<?xml version="1.0" encoding="UTF-8"?>
<software>
    <announcement>这是一个公告</announcement>
    <update_content>
     1.XXX
     2.XXX
    </update_content>
    <version>2.1.0</version>
    <update_url>https://update.example.com/v2.1.0.zip</update_url>
    <force_update>true</force_update>
</software>
```

#### 项目结构：

- about.php：关于页面

- admin.php：管理页面

- api_docs.php：接口文档页面

- create_app.php：应用创建页面

- db_config.php：数据库配置文件

- delete_app.php：删除处理脚本

- edit_config.php：配置编辑页面

- forgot_password.php：密码重置请求页面

- get_config.php：API接口文件

- login.php：登录页面

- logout.php：退出文件

- register.php：注册页面

- reset_password.php：密码修改页面

- save_config.php：配置保存文件

- software_date.sql：首次部署时用于恢复数据库

#### 部署说明：

- 1.首次使用需自行创建一个默认数据库，在将目录下的 "software_date" 进行备份恢复，然后在去 "db_config.php"" 里面将数据库的连接信息更改为你对应的数据库信息。

- 2.首次使用请将目录下的 "register.php.stop" 文件的 ".stop" 后缀删除后，在通过 "https://你的域名/register.php" 进行管理账号注册，注册完成后建议将该文件删除，防止被恶意注册。

- 3.由于当前不支持用户区分管理，所以不管注册多少个账号都是管理同一个应用池，所以只需要注册一个账号即可。
