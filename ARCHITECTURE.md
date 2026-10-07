# php-test 前端架构方案

## 1. 文档目的

本文整理当前 `php-test` 项目，并比较以下两种 Vue 接入方案：

1. 方法 2，Laravel + Inertia + Vue：前后端在同一个 Laravel 项目中。
2. 方法 3，Vue SPA + Laravel API：前后端完全分离，通过 HTTP API 通信。

当前已采用方法 2：Laravel + Inertia + Vue。方法 3 保留作未来独立客户端或独立部署时的选型参考。

## 2. 当前系统

当前项目是一个 Laravel + Inertia + Vue 单体应用：

- PHP 8.5
- Laravel 13
- PostgreSQL 18
- Vite 8
- Tailwind CSS 4
- PHPUnit 12
- 本地地址：`http://127.0.0.1:8000`

当前页面请求流程：

```text
浏览器
  -> routes/web.php
  -> WelcomeMessage Eloquent 模型
  -> PostgreSQL welcome_messages 表
  -> Inertia 将数据传给 Home / About Vue 页面
  -> resources/views/app.blade.php 作为应用根模板
```

数据库中保存 Home 和 About 两条欢迎信息，Laravel 分别传给对应的 Vue 页面。导航使用普通 `<a>` 链接；当前没有独立 API。

## 3. 方法 2：Laravel + Inertia + Vue

### 3.1 定位

Laravel 与 Vue 保存在同一个仓库中。Laravel 负责路由、认证、授权、数据校验、业务逻辑和数据库访问；Vue 负责页面与交互；Inertia 负责将 Laravel 控制器中的数据传给 Vue 页面。

这种方案不会为每个内部页面额外编写 REST API。服务器仍然使用 `routes/web.php`，并通过 Session 和 Cookie 处理登录状态。

### 3.2 建议结构

```text
php-test/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   └── Services/              # 有明确复用需求时再建立
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   └── js/
│       ├── Components/        # 通用 Vue 组件
│       ├── Layouts/           # 页面布局
│       ├── Pages/             # 与 Laravel 路由对应的页面
│       │   ├── Home.vue
│       │   ├── About.vue
│       │   ├── Services.vue
│       │   └── Contact.vue
│       ├── Types/             # TypeScript 类型
│       └── app.ts             # Vue/Inertia 入口
├── routes/
│   ├── web.php                # Inertia 页面路由
│   └── console.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── vite.config.js
```

### 3.3 请求和数据流

```text
浏览器访问 /
  -> Laravel Web 路由
  -> 查询 Home 的 WelcomeMessage
  -> Inertia::render('Home', props)
  -> Home.vue 接收 props
  -> Vue 渲染页面
```

表单提交也由 Inertia 发往 Laravel Web 路由：

```text
Vue 表单
  -> Inertia POST/PUT/DELETE
  -> Form Request 校验
  -> Controller
  -> Model / Service
  -> PostgreSQL
  -> Redirect + Flash Message
  -> Vue 页面更新
```

### 3.4 后端职责

- `routes/web.php`：声明页面路由，路由本身保持简短。
- Controller：接收请求、调用模型或业务服务、返回 Inertia 页面。
- Form Request：集中处理输入校验和请求级授权。
- Model：定义数据、类型转换和 Eloquent 关系。
- Policy：处理资源权限。
- Service：仅承载需要跨控制器复用或较复杂的业务流程。
- Migration 和 Seeder：维护数据库结构及开发数据。

### 3.5 前端职责

- `Pages`：页面级 Vue 组件，与后端路由对应。
- `Components`：可复用的展示和交互组件。
- `Layouts`：统一导航、侧栏和页面框架。
- TypeScript 类型：描述 Laravel 传入的页面属性。
- Vite：开发热更新和生产资源构建。

### 3.6 优点

- 一个仓库、一套路由和一次部署。
- 不需要为内部页面重复设计 API。
- 可以直接使用 Laravel Session、CSRF、认证和授权。
- Laravel 校验错误可以直接传给 Vue 表单。
- 保留 Vue 组件化、响应式状态和 SPA 式页面体验。
- 对当前小型项目改造成本较低。

### 3.7 限制

- Vue 页面与 Laravel 路由及 Inertia 协议存在绑定。
- 不适合作为第三方公共 API 的唯一架构。
- 如果以后存在手机 App，仍需补充独立 API。
- 前端无法完全脱离 Laravel 单独部署。

### 3.8 适用场景

- 只有一个 Web 前端。
- PHP 和 Vue 由同一团队维护。
- 前后端一起发布。
- 希望快速完成后台系统、业务平台或管理系统。
- 暂时没有手机 App、第三方开放 API 或多个独立客户端。

## 4. 方法 3：Vue SPA + Laravel API

### 4.1 定位

Vue 和 Laravel 是两个独立应用。Vue SPA 只负责浏览器界面，通过 JSON API 请求 Laravel；Laravel 不返回业务页面，只提供认证、业务逻辑、文件处理和数据接口。

可以采用两个独立仓库，也可以使用一个仓库中的两个顶级目录。若团队和发布流程尚未分开，建议先使用同一仓库，减少版本协调成本。

### 4.2 建议结构

```text
project/
├── backend/                   # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   ├── Policies/
│   │   └── Services/
│   ├── database/
│   ├── routes/
│   │   └── api.php
│   └── tests/
└── frontend/                  # Vue SPA
    ├── src/
    │   ├── api/
    │   ├── components/
    │   ├── layouts/
    │   ├── router/
    │   ├── stores/
    │   ├── types/
    │   └── views/
    ├── package.json
    └── vite.config.ts
```

如果拆成两个仓库，可以分别命名为：

```text
php-test-api
php-test-web
```

### 4.3 请求和数据流

```text
浏览器打开 Vue SPA
  -> Vue Router 匹配前端页面
  -> API Client 请求 /api/v1/welcome-messages/1
  -> Laravel API 路由
  -> API Controller
  -> Form Request / Policy
  -> Model / Service
  -> PostgreSQL
  -> API Resource 生成 JSON
  -> Vue Store 或页面状态
  -> Vue 渲染页面
```

### 4.4 Laravel API 职责

- `routes/api.php`：只声明 API 路由，并从一开始使用 `/api/v1` 版本前缀。
- API Controller：协调请求，不直接承载复杂业务逻辑。
- Form Request：校验 JSON 输入。
- API Resource：统一响应字段，避免直接暴露数据库模型结构。
- Policy：确保每个资源操作都经过授权。
- Service：承载可复用业务流程，并与传输层解耦。
- Sanctum：处理第一方 SPA 登录；如果将来需要标准 OAuth2，再评估 Passport。

建议统一响应格式：

```json
{
  "data": {
    "id": 1,
    "content": "Hello from PostgreSQL!"
  }
}
```

错误响应至少应保持以下信息稳定：

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "content": ["The content field is required."]
  }
}
```

### 4.5 Vue SPA 职责

- Vue Router：管理前端路由。
- API Client：统一 Base URL、请求头、CSRF、超时和错误转换。
- Pinia：只保存需要跨页面共享的客户端状态。
- View：组织页面，不直接散落底层 HTTP 调用。
- TypeScript：维护 API 请求和响应类型。
- 环境配置：区分本地、测试和生产 API 地址。

### 4.6 认证和安全

第一方 Web SPA 建议使用 Laravel Sanctum 的 Cookie 认证：

```text
Vue 获取 CSRF Cookie
  -> 提交登录请求
  -> Laravel 建立 Session
  -> 后续 API 请求携带 Cookie
```

前后端使用不同域名时，需要明确配置：

- CORS 允许来源。
- Sanctum stateful domains。
- Session Cookie 域名、Secure 和 SameSite。
- CSRF Cookie 和凭证请求。
- HTTPS。

不要把数据库密码、Laravel `APP_KEY` 或长期有效的敏感令牌放入 Vue 构建变量中。

### 4.7 优点

- 前端和后端可以独立开发、测试、部署和扩容。
- 同一 API 可以服务 Web、手机 App 和第三方客户端。
- API 契约清晰，适合独立团队协作。
- Vue 可以部署到静态托管或 CDN。
- 后端可专注于领域逻辑和数据服务。

### 4.8 限制

- 必须维护 API 契约、版本、文档和兼容性。
- 认证、CSRF、CORS 和 Cookie 配置更复杂。
- 需要两个开发服务器和两套构建流程。
- 前后端发布时间需要协调。
- 一个简单页面也需要 Controller、Resource、API Client 和前端状态处理。
- 对当前项目而言，初期成本明显高于方案一。

### 4.9 适用场景

- 确定需要手机 App、桌面客户端或第三方 API。
- 前端和后端由不同团队负责。
- 两端需要独立部署或独立扩容。
- API 本身是正式产品。
- 多个客户端共享相同业务能力。

## 5. 两种方案的系统对比

| 项目 | Laravel + Inertia + Vue | Vue SPA + Laravel API |
| --- | --- | --- |
| 仓库 | 一个 | 一个双目录或两个仓库 |
| 页面路由 | Laravel | Vue Router |
| 后端路由 | `web.php` | `api.php` |
| 数据传输 | Inertia props | JSON API |
| Web 认证 | Laravel Session | Sanctum Session/Cookie |
| CSRF | Laravel/Inertia 自动协作 | 前后端需要共同配置 |
| CORS | 通常不需要 | 通常需要 |
| 本地服务 | Laravel + Vite | Laravel API + Vue Vite |
| 部署 | 一次部署 | 两端分别部署 |
| 移动端复用 | 需要新增 API | 可以直接复用 API |
| 初期复杂度 | 较低 | 较高 |
| 长期边界 | 适合单一 Web 产品 | 适合多客户端平台 |

## 6. 对当前项目的建议

当前项目已采用方法 2。Home 和 About 从 PostgreSQL 读取各自的欢迎词；Services 和 Contact 是简单 Vue 页面。所有页面由 Laravel 路由返回 Inertia 响应，前端使用 TypeScript，菜单通过 `<a>` 链接跳转。

只有满足以下任一条件时，再切换或扩展为方法 3：

- 确认开发手机 App。
- 确认向第三方开放 API。
- 前后端需要独立团队和独立发布。
- Vue 必须部署到与 Laravel 不同的基础设施。

## 7. 演进策略

选择方法 2 不会阻止将来建设 API。建议把可复用业务规则放在模型、Policy、Service 或 Action 中，不把核心业务写死在 Inertia Controller 中。以后新增 API 时，可以复用这些业务能力，只新增 API Controller、Form Request 和 API Resource。

```text
当前：
Inertia Controller -> Shared Business Logic -> Model -> PostgreSQL

未来：
Inertia Controller --┐
                     ├-> Shared Business Logic -> Model -> PostgreSQL
API Controller ------┘
```

通过这种方式，项目可以先保持简单，并在出现真实的多客户端需求后平滑扩展。
