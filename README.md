# php-test

独立部署的 Laravel JSON API 和 Vue 3 SPA。Laravel 位于仓库根目录，只访问 PostgreSQL；Vue 前端位于 `frontend/`，只通过 `/api/v1` 通信。

## 环境

- PHP 8.5
- Composer 2.10
- Laravel 13
- PostgreSQL 18
- Node.js 22 LTS
- Vue 3、TypeScript、Vite、Tailwind CSS

## 后端安装

```bash
cd /Users/wenbiaoli/develop/php-test
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

已有 `.env` 时不要覆盖。主要配置：

```dotenv
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:5173

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=php_test
DB_USERNAME=php_test
DB_PASSWORD=
```

启动 API：

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

健康检查：<http://127.0.0.1:8000/up>

## 前端安装

```bash
cd /Users/wenbiaoli/develop/php-test/frontend
nvm install
nvm use
node --version # 必须为 v22.x
npm ci
cp .env.example .env
npm run dev
```

已有 `frontend/.env` 时不要覆盖。主要配置：

```dotenv
VITE_API_BASE_URL=http://127.0.0.1:8000
VITE_WRITES_ENABLED=true
```

访问 <http://localhost:5173>。`frontend/.npmrc` 会拒绝 Node.js 22 之外的主版本。

## 测试

后端：

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

前端：

```bash
cd frontend
nvm use
npm test
npm run typecheck
npm run build
```

## API

```text
GET    /api/v1/welcome-messages
POST   /api/v1/welcome-messages
GET    /api/v1/welcome-messages/{page}
PUT    /api/v1/welcome-messages/{page}
PATCH  /api/v1/welcome-messages/{page}
DELETE /api/v1/welcome-messages/{page}
```

`page` 仅支持 `home`、`about`、`services`、`contact`。未加入认证前，写请求只允许 `local` 和 `testing` 环境；生产环境返回 403。

## 独立部署

Laravel API 只需要 PHP、Composer 和 PostgreSQL：

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

生产环境至少设置：

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.com
FRONTEND_URL=https://www.example.com
```

Vue SPA 使用 Node.js 22 构建：

```bash
cd frontend
nvm use
npm ci
VITE_API_BASE_URL=https://api.example.com VITE_WRITES_ENABLED=false npm run build
```

只部署 `frontend/dist/`。静态服务器需要把未知路径回退到 `index.html`。生产 API 不需要 Node.js，生产前端不需要 PHP。

## PostgreSQL

macOS Homebrew 初始化：

```bash
brew install postgresql@18
brew services start postgresql@18
/opt/homebrew/opt/postgresql@18/bin/createuser --login php_test
/opt/homebrew/opt/postgresql@18/bin/createdb --owner=php_test php_test
```

查询示例数据：

```bash
/opt/homebrew/opt/postgresql@18/bin/psql \
  -h 127.0.0.1 \
  -U php_test \
  -d php_test \
  -c "SELECT id, page, content FROM welcome_messages ORDER BY id;"
```

Windows 使用 PostgreSQL 安装包提供的 `psql`。本地 `.env` 中填写安装时创建的数据库密码，不要提交 `.env`。
