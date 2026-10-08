# 前后端分离迁移计划

## 1. 文档目的

本文用于记录当前 Laravel + Inertia + Vue 应用分阶段迁移为两个独立应用的过程：

- Laravel JSON API 后端
- Vue 3 + TypeScript SPA 前端

本次迁移仅覆盖现有的 Home、About、Services 和 Contact 四个页面。迁移期间继续使用一个 Git 仓库，暂不拆分为两个仓库。

## 2. 已确认的决定

| 项目 | 决定 |
| --- | --- |
| Laravel Mix | 不要求使用 |
| Vue 版本 | Vue 3 |
| Git 仓库 | 暂时使用一个仓库，以后可再拆分 |
| 部署方式 | 前端和后端分别部署 |
| 认证 | 本次暂不实现，后续单独追加 |
| 其他客户端 | 将来可能存在，目前尚未确定 |
| API 能力 | 需要新增、查询、修改、删除 |
| TypeScript | 保留 |
| Tailwind CSS | 保留 |
| 本地开发 | 前端和后端分别启动 |
| 页面范围 | 仅 Home、About、Services、Contact |
| 前端构建工具 | Vite |
| 数据库 | PostgreSQL，仅允许 Laravel 后端访问 |

## 3. 重要限制

本次迁移暂不实现认证，但要求提供新增、修改和删除接口。如果把没有认证的写入接口直接部署到公网，任何人都可能修改或删除页面内容。

在认证和授权实现之前，需要采用以下临时策略之一：

1. 推荐方案：完整 CRUD 接口只在本地和测试环境开放；生产环境只开放查询接口。
2. 备选方案：在基础设施层临时保护写入接口，直到应用认证完成。

在部署写入接口之前，必须确认采用哪一种策略。

## 4. 当前架构

```text
浏览器
  -> Laravel routes/web.php
  -> WelcomeMessage 模型
  -> PostgreSQL
  -> Inertia props
  -> resources/js/Pages 下的 Vue 页面
```

当前系统特点：

- 浏览器页面路由由 Laravel 管理。
- Home 和 About 通过 Inertia props 接收数据库内容。
- Services 和 Contact 是静态 Vue 页面。
- Vue、TypeScript、Tailwind 和 Laravel 作为一个应用构建。
- `resources/views/app.blade.php` 是 Inertia 应用根模板。

## 5. 目标架构

```text
浏览器
  -> Vue 3 SPA
  -> Vue Router
  -> 带 TypeScript 类型的 API Client
  -> Laravel /api/v1 接口
  -> Controller / Request / Resource
  -> WelcomeMessage 模型
  -> PostgreSQL
```

迁移期间的目标目录结构：

```text
php-test/
├── app/                       # Laravel API 后端
├── bootstrap/
├── config/
├── database/
├── routes/
│   ├── api.php
│   └── console.php
├── tests/
├── composer.json
├── artisan
└── frontend/                  # 独立 Vue 应用
    ├── src/
    │   ├── api/
    │   ├── components/
    │   ├── layouts/
    │   ├── router/
    │   ├── types/
    │   └── views/
    ├── package.json
    ├── tsconfig.json
    └── vite.config.ts
```

Laravel 暂时保留在仓库根目录，避免在功能迁移期间进行风险较高的大规模目录移动。以后拆分仓库时，可以把 `frontend/` 迁移到独立仓库，现有仓库继续保留 Laravel 后端及其历史记录。

## 6. API 契约草案

迁移期间继续使用现有 `welcome_messages` 表和 `WelcomeMessage` 模型。数据库中将保存 `home`、`about`、`services` 和 `contact` 四条页面数据。

计划提供以下接口：

```text
GET    /api/v1/welcome-messages
POST   /api/v1/welcome-messages
GET    /api/v1/welcome-messages/{page}
PUT    /api/v1/welcome-messages/{page}
PATCH  /api/v1/welcome-messages/{page}
DELETE /api/v1/welcome-messages/{page}
```

响应示例：

```json
{
  "data": {
    "page": "home",
    "content": "Hello from PostgreSQL!"
  }
}
```

本次范围内，`page` 只允许使用四个已支持的页面标识。

## 7. 迁移阶段

### 阶段 0：迁移规划和基准确认

状态：已完成

目标：

- 记录已经确认的架构决定。
- 明确迁移顺序和各阶段回滚边界。
- 保留当前可运行的 Inertia 应用作为迁移基准。
- 开始开发前确认后端测试和前端生产构建能够通过。

阶段成果：

- 本迁移计划文档。
- 在进度记录中保存基准测试和构建结果。

验收条件：

- 明确本次范围和不包含的内容。
- 后续每个阶段都有独立验证方法。
- 不删除现有应用代码。

### 阶段 1：在保留 Inertia 的同时增加 Laravel API

状态：已完成（2026-10-07，已提交 Git）

目标：

- 启用 Laravel API 路由。
- 增加带版本号的 `/api/v1` 路由。
- 为 `WelcomeMessage` 增加 API Controller 和 API Resource。
- 为新增和修改操作增加请求校验。
- 为四个页面创建种子数据，并且不覆盖用户已经修改的内容。
- 保持现有 Inertia 路由继续工作。

计划新增的后端文件：

```text
routes/api.php
app/Http/Controllers/Api/V1/WelcomeMessageController.php
app/Http/Requests/StoreWelcomeMessageRequest.php
app/Http/Requests/UpdateWelcomeMessageRequest.php
app/Http/Resources/WelcomeMessageResource.php
tests/Feature/Api/WelcomeMessageTest.php
```

验收条件：

- 六个 CRUD 路由返回稳定的 JSON 响应。
- 校验错误使用统一 JSON 格式。
- 不支持的页面标识会被拒绝。
- Home、About、Services 和 Contact 对应的数据全部存在。
- 现有 Inertia 页面继续正常工作。
- 后端测试全部通过。

回滚方法：删除新增的 API 路由和 API 类，继续使用现有 Inertia 应用。

### 阶段 2：建立独立 Vue 应用

状态：已完成（2026-10-07，已提交 Git）

目标：

- 在 `frontend/` 中建立 Vue 3 + TypeScript + Vite 应用。
- 配置 Tailwind CSS。
- 使用 Vue Router 管理四个页面路由。
- 增加带类型的 API Client 和基于环境变量的后端地址。
- 建立加载中、空数据和错误状态。

计划新增的前端文件：

```text
frontend/src/api/client.ts
frontend/src/api/welcomeMessages.ts
frontend/src/components/SiteNav.vue
frontend/src/router/index.ts
frontend/src/types/welcomeMessage.ts
frontend/src/views/HomeView.vue
frontend/src/views/AboutView.vue
frontend/src/views/ServicesView.vue
frontend/src/views/ContactView.vue
frontend/src/App.vue
frontend/src/main.ts
frontend/.env.example
```

验收条件：

- `npm run dev` 可以独立启动前端。
- `npm run typecheck` 通过。
- `npm run build` 可以生成独立前端构建产物。
- 四个路由支持直接访问和浏览器刷新。
- 前端代码不再依赖 Inertia 或 Laravel 专用前端模块。

回滚方法：删除 `frontend/`，现有 Inertia 应用不受影响。

### 阶段 3：迁移四个页面并接入 API

状态：已完成（2026-10-07，已提交 Git）

目标：

- 把现有页面和导航迁移到独立 SPA。
- 四个页面分别通过 Laravel API 获取数据库内容。
- 使用 Vue Router 导航替换普通 `<a>` 链接。
- 页面显示内容继续全部使用英文。

验收条件：

- `/`、`/about`、`/services` 和 `/contact` 均由 SPA 正常显示。
- 每个页面能够读取并显示对应的数据库记录。
- 页面具备明确的加载和 API 错误状态。
- 页面切换不会触发完整浏览器刷新。
- TypeScript 不直接假设未经校验的 API 响应结构。

回滚方法：继续把访问流量指向现有 Laravel/Inertia 前端。

### 阶段 4：接入新增、查询、修改和删除功能

状态：已完成（2026-10-08，已提交 Git）

目标：

- 在 Vue 前端接入新增、查询、修改和删除操作。
- 增加表单校验和 API 错误显示。
- 明确页面数据删除后的界面行为。
- 应用认证完成前的临时写入接口安全策略。

验收条件：

- 完成新增、查询、修改和删除的端到端验证。
- 无效输入会被前端和后端共同拒绝。
- 数据修改后无需完整刷新页面即可显示最新结果。
- 生产环境不会暴露无认证保护的写入接口。

回滚方法：禁用写入路由，保留只读页面和查询接口。

### 阶段 5：实现独立启动和独立部署

状态：已完成（2026-10-08，已提交 Git）

目标：

- 提供独立的后端和前端启动命令。
- 配置 CORS 和各环境的 API 地址。
- 定义独立的构建与部署产物。
- 记录开发、测试和生产环境变量。
- 将独立前端的开发、CI 和部署运行时统一为 Node.js 22 LTS，避免本机 Node.js 23 与 Vitest 4 的 engine 不兼容提示。
- 使用 `.nvmrc` 和 `package.json` 的 `engines` 约束 Node.js 版本，并在 README 中记录版本切换和依赖安装命令。

预期本地启动命令：

```bash
# 后端
php artisan serve --host=127.0.0.1 --port=8000

# 前端
cd frontend
npm run dev
```

验收条件：

- 前端和后端能够独立启动。
- 前端部署不依赖 PHP 运行环境。
- 后端部署不需要构建 Vue SPA。
- CORS 只允许配置过的前端来源。
- README 包含前后端分别安装、启动和部署的说明。
- Node.js 22 LTS 环境下执行 `npm ci`、`npm test`、`npm run typecheck` 和 `npm run build` 均成功，且不再出现 `EBADENGINE` 警告。
- 本地开发、CI 和生产构建读取同一份 Node.js 版本约束。

回滚方法：部署配置修复前，继续把用户访问指向现有 Laravel/Inertia 应用。

### 阶段 6：删除 Inertia 和旧前端文件

状态：已完成（2026-10-08，待用户确认）

前置条件：阶段 1 至阶段 5 已全部完成并得到确认。

目标：

- 删除 Inertia 前端依赖和中间件。
- 删除 Blade 应用根模板。
- 删除 `resources/js` 下的旧 Vue 页面。
- 删除只为 Laravel 内嵌前端服务的 Vite 配置。
- 保留 Laravel API、数据库、测试和后端配置。

最终检查后可能删除的文件：

```text
app/Http/Middleware/HandleInertiaRequests.php
resources/js/Components/
resources/js/Pages/
resources/js/app.ts
resources/views/app.blade.php
```

验收条件：

- 删除 Inertia 后 Laravel API 测试全部通过。
- 独立前端类型检查和生产构建通过。
- 后端不存在返回 Inertia 响应的路由。
- 所有替代实现验证完成后才删除旧文件。

回滚方法：本阶段使用独立提交，确保删除操作可以单独撤销。

## 8. 测试策略

后端：

- 为列表、新增、详情、修改和删除编写 API Feature Test。
- 测试不支持的页面标识和无效内容。
- 测试 `page` 字段唯一性。
- 验证配置过的前端来源能够通过 CORS。

前端：

- 执行 TypeScript 类型检查。
- 验证生产构建。
- 根据实际价值测试加载成功、空数据和错误状态。
- 测试四个 Vue Router 路由。
- 测试 API Client 对成功响应和校验错误的转换。

端到端：

- 使用独立运行的前端和后端访问四个页面。
- 修改页面数据并确认新内容能够显示。
- 删除并重新创建一条页面数据。
- 确认 PostgreSQL 中的数据结果正确。

## 9. 暂不处理的内容

- 用户认证和用户管理
- 角色与权限管理
- 把当前仓库拆分为两个 Git 仓库
- 手机端或第三方客户端
- OAuth2 或 Laravel Passport
- Home、About、Services、Contact 之外的页面
- 服务端渲染

这些内容应在 API 和 SPA 边界稳定之后，作为独立任务处理。

## 10. 已确认决定与后续问题

已确认：

1. 四个页面均提供可见的新增、编辑和删除界面。
2. 应用认证完成前，生产环境禁用写入接口；前端生产构建默认隐藏写入控件，Laravel 后端继续返回 JSON 403，形成双重保护。
3. 删除页面数据后显示空状态，并提供立即重新创建功能。

后续仍需确认：

1. 生产环境计划使用什么前端和后端地址，例如 `www.example.com` 和 `api.example.com`？
2. 是否需要现在生成 OpenAPI 等 API 文档，还是以后再处理？

## 11. 进度记录

| 日期 | 阶段 | 阶段成果 | 验证结果 | 状态 |
| --- | --- | --- | --- | --- |
| 2026-10-07 | 阶段 0 | 已记录迁移决定并生成分阶段计划 | 已根据当前 Laravel/Inertia/Vue 结构核对范围 | 已完成 |
| 2026-10-07 | 阶段 1 | 版本化 CRUD API、Resource、请求校验、四页非覆盖种子数据、本地/测试写入保护；按 AGENTS.md 安装 Boost | 60 项测试 / 307 个断言通过；TypeScript 和生产构建通过；本地 PostgreSQL 四页齐全且旧记录未变；四个 Inertia 页面及 API 实际 HTTP 检查通过 | 已完成并提交：`83954be` |
| 2026-10-07 | 阶段 2 | 建立独立 Vue 3 + TypeScript + Vite + Tailwind CSS SPA，配置 Vue Router、类型化 API Client 和通用内容状态 | 独立前端 typecheck、生产构建通过；四路由直接访问均为 200；浏览器导航和刷新通过；控制台无警告或错误 | 已完成并提交：`6383df6` |
| 2026-10-07 | 阶段 3 | 将 Home、About、Services、Contact 页面迁移到独立 SPA，并分别接入 Laravel API | 四页真实 PostgreSQL 内容、客户端导航、直接刷新、API 失败和重试恢复均经浏览器验证；类型检查和生产构建通过 | 已完成并提交：`5e03807` |
| 2026-10-08 | 阶段 4 | 四页可见 CRUD、前后端校验、删除确认及立即重建、生产环境写入双重保护 | 前端 15 项测试通过；独立前端 typecheck/构建、Laravel 60 项测试/307 个断言及旧前端构建通过；使用临时 SQLite 完成浏览器 CRUD 验证，生产模式写入返回 403 且不显示写入控件 | 已完成并提交：`88cfd03` |
| 2026-10-08 | 阶段 5 | 独立启动和部署说明、受限 CORS、前后端环境变量、Node.js 22 LTS 约束 | Node.js 22.23.3 下 `npm ci`、15 项前端测试、类型检查和构建通过；Laravel 61 项测试/315 个断言通过；独立服务、CORS 和浏览器读取验证通过 | 已完成并提交：`a08ea2b` |
| 2026-10-08 | 阶段 6 | 删除 Inertia、Laravel 内嵌 Vue、Web 页面路由和根目录 Node 构建工具链 | Laravel 58 项测试/282 个断言、Composer 校验、独立前端 15 项测试、类型检查和构建通过；API-only 路由与独立 SPA 经 HTTP 和浏览器验证 | 已完成，待用户确认；未提交 |

每完成一个阶段，都要更新此表，记录实际交付内容、测试结果、未解决风险，以及用户确认提交后对应的提交记录。

## 12. 工作约定

- 每次只完成一个阶段。
- 每个阶段结束后列出修改文件和验证结果。
- 得到用户确认后再开始下一阶段。
- 独立 SPA 验证完成之前，不删除可运行的 Inertia 前端。
- 不自动执行 Git 提交，只有用户明确确认后才提交。

### 阶段 1 实施记录（2026-10-07）

- 新增：`routes/api.php`、`app/Http/Controllers/Api/V1/WelcomeMessageController.php`、`app/Http/Requests/StoreWelcomeMessageRequest.php`、`app/Http/Requests/UpdateWelcomeMessageRequest.php`、`app/Http/Resources/WelcomeMessageResource.php`、`app/Http/Middleware/EnsureLocalApiWrites.php`、`tests/Feature/Api/WelcomeMessageTest.php`。
- 修改：`bootstrap/app.php`、`app/Models/WelcomeMessage.php`、`database/seeders/DatabaseSeeder.php`、本迁移文档。
- 项目初始化要求：安装 `laravel/boost` 开发依赖，更新 `composer.json` / `composer.lock`，新增 `boost.json`，执行 `boost:install --guidelines --no-interaction`。本机忽略的 AGENTS.md、CLAUDE.md 和 Cursor 指南由安装器更新。
- API 契约：查询、修改和删除成功返回 200 + `data`，新增返回 201 + `data`；DELETE 返回已删除记录的 page/content。校验失败返回 422 + `message` / `errors`；不存在或不支持的 URL 页面标识返回 JSON 404。Laravel 路由列表显示 5 条，PUT/PATCH 共用一条，对应计划中的六种请求。
- 新增要求 page 为 home/about/services/contact 且唯一，content 为非空字符串，最长 255 字符（匹配现有数据库列）。PUT/PATCH 均要求 content；page 可以省略或与 URL 相同，不允许重命名页面。额外字段不会写入数据库。
- 临时采用文档推荐保护：所有环境允许读取，仅 `APP_ENV=local` / `testing` 允许写入，其他环境写入返回 JSON 403。正式认证与部署策略确认仍留在后续阶段。
- 基准：5 项后端测试 / 38 个断言通过；`npm run build` 包含 TypeScript 检查且通过。构建仅有可选 fontaine 字体回退优化提示，无构建错误。
- 最终：`php artisan test --compact` 共 60 项测试 / 307 个断言通过；`vendor/bin/pint --dirty --format agent` 和 `git diff --check` 通过。
- 数据验证：对本机 PostgreSQL 执行非覆盖种子填充，四页均存在，填充前已有记录（包括时间戳）完全不变。
- HTTP 验证：临时服务下四个旧页面、API 列表及四页详情均为 200，不支持的页面为 JSON 404；临时服务已停止。
- 验证边界：完整 CRUD 自动化测试使用项目现有的内存 SQLite；PostgreSQL 验证覆盖种子数据及实际 HTTP 读取，未对用户真实数据进行更新/删除测试。未进行浏览器视觉验证。
- 已知行为：删除 home/about 后原有 Inertia 页面会因原有 firstOrFail 返回 404，重新 POST 对应记录可恢复；删除后的前端体验按计划留到后续阶段处理。
- 本记录完成时阶段 2 至 6 尚未执行；未进行 Git 提交。回滚 API 还应撤销 bootstrap 中的 API 注册，并移除新增写入保护中间件。

### 阶段 2 实施记录（2026-10-07）

- 在 `frontend/` 中建立独立 Vue 3 + TypeScript + Vite 应用，使用自己的 `package.json`、`package-lock.json`、`tsconfig.json` 和 `vite.config.ts`，可以与 Laravel 根目录前端分别安装、启动和构建。
- 使用 Vue Router 的 HTML5 history 模式配置 `/`、`/about`、`/services` 和 `/contact`，导航使用 `RouterLink`，路由切换不触发完整页面刷新，并为各路由设置独立页面标题。
- 使用 Tailwind CSS v4 CSS-first 配置，新增响应式导航、页面壳和 `ContentState` 组件。该组件覆盖 loading、empty、error、ready 四种状态；阶段 2 的页面显示空数据占位，正式页面内容和 API 接入保留到阶段 3。
- 新增类型化 API Client，后端地址通过 `VITE_API_BASE_URL` 配置，默认使用 `http://127.0.0.1:8000`；请求层统一处理网络、非 JSON、无效 JSON 和非成功响应，Welcome Message API 层在运行时校验响应 envelope、page 和 content，避免直接信任未知 JSON。
- 独立前端未引用 Inertia、`laravel-vite-plugin` 或 Laravel 的 `resources/js`；根目录现有 Laravel/Inertia 前端未修改或删除。
- `npm install` 安装 62 个包，审计结果为 0 个漏洞。
- `npm run typecheck` 通过；`npm run build` 通过，生成 `dist/index.html`、CSS 和 JavaScript 生产产物。
- 开发服务器在 `127.0.0.1:15173` 临时启动；四个路由直接访问均返回 200，浏览器中从 About 切换到 Services 后 URL 和标题正确，刷新 Services 后仍正常显示，浏览器控制台无警告或错误。
- 回归验证：Laravel 完整测试仍为 60 项 / 307 个断言通过；根目录现有 Inertia 前端的 TypeScript 检查和生产构建仍通过。根目录构建只有既有的可选 `fontaine` 字体回退优化提示，无构建错误。
- 本记录完成时阶段 3 至 6 尚未执行。阶段 2 已在用户确认后提交为 `6383df6`；回滚时可单独撤销该提交，不影响现有 Laravel/Inertia 应用。

### 阶段 3 实施记录（2026-10-07）

- 新增共享 `WelcomeMessagePage` 组件，由四个 View 分别传入 `home`、`about`、`services`、`contact` 及对应英文标题；Home 保留原页面的 `Hello Laravel + Vue` 标题。
- 四页在挂载时通过 Laravel `/api/v1/welcome-messages/{page}` 获取内容；请求禁用缓存，路由离开时取消未完成请求，重试时也会取消前一请求，避免过期响应覆盖当前状态。
- API 层继续把响应当作 `unknown` 处理，验证 envelope、page 和 content 后才返回类型化数据，并额外确认响应 page 与请求 page 一致。
- 页面提供加载骨架、404/空内容状态、API/网络/响应格式错误状态以及 `Try again` 恢复操作；未加入新增、修改或删除界面，CRUD UI 仍属于阶段 4。
- 所有页面和状态文案保持英文；导航继续使用 Vue Router 的 `RouterLink`，独立前端未引入 Inertia 或 Laravel 专用前端依赖。
- 真实数据验证：Home 显示 `Hello from PostgreSQL!`，About 显示数据库当前值 `Welcome to About!from PostgreSQL`，Services 和 Contact 显示各自数据库内容。未修改这些 PostgreSQL 记录。
- 本地 CORS 当前按 Laravel 默认配置对 API 来源返回 `Access-Control-Allow-Origin: *`，因此独立前端可完成阶段 3 读取；按计划在阶段 5 收紧为配置过的前端来源。
- 浏览器验证：四页客户端导航后 URL、标题和内容正确；直接刷新 Contact 后仍读取成功；关闭 Laravel 后显示明确 API 不可用提示与重试按钮，恢复 Laravel 后重试成功；恢复后浏览器控制台无错误。
- 最终回归：独立前端 `npm run typecheck` 和 `npm run build` 通过；Laravel 60 项测试 / 307 个断言通过；根目录 Inertia 前端 TypeScript 检查和生产构建通过。根目录构建只有既有的可选 `fontaine` 提示。
- 阶段 3 已在用户确认后提交为 `5e03807`；阶段 4 至 6 在本记录完成时尚未执行。回滚时仍可继续把访问流量指向现有 Laravel/Inertia 前端。

### 阶段 4 实施记录（2026-10-08）

- 独立 SPA 的四个页面均接入新增、查询、修改和删除操作。已有内容显示 `Edit content` 和 `Delete content`；缺少记录时显示空状态和 `Create content`，删除成功后可立即重新创建。
- 编辑和新增共用可见表单。前端拒绝空白内容和超过 255 个字符的内容，显示字符计数；Laravel 请求校验继续作为后端边界，422 字段错误会显示在对应输入框下方。
- API Client 新增 POST、PATCH 和 DELETE 方法，继续验证响应 envelope、page 和 content；写入期间禁用重复操作，并取消过期请求，成功后直接更新当前页面状态，无需完整刷新。
- 删除采用两步界面确认：首次点击仅显示确认区域，只有再次点击 `Delete permanently` 才发送 DELETE；也可取消并恢复正常页面。
- 临时安全策略已经落实：开发模式默认允许写入，生产构建默认隐藏所有写入控件；只有显式设置 `VITE_WRITES_ENABLED=true` 才显示。Laravel 的本地/测试写入中间件仍会在其他环境返回 JSON 403，前端也会显示明确的禁用提示。
- 新增 Vitest、Vue Test Utils 和 jsdom，覆盖加载、查询、创建、修改、删除确认、空白/超长输入、后端 422、生产 403、响应校验和只读模式，共 2 个测试文件、15 项测试。
- 浏览器端到端验证使用一次性 SQLite 数据库和临时端口：删除 Contact 后显示空状态，重新创建成功，空白修改被前端拒绝，正常修改立即显示新内容且未刷新页面；生产后端的 PATCH 返回 403，生产前端不显示编辑或删除控件。临时服务和数据库文件均已清理，真实 PostgreSQL 未被修改。
- 最终回归：独立前端 `npm test` 为 15 项通过，`npm run typecheck` 和默认生产构建通过；Laravel 为 60 项测试 / 307 个断言通过；根目录 Inertia 前端的 TypeScript 检查和生产构建通过。根目录构建只有既有的可选 `fontaine` 提示，`git diff --check` 通过。
- 依赖安装生成锁文件后，标准 `npm ci --dry-run` 可通过，审计结果为 0 个漏洞。本机 Node.js 23 不在 Vitest 4 支持的 engine 范围内，当前测试仍通过；阶段 5 已计划把本地开发、CI 和生产构建版本统一固定为 Node.js 22 LTS。
- 阶段 4 已在用户确认后提交为 `88cfd03`；阶段 5 和阶段 6 尚未执行。回滚时可撤销该提交，继续使用阶段 3 的只读 SPA。

### 阶段 5 实施记录（2026-10-08）

- 发布 Laravel `config/cors.php`，API 不再使用默认通配符来源。`FRONTEND_URL` 指定唯一允许的浏览器来源，本地默认值为 `http://localhost:5173`。
- `.env.example` 新增 `FRONTEND_URL`；独立前端继续通过 `VITE_API_BASE_URL` 指向 API，并通过 `VITE_WRITES_ENABLED` 控制写入界面。
- `frontend/.nvmrc` 固定 Node.js 22，`frontend/package.json` 的 `engines.node` 固定为 `22.x`，`frontend/.npmrc` 启用 `engine-strict`。Node.js 23 会明确返回 `EBADENGINE`，避免继续使用 Vitest 4 不支持的运行时。
- README 已记录前后端分别安装、启动、测试和部署的命令，以及开发和生产环境变量。前端只部署 `frontend/dist/`，静态服务器需要回退到 `index.html`；API 部署不需要执行 npm 命令。
- 新增 CORS Feature Test。配置来源的预检响应声明该来源；其他来源不会得到与请求来源匹配的 `Access-Control-Allow-Origin`，浏览器因此拒绝跨域访问。
- Node.js 22.23.3 验证：`npm ci`、15 项 Vitest 测试、`npm run typecheck`、`npm run build` 和依赖审计均通过，未出现 `EBADENGINE`，审计为 0 个漏洞。
- 独立运行验证：Laravel 临时运行于 `127.0.0.1:18006`，Vue Vite 临时运行于 `localhost:5173`；`/` 与 `/contact` 均返回 200。浏览器成功读取 PostgreSQL 的 Home 内容并显示写入控件，控制台无警告或错误。
- 后端实际 CORS 预检为 204，并只声明 `http://localhost:5173`；API 实际读取返回 Home 数据。临时前后端服务已停止。
- 最终回归：Laravel 61 项测试 / 315 个断言通过；Pint 和 `git diff --check` 通过；旧 Inertia 前端生产构建通过，仅保留既有的可选 `fontaine` 提示。Laravel CORS 配置可正常缓存，验证后已清除测试缓存。
- 实际生产域名和 OpenAPI 生成时机仍按第 10 节保留为后续决定。阶段 5 已在用户确认后提交为 `a08ea2b`；阶段 6 在本记录完成时尚未执行。

### 阶段 6 实施记录（2026-10-08）

- Composer 已移除 `inertiajs/inertia-laravel`；`bootstrap/app.php` 不再注册 Web 路由或 Inertia middleware，只保留 API、Console、健康检查和默认 middleware 初始化。
- 删除 `HandleInertiaRequests`、`routes/web.php`、旧 Inertia Feature Test、Blade 根模板，以及 `resources/css` 和 `resources/js` 中的旧 Vue 页面与入口。
- 保留空的 `resources/views/.gitkeep`，因为 Laravel `php artisan optimize` 的 view cache 仍要求该目录存在；目录中没有 Blade 业务页面。
- 删除根目录 `package.json`、`package-lock.json`、`.npmrc`、`tsconfig.json` 和 `vite.config.js`。Laravel 后端安装、开发和生产部署不再需要 Node.js；Composer setup 不再执行 npm。
- 删除本地根目录 `node_modules`、`public/build`、Inertia devtools 和已编译 Blade 缓存。独立前端的源码、依赖和 `frontend/dist/` 不受影响。
- README 和 ARCHITECTURE 已改为最终前后端分离结构，不再把 Inertia 或 Laravel 内嵌 Vue 描述为当前运行方式。
- 路由检查只显示 5 条 `/api/v1/welcome-messages` 资源路由；Laravel 根路径 `/` 返回 404，健康检查 `/up` 返回 200，API 实际读取 PostgreSQL 数据正常。
- 浏览器验证：独立 SPA Home 和 About 正常读取 PostgreSQL 内容，Vue Router 导航成功，写入控件存在，控制台无警告或错误。临时 Laravel 和 Vite 服务已停止。
- 新增 API-only 边界测试，固定 `/`、`/about`、`/services` 和 `/contact` 在 Laravel 后端均返回 404，防止旧页面路由被误加回来。
- 最终回归：Laravel 58 项测试 / 282 个断言通过；Composer 严格校验和离线安装 dry-run 通过；`php artisan optimize` 全部成功并在验证后清理缓存；Pint 通过；Node.js 22 下独立前端 15 项测试、类型检查和生产构建通过；`git diff --check` 通过。
- 阶段 6 尚未进行 Git 提交。用户确认后应以独立提交保存，便于整体回滚删除操作。
