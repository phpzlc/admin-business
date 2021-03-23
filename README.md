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