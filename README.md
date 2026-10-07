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

如果 `.env` 中的 `APP_KEY` 仍为空，运行以下命令生成密钥：

```bash
php artisan key:generate
```

如果修改 `.env` 后应用仍使用旧配置，可以运行 `php artisan config:clear`。

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

## Windows 11 首次构建与启动（CMD）

下面的命令在 **Windows 命令提示符（CMD）** 中执行。以复制到 `C:\Users\Acer\Desktop\10月新现场\Project-laravel\_demo\php-laravel-demo` 的项目为例；如果实际目录不同，请替换路径。首次准备需要安装 PHP（满足 `composer.json` 中的 `^8.3` 要求）、Composer 和 PostgreSQL，并确保命令在新打开的 CMD 中可用：

```bat
php -v
composer --version
psql --version
```

如果 `psql` 无法识别，可以使用 PostgreSQL 安装包自带的 **SQL Shell (psql)**，或者把其 `bin` 目录加入 Windows 的 `PATH`。如果 `composer` 无法识别，请先安装 [Composer for Windows](https://getcomposer.org/download/)，安装时选择正在使用的 `php.exe`，然后重新打开 CMD。

进入项目并安装 PHP 依赖：

```bat
cd /d "C:\Users\Acer\Desktop\10月新现场\Project-laravel\_demo\php-laravel-demo"
composer install
dir vendor\autoload.php
```

如果运行 `php artisan serve` 时提示找不到 `vendor/autoload.php`，说明尚未成功安装项目依赖。执行 `composer install`，不要修改 `artisan`。如果安装时提示缺少扩展，用 `php --ini` 找到 CLI 使用的 `php.ini`，再用 `php -m` 检查扩展；按报错启用缺少的扩展，并确认至少有 `pdo_pgsql`（连接 PostgreSQL 所需）。更改 `php.ini` 后重新打开 CMD，再运行 `composer install`。

创建本机配置。**仅当 `.env` 不存在时**执行 `copy`；已有 `.env` 时只修改其中的数据库设置，避免覆盖已有配置：

```bat
copy .env.example .env
php artisan key:generate
```

安装并启动 PostgreSQL 后，在 CMD 中连接管理员数据库：

```bat
psql -h 127.0.0.1 -U postgres -d postgres
```

输入安装 PostgreSQL 时设置的管理员密码，然后在 `psql` 中执行以下一次性 SQL。请把示例密码换成仅供本机使用的密码：

```sql
CREATE ROLE php_test WITH LOGIN PASSWORD 'replace_with_a_local_password';
CREATE DATABASE php_test OWNER php_test;
\q
```

在 `.env` 中设置 Windows 本机数据库连接，`DB_PASSWORD` 填入上一步实际使用的密码：

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=php_test
DB_USERNAME=php_test
DB_PASSWORD=replace_with_a_local_password
```

`.env.example` 中的空密码是当前 macOS 本机环境的配置示例，Windows PostgreSQL 通常需要填写密码。不要提交包含密码的 `.env`。

清除旧配置，创建表并写入欢迎页示例数据，然后启动：

```bat
php artisan config:clear
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8001
```

打开 <http://127.0.0.1:8001>。看到 `Hello from PostgreSQL!` 表示 Laravel 已成功读取数据库。在启动服务器的 CMD 中按 `Ctrl+C` 停止。再次启动时，只需确认 PostgreSQL 服务已运行，然后在项目目录执行 `php artisan serve --host=127.0.0.1 --port=8001`。

可选的检查命令：

```bat
php artisan migrate:status
php artisan test
psql -h 127.0.0.1 -U php_test -d php_test -c "SELECT id, content FROM welcome_messages;"
```
