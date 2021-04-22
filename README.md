## 安装

```shell
composer require phpzlc/admin-business 
```

## 配置

在项目根路由中`config/routes.yaml`引入

```yaml
admin:
  resource: "routing/admin/admin.yaml"
  prefix:   /admin

upload:
  resource: "routing/upload/upload.yaml"
  prefix:   /upload

captcha:
  resource: "routing/captcha/captcha.yaml"
  prefix:   /captcha
```

## README.md 补充

> php.ini

```apacheconfig
upload_max_filesize = 1024M
post_max_size = 1024M
```

> nginx

```apacheconfig
client_max_body_size     1024M;
proxy_connect_timeout    9000s;
proxy_read_timeout       9000s;
proxy_send_timeout       9000s;
```

> 文件夹权限

```shell
sudo chmod -R 777 public/upload/
```

## 代码配置

> 平台注册

文件位置: `config/packages/phpzlc-platform-business.yaml`

```yaml
  # 平台 - 后台
  platform_admin: admin

  # 全部平台
  platform_array:
    '%platform_admin%': 后台
```


> 操作主体注册

文件位置: `config/packages/phpzlc-auth-business.yaml`

```yaml
  # 操作主体- 管理员
  subject_admin: admin

  # 全部操作主体
  subject_array:
    '%subject_admin%': 管理员
```

> 登录标记代码注入 

文件位置: `src/Business/AuthBusiness/AuthTag.php`

```php
    /**
     * 设置Session标记
     * 
     * @param ContainerInterface $container
     * @param UserAuth $userAuth
     * @return string
     * @throws Exception
     */
    public static function set(ContainerInterface $container, UserAuth $userAuth)
    {
        $tag = '';

        switch (PlatformClass::getPlatform()){
            case $container->get('parameter_bag')->get('platform_admin'):
                $container->get('session')->set(PlatformClass::getPlatform() . $container->get('parameter_bag')->get('login_tag_session_name'), $userAuth->getId());
                break;
            default:
                throw new \Exception('来源溢出');
        }

        return $tag;
    }

    /**
     * 获取Session标记内容
     * 
     * @param ContainerInterface $container
     * @return UserAuth|false|object
     * @throws Exception
     */
    public static function get(ContainerInterface $container)
    {
        $userAuth = null;

        /**
         * @var ManagerRegistry $doctrine
         */
        $doctrine = $container->get('doctrine');

        switch (PlatformClass::getPlatform()){
            case $container->get('parameter_bag')->get('platform_admin'):
                $user_auth_id = $container->get('session')->get(PlatformClass::getPlatform() . $container->get('parameter_bag')->get('login_tag_session_name'));
                $userAuth = $doctrine->getRepository('App:UserAuth')->find($user_auth_id);
                break;
            default:
                throw new \Exception('来源溢出');
        }

        return $userAuth;
    }

    /**
     * 移除Session标记
     * 
     * @param ContainerInterface $container
     * @throws Exception
     */
    public static function remove(ContainerInterface $container)
    {
        switch (PlatformClass::getPlatform()){
            case $container->get('parameter_bag')->get('platform_admin'):
                $container->get('session')->remove(PlatformClass::getPlatform() . $container->get('parameter_bag')->get('login_tag_session_name'));
                break;
            default:
                throw new \Exception('来源溢出');
        }
    }
```

> 登录类引入

文件位置: `src/Business/AuthBusiness/UserAuthBusiness.php`

```php
    /**
     * 获取指定平台端方法
     *
     * @param $subject_type
     * @return AdminAuth|mixed
     * @throws Exception
     */
    private function getUserAuthService($subject_type)
    {
        if(!array_key_exists($subject_type, $this->subjectAuthCaches)){
            switch ($subject_type){
                case $this->getParameter('subject_admin'):
                    $this->subjectAuthCaches[$subject_type] = new AdminAuth($this->container);
                    break;                   
                default:
                    throw new \Exception('授权登录权限不存在');
            }
        }
        
        return $this->subjectAuthCaches[$subject_type];
    }
```
