# php-test

使用 Laravel、Inertia、Vue、TypeScript 和 PostgreSQL 的本地示例。首页有四项菜单，分别连接 Home、About、Services、Contact Vue 页面；Home 和 About 显示 `welcome_messages` 表中对应的欢迎词。

## 当前环境

- PHP 8.5
- Composer 2.10
- Node.js、npm
- Laravel 13
- Inertia 3、Vue 3、TypeScript
- PostgreSQL 18
- 数据库：`php_test`
- 数据库用户：`php_test`
- 本地地址：<http://127.0.0.1:8000>

## 首次安装环境

```bash
brew install php composer node postgresql@18
brew services start postgresql@18
```

确认环境：

```bash
php -v
composer --version
node --version
npm --version
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
npm ci
npm run build
```

如果 `.env` 不存在：

```bash
cp .env.example .env
php artisan key:generate
```

创建数据库表并写入 Home、About 两条欢迎数据：

```bash
php artisan migrate --seed
```

数据库准备好并完成 `.env` 配置后，也可以运行 `composer run setup` 一次完成依赖安装、迁移、种子数据和前端构建。重复运行不会替换已有的 `APP_KEY`，欢迎数据也不会重复插入。

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

启动 Laravel（已执行过 `npm run build` 时，无须同时运行 Vite）：

```bash
cd /Users/wenbiaoli/develop/php-test
php artisan serve --host=127.0.0.1 --port=8000
```

浏览器访问：

<http://127.0.0.1:8000>

首页显示 `Hello Laravel + Vue` 和 Home 的欢迎词 `Hello from PostgreSQL!`。菜单可进入 <http://127.0.0.1:8000/about>、<http://127.0.0.1:8000/services> 和 <http://127.0.0.1:8000/contact>；About 页面显示 `Welcome to About!`。

修改 Vue 页面时，在另一个终端运行 `npm run dev`，即可使用 Vite 热更新。首次克隆项目或依赖更新后，先运行 `npm ci`；要生成可直接由 Laravel 提供的前端资源，运行 `npm run build`。

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
  -c "SELECT id, page, content FROM welcome_messages ORDER BY id;"
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
npm run typecheck
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

下面的命令在 **Windows 命令提示符（CMD）** 中执行。以复制到 `C:\Users\Acer\Desktop\10月新现场\Project-laravel\_demo\php-laravel-demo` 的项目为例；如果实际目录不同，请替换路径。首次准备需要安装 PHP（满足 `composer.json` 中的 `^8.3` 要求）、Composer、Node.js（含 npm）和 PostgreSQL，并确保命令在新打开的 CMD 中可用：

```bat
php -v
composer --version
node --version
npm --version
psql --version
```

如果 `psql` 无法识别，可以使用 PostgreSQL 安装包自带的 **SQL Shell (psql)**，或者把其 `bin` 目录加入 Windows 的 `PATH`。如果 `composer` 无法识别，请先安装 [Composer for Windows](https://getcomposer.org/download/)，安装时选择正在使用的 `php.exe`，然后重新打开 CMD。

进入项目，安装 PHP 和前端依赖，并构建 Vue 页面：

```bat
cd /d "C:\Users\Acer\Desktop\10月新现场\Project-laravel\_demo\php-laravel-demo"
composer install
dir vendor\autoload.php
npm ci
npm run build
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

打开 <http://127.0.0.1:8001>，可看到 Home 的数据库欢迎词和四项菜单；打开 <http://127.0.0.1:8001/about>，可看到 About 的数据库欢迎词。Services 与 Contact 页面也可从菜单进入。在启动服务器的 CMD 中按 `Ctrl+C` 停止。再次启动时，只需确认 PostgreSQL 服务已运行，然后在项目目录执行 `php artisan serve --host=127.0.0.1 --port=8001`。修改 Vue 页面时，在另一个 CMD 窗口运行 `npm run dev`；更新依赖时运行 `npm ci` 和 `npm run build`，使用 `npm run typecheck` 检查 TypeScript。

可选的检查命令：

```bat
php artisan migrate:status
php artisan test
npm run typecheck
psql -h 127.0.0.1 -U php_test -d php_test -c "SELECT id, page, content FROM welcome_messages ORDER BY id;"
```
