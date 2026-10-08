# php-test 架构

## 当前架构

项目已完成前后端分离：

```text
浏览器
  -> Vue 3 SPA
  -> TypeScript API Client
  -> Laravel /api/v1
  -> Controller / Form Request / API Resource
  -> Eloquent
  -> PostgreSQL
```

Laravel 不返回业务页面。Vue SPA 不读取数据库，也不依赖 Laravel、Blade 或 Inertia。

## 目录边界

```text
php-test/
├── app/                    # Laravel API
├── config/
├── database/
├── routes/api.php
├── tests/
└── frontend/               # 独立 Vue SPA
    ├── src/api/
    ├── src/components/
    ├── src/router/
    ├── src/types/
    └── src/views/
```

后端使用 Composer 安装和部署。前端使用 Node.js 22.12+、npm 和 Vite 构建，产物为 `frontend/dist/`。

## API 边界

- API 使用 `/api/v1` 版本前缀。
- Controller 协调请求和模型。
- Form Request 校验 JSON 输入。
- API Resource 固定响应字段。
- PostgreSQL 只允许 Laravel 访问。

成功响应：

```json
{
  "data": {
    "page": "home",
    "content": "Hello from PostgreSQL!"
  }
}
```

校验失败返回 422，并包含 `message` 和 `errors`。

## 前端边界

- Vue Router 管理四个页面。
- API Client 使用 `VITE_API_BASE_URL`。
- API 响应先做运行时校验，再进入组件状态。
- 页面处理 loading、empty、error 和 ready 状态。
- 创建、修改和删除成功后直接更新页面状态。

## 安全

- `FRONTEND_URL` 是 Laravel 允许的唯一 CORS 来源。
- 未实现认证前，生产和预发布环境拒绝写请求。
- 生产前端设置 `VITE_WRITES_ENABLED=false`。
- 数据库密码和 `APP_KEY` 不进入前端环境变量。

## 部署

Laravel API 与 Vue SPA 独立发布：

- API：PHP 文件、Composer 依赖、环境变量、数据库迁移。
- SPA：`frontend/dist/` 静态文件和 Vue Router history fallback。

两端通过版本化 JSON API 通信，不共享构建产物或运行时。
