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

> 文件夹权限

```shell
sudo chmod -R 777 public/upload/
```