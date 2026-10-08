# 生命保险 Demo 开发计划

## 1. 目标

把当前 Laravel API + Vue SPA 项目改造成一个可本地运行的生命保险保单管理 Demo。

只实现一条完整业务链路：

```text
保单列表 -> 新建保单 -> 保单详情 -> 编辑保单 -> 删除保单
```

## 2. 本次范围

- 保单列表
- 保单详情
- 新建保单
- 编辑保单
- 删除保单
- 前后端输入校验
- 空列表、加载中、保存失败和删除失败状态
- PostgreSQL 数据持久化
- 最小自动化测试和浏览器验证

## 3. 暂不实现

- 登录、用户和权限控制
- 投保人、被保险人、受益人的独立档案
- 保险产品配置
- 核保、理赔、缴费记录和保单变更历史
- 附件上传
- 通知、报表和导出
- 搜索、筛选、分页和批量操作
- 软删除和审计日志

这些功能没有当前需求，先不预留抽象层。

## 4. 最小数据模型

只新增一张 `life_insurance_policies` 表。人员信息直接保存在保单中，不拆分关联表。

| 字段 | 类型 | 规则 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `policy_number` | varchar(50) | 必填、唯一 |
| `policyholder_name` | varchar(100) | 投保人姓名，必填 |
| `insured_name` | varchar(100) | 被保险人姓名，必填 |
| `insured_birth_date` | date | 必填，不得晚于今天 |
| `beneficiary_name` | varchar(100) nullable | 受益人姓名 |
| `coverage_amount` | decimal(15,2) | 保额，必须大于 0 |
| `premium_amount` | decimal(15,2) | 保费，必须大于 0 |
| `currency` | char(3) | 默认 `CNY` |
| `status` | varchar(20) | `draft`、`active`、`expired`、`cancelled` |
| `effective_date` | date | 生效日，必填 |
| `expiry_date` | date nullable | 失效日，不得早于生效日 |
| `notes` | text nullable | 备注，最多 1000 字符 |
| `created_at` / `updated_at` | timestamp | Laravel 时间戳 |

删除采用硬删除。Demo 不增加软删除字段。

## 5. API

```text
GET    /api/v1/life-insurance-policies
POST   /api/v1/life-insurance-policies
GET    /api/v1/life-insurance-policies/{policy}
PUT    /api/v1/life-insurance-policies/{policy}
PATCH  /api/v1/life-insurance-policies/{policy}
DELETE /api/v1/life-insurance-policies/{policy}
```

成功响应继续使用现有格式：

```json
{
  "data": {
    "id": 1,
    "policy_number": "POL-0001",
    "status": "active"
  }
}
```

校验失败返回 422 和字段错误；不存在的保单返回 JSON 404。

## 6. 前端页面

| 路由 | 页面 | 最小功能 |
| --- | --- | --- |
| `/policies` | 保单列表 | 显示保单号、投保人、被保险人、保额、状态、生效日和操作 |
| `/policies/create` | 新建保单 | 填写字段并创建 |
| `/policies/:id` | 保单详情 | 显示全部字段，提供编辑和删除入口 |
| `/policies/:id/edit` | 编辑保单 | 修改并保存 |

删除前显示确认操作。删除成功后返回列表。列表为空时显示新建入口。

## 7. 实施阶段

### 阶段 A：数据库和 API

- 新增 Migration、Model、Factory、Seeder。
- 新增 API Controller、Form Request、Resource 和版本化路由。
- 写入 3 条示例保单。
- 增加 CRUD、校验、唯一性和 404 Feature Test。

验收：API CRUD 测试通过，种子数据可重复执行。

### 阶段 B：Vue 页面

- 用保单导航替换当前 Welcome Message 示例页面。
- 新增列表、详情、新建和编辑页面。
- 新增类型化 API Client。
- 复用现有加载、错误和表单交互方式。

验收：四个路由可直接访问和刷新，TypeScript 检查与生产构建通过。

### 阶段 C：端到端确认

- 独立启动 Laravel 和 Vue。
- 创建一张保单。
- 在列表和详情中确认数据。
- 编辑保单并确认页面立即更新。
- 删除保单并确认返回列表。
- 验证无效输入同时被前端和后端拒绝。

验收：浏览器完成完整 CRUD，控制台无错误，Laravel 和前端测试全部通过。

### 阶段 D：清理示例代码

- 删除 `WelcomeMessage` 模型、API、迁移、种子逻辑和前端页面。
- 清理无用测试和文档引用。
- 保留独立部署、CORS 和生产写入保护。

验收：仓库不再包含旧欢迎页业务代码，完整回归通过。

## 8. 完成标准

- 本地可分别启动 Laravel API 和 Vue SPA。
- 保单 CRUD 全部可用。
- 列表和详情显示 PostgreSQL 数据。
- 校验错误清楚显示。
- 删除需要确认。
- 生产环境继续拒绝未认证写请求。
- 后端测试、前端测试、类型检查和生产构建全部通过。
- README 更新为生命保险 Demo 的安装和启动说明。

## 9. 默认决定

- 界面默认使用简体中文。
- 默认币种为 `CNY`，数据库保留三字符币种字段。
- 保单号由用户输入并保证唯一。
- 删除采用硬删除。
- 列表按创建时间倒序，不做分页。
- 延续当前同仓库、独立部署的 Laravel API + Vue SPA 架构。

这些默认值不会阻塞开发。需要其他语言、币种或删除策略时，应在阶段 A 开始前调整。
