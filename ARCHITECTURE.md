# php-test 当前架构

## 1. 系统概览

项目当前是同仓库、前后端分离的生命保险保单 Demo：

```text
浏览器
  -> Vue 3 SPA（frontend/）
  -> TypeScript API Client
  -> Laravel JSON API（/api/v1）
  -> Controller / Form Request / API Resource
  -> LifeInsurancePolicy Eloquent Model
  -> PostgreSQL（life_insurance_policies）
```

Laravel 只提供 JSON API、数据库访问和健康检查，不再提供 Blade、Inertia 或业务页面。Vue SPA 独立运行和构建，不直接访问数据库。

## 2. 目录结构

```text
php-test/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/LifeInsurancePolicyController.php
│   │   ├── Middleware/EnsureLocalApiWrites.php
│   │   ├── Requests/
│   │   └── Resources/LifeInsurancePolicyResource.php
│   └── Models/LifeInsurancePolicy.php
├── database/
│   ├── factories/LifeInsurancePolicyFactory.php
│   ├── migrations/2026_10_08_022545_create_life_insurance_policies_table.php
│   └── seeders/DatabaseSeeder.php
├── routes/api.php
├── tests/Feature/Api/LifeInsurancePolicyTest.php
├── composer.json
├── artisan
└── frontend/
    ├── src/
    │   ├── api/
    │   ├── components/
    │   ├── router/
    │   ├── types/
    │   └── views/
    ├── .storybook/
    ├── package.json
    ├── vite.config.ts
    └── vitest.config.ts
```

## 3. 后端架构

### 3.1 路由和启动

- `routes/api.php` 注册 `/api/v1/life-insurance-policies` RESTful 路由。
- `bootstrap/app.php` 只注册 API、Console 和 `/up` 健康检查路由。
- 后端使用 PHP、Composer 和 PostgreSQL，独立启动于 `127.0.0.1:8000`。

### 3.2 API 请求流程

```text
HTTP 请求
  -> EnsureLocalApiWrites
  -> LifeInsurancePolicyController
  -> Form Request 校验
  -> LifeInsurancePolicy
  -> LifeInsurancePolicyResource
  -> JSON { data: ... }
```

- `IndexLifeInsurancePolicyRequest` 校验列表搜索和状态筛选参数。
- `StoreLifeInsurancePolicyRequest` 和 `UpdateLifeInsurancePolicyRequest` 校验新增、修改数据。
- `LifeInsurancePolicyResource` 固定 API 响应字段和 JSON envelope。
- Laravel API 异常通过 `bootstrap/app.php` 统一按 JSON 返回。

### 3.3 当前 API

```text
GET    /api/v1/life-insurance-policies
POST   /api/v1/life-insurance-policies
GET    /api/v1/life-insurance-policies/{life_insurance_policy}
PUT    /api/v1/life-insurance-policies/{life_insurance_policy}
PATCH  /api/v1/life-insurance-policies/{life_insurance_policy}
DELETE /api/v1/life-insurance-policies/{life_insurance_policy}
```

列表查询：

```text
GET /api/v1/life-insurance-policies?search=张三&status=active
```

`search` 查询保单号、投保人、被保险人、受益人和备注；`status` 按保单状态筛选。列表默认按创建时间倒序，`per_page` 仅允许 10、20、30、50；响应包含 Laravel paginator 的 `meta` 和 `links`。

### 3.4 数据模型

当前业务数据库只使用 `life_insurance_policies` 表，人员信息直接保存在保单中，不拆分独立人员表。

主要字段：

- 保单号：`policy_number`，唯一，最多 50 字符
- 投保人和被保险人：姓名字段必填
- 保额和保费：`decimal(15, 2)`，必须大于 0
- 状态：`draft`、`active`、`expired`、`cancelled`
- 生效日和失效日：日期字段，失效日不能早于生效日
- 受益人和备注：可为空
- 删除：硬删除，不使用软删除

`DatabaseSeeder` 使用 `firstOrCreate` 写入 120 条可重复执行的示例保单。

## 4. 前端架构

### 4.1 页面路由

| 路由 | 页面 | 作用 |
| --- | --- | --- |
| `/` | HomeView | 应用入口 |
| `/policies` | PolicyListView | 保单列表、搜索、状态筛选、删除 |
| `/policies/create` | PolicyCreateView | 新建保单 |
| `/policies/:id` | PolicyDetailView | 查看保单详情 |
| `/policies/:id/edit` | PolicyEditView | 编辑保单 |

Vue Router 使用 history 模式，未知路径回退到首页。

### 4.2 前端分层

```text
View
  -> API module（frontend/src/api/）
  -> apiRequest（统一请求、错误和响应处理）
  -> Laravel /api/v1
```

- `frontend/src/types/lifeInsurancePolicy.ts` 定义保单、状态和表单类型。
- `frontend/src/api/lifeInsurancePolicies.ts` 封装 CRUD、搜索和状态筛选。
- API 响应先做运行时结构校验，再进入页面状态。
- 页面处理 loading、empty、error 和 ready 状态。
- 新增、修改、删除成功后直接更新页面状态，不强制刷新浏览器。

### 4.3 通用组件

`frontend/src/components/` 当前包含：

- `BaseInput`、`BaseSelect`、`BaseTextarea`
- `BaseButton`、`BaseErrorMessage`
- `BaseTable`
- `PolicySearchBox`、`PolicyStatusSelect`
- `ContentState`
- `LifeInsurancePolicyForm`

基础控件采用最小 props 和 `v-model` 传递；对应控件均有 Storybook story。`BaseTable` 通过 columns、rows 和动态 cell slot 支持通用表格渲染。

## 5. Storybook 和测试

- Storybook 配置位于 `frontend/.storybook/`。
- 组件 story 位于 `frontend/src/components/*.stories.ts`。
- Storybook 使用 Vitest、Playwright 和 accessibility addon 做浏览器级组件测试。
- 前端验证包括 `npm test`、`npm run typecheck`、`npm run build` 和 `npm run test-storybook -- --run`。
- 后端验证包括 Laravel Feature Test 和 Pint 格式检查。

## 6. 安全边界

- `FRONTEND_URL` 限制 Laravel API 的 CORS 来源。
- `EnsureLocalApiWrites` 只允许 `local` 和 `testing` 环境执行写请求；生产环境拒绝新增、修改和删除。
- 生产前端使用 `VITE_WRITES_ENABLED=false` 隐藏写入控件。
- `APP_KEY`、数据库密码和后端环境变量不进入前端构建。
- 当前未实现认证和授权，因此不应将写入接口直接暴露到公网。

## 7. 部署边界

Laravel API 和 Vue SPA 独立部署：

- API：PHP 源码、Composer 依赖、环境变量和 PostgreSQL。
- SPA：`frontend/dist/` 静态文件。
- 静态服务器需要将 Vue Router 未知路径回退到 `index.html`。
- API 部署不需要 Node.js；生产前端不需要 PHP。

## 8. 非当前架构内容

- 不使用 Laravel Blade、Inertia 或 Laravel 内嵌 Vue 页面。
- 不使用 `welcome_messages` 表及 Welcome Message 业务。
- 不包含登录、权限、核保、理赔、缴费记录、附件、通知、报表和审计日志。
