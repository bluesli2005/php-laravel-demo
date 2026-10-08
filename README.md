# php-test

Laravel JSON API + Vue 3 SPA 的本地示例项目。后端位于仓库根目录，前端位于 `frontend/`，数据库使用 PostgreSQL。

## 1. 环境要求

- macOS + Homebrew（Windows 请使用对应安装包和命令）
- PHP 8.3+
- Composer 2+
- Node.js 22.12+
- npm
- PostgreSQL 18

确认命令：

```bash
php -v
composer --version
node --version
npm --version
/opt/homebrew/opt/postgresql@18/bin/psql --version
```

## 2. 第一次构建

以下步骤适用于第一次在本机准备项目。

### 2.1 安装并启动 PostgreSQL

```bash
brew install postgresql@18
brew services start postgresql@18
```

只需首次执行一次，创建本地数据库用户和数据库：

```bash
/opt/homebrew/opt/postgresql@18/bin/createuser --login php_test
/opt/homebrew/opt/postgresql@18/bin/createdb --owner=php_test php_test
```

当前 macOS 本地配置使用可信认证，数据库密码为空。如果本机 PostgreSQL 要求密码，请将密码填写到 `.env` 的 `DB_PASSWORD`。

### 2.2 安装后端依赖并创建环境配置

```bash
cd {USERPATH}/php-test
composer install
cp .env.example .env
php artisan key:generate
```

已有 `.env` 时不要再次执行 `cp`，避免覆盖本地配置。

检查 `.env` 中的数据库配置：

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=php_test
DB_USERNAME=php_test
DB_PASSWORD=
```

前端 API 地址：

```dotenv
FRONTEND_URL=http://localhost:5173
```

### 2.3 初始化数据库

执行迁移并写入保险示例数据：

```bash
php artisan migrate --seed
```

验证迁移状态：

```bash
php artisan migrate:status
```

### 2.4 安装并构建前端

```bash
cd {USERPATH}/php-test/frontend
nvm install
nvm use
npm ci
cp .env.example .env
npm run build
```

已有 `frontend/.env` 时不要再次执行 `cp`。本地开发配置通常为：

```dotenv
VITE_API_BASE_URL=http://127.0.0.1:8000
VITE_WRITES_ENABLED=true
```

## 3. 日常启动

每次启动前确认 PostgreSQL 正在运行：

```bash
brew services start postgresql@18
```

### 3.1 启动后端 API

终端一：

```bash
cd {USERPATH}/php-test
php artisan serve --host=127.0.0.1 --port=8000
```

后端地址：<http://127.0.0.1:8000>

健康检查：<http://127.0.0.1:8000/up>

### 3.2 启动前端开发服务器

终端二：

```bash
cd {USERPATH}/php-test/frontend
nvm use
npm run dev
```

前端地址：<http://localhost:5173>

前端通过 `VITE_API_BASE_URL` 访问后端 API。修改 Vue 或 TypeScript 文件后，Vite 会自动热更新。

停止服务：在对应终端按 `Control + C`。停止 PostgreSQL：

```bash
brew services stop postgresql@18
```

## 4. 数据库更新方法

### 4.1 查看迁移状态

```bash
cd {USERPATH}/php-test
php artisan migrate:status
```

### 4.2 添加数据库字段或表

不要直接修改已经执行过的旧 migration。创建新的 migration：

```bash
php artisan make:migration add_example_column_to_life_insurance_policies_table --table=life_insurance_policies
```

编辑生成的文件后执行：

```bash
php artisan migrate
```

### 4.3 回滚最近一次迁移

```bash
php artisan migrate:rollback
```

只在本地或测试环境使用。回滚前确认不会影响需要保留的数据。

### 4.4 重新执行种子数据

当前 `DatabaseSeeder` 使用 `firstOrCreate`，重复执行不会重复插入示例保单：

```bash
php artisan db:seed
```

### 4.5 本地完全重建数据库

以下命令会删除当前数据库表和数据，仅适用于本地开发：

```bash
php artisan migrate:fresh --seed
```

不要在生产环境执行 `migrate:fresh`。

### 4.6 查询示例保单

```bash
/opt/homebrew/opt/postgresql@18/bin/psql \
  -h 127.0.0.1 \
  -U php_test \
  -d php_test \
  -c "SELECT id, policy_number, policyholder_name, status FROM life_insurance_policies ORDER BY id;"
```

## 5. 测试与检查

后端：

```bash
cd {USERPATH}/php-test
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

前端：

```bash
cd {USERPATH}/php-test/frontend
npm test
npm run typecheck
npm run build
```

Storybook：

```bash
npm run storybook
npm run test-storybook -- --run
npm run build-storybook
```

## 6. API

```text
GET    /api/v1/life-insurance-policies
POST   /api/v1/life-insurance-policies
GET    /api/v1/life-insurance-policies/{life_insurance_policy}
PUT    /api/v1/life-insurance-policies/{life_insurance_policy}
PATCH  /api/v1/life-insurance-policies/{life_insurance_policy}
DELETE /api/v1/life-insurance-policies/{life_insurance_policy}
```

列表接口支持：

```text
GET /api/v1/life-insurance-policies?search=张三&status=active
```

`search` 用于搜索保单号、投保人、被保险人、受益人和备注；`status` 用于按保险状态筛选。

未加入认证前，写请求只允许在 `local` 和 `testing` 环境使用；生产环境返回 403。

## 7. 生产构建

后端：

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

前端：

```bash
cd frontend
nvm use
npm ci
VITE_API_BASE_URL=https://api.example.com VITE_WRITES_ENABLED=false npm run build
```

生产环境只部署 `frontend/dist/`。静态服务器需要将未知路径回退到 `index.html`。

不要将 `.env`、`frontend/.env` 或包含密码的配置提交到 Git。
