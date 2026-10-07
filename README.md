# php-test

用于验证本地 PHP、Laravel 和 PostgreSQL 环境的项目。欢迎页会从 PostgreSQL 的 `welcome_messages` 表读取并显示一条数据。

## 当前环境

- PHP 8.5
- Composer 2.10
- Laravel 13
- PostgreSQL 18
- 数据库：`php_test`
- 数据库用户：`php_test`
- 本地地址：<http://127.0.0.1:8000>

## 首次安装环境

```bash
brew install php composer postgresql@18
brew services start postgresql@18
```

确认环境：

```bash
php -v
composer --version
/opt/homebrew/opt/postgresql@18/bin/psql --version
brew services list
```

## 首次创建数据库

以下命令只需执行一次：

```bash
/opt/homebrew/opt/postgresql@18/bin/createuser --login php_test
/opt/homebrew/opt/postgresql@18/bin/createdb --owner=php_test php_test
```

当前 Homebrew PostgreSQL 仅用于本机开发，本地连接采用可信认证，因此 `.env` 中的 `DB_PASSWORD` 为空。

## 首次初始化项目

```bash
cd /Users/wenbiaoli/develop/php-test
composer install
```

如果 `.env` 不存在：

```bash
cp .env.example .env
php artisan key:generate
```

创建数据库表并写入欢迎页数据：

```bash
php artisan migrate --seed
```

## 日常启动

先确保 PostgreSQL 已启动：

```bash
brew services start postgresql@18
```

启动 Laravel：

```bash
cd /Users/wenbiaoli/develop/php-test
php artisan serve --host=127.0.0.1 --port=8000
```

浏览器访问：

<http://127.0.0.1:8000>

页面显示 `Hello from PostgreSQL!` 表示 Laravel 已成功读取数据库。

在启动 Laravel 的终端中按 `Control + C` 可以停止开发服务器。PostgreSQL 后台服务可以使用以下命令停止：

```bash
brew services stop postgresql@18
```

## 验证数据库

直接查询欢迎页数据：

```bash
/opt/homebrew/opt/postgresql@18/bin/psql \
  -h 127.0.0.1 \
  -U php_test \
  -d php_test \
  -c "SELECT id, content FROM welcome_messages;"
```

检查 Laravel 迁移状态：

```bash
php artisan migrate:status
```

重新写入种子数据：

```bash
php artisan db:seed
```

## 测试和排查

```bash
php artisan test
php artisan route:list
php artisan optimize:clear
lsof -nP -iTCP:8000 -sTCP:LISTEN
```

如果 8000 端口已被占用：

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

PHP 内置开发服务器仅用于本地开发，不应直接用于生产环境。
