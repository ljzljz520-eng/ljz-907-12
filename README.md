# 影格 (CineVault)

**影格 (CineVault)** 是一款基于现代 Web 技术栈构建的高品质电影数据管理系统。它专注于极致的视觉展示与深度元数据管理，为影迷提供沉浸式的观影资料探索体验。

## 🛠 技术栈

- **前端**: Vue 3 + Tailwind CSS v4 + Lucide Icons + Headless UI
- **后端**: Laravel 10 + PHP 8.2 + Eloquent ORM
- **数据库**: MySQL 8.0
- **容器化**: Docker Compose

## 🚀 快速开始

### 1. 启动服务

```bash
cd cinevault
docker compose up -d --build
```

系统将自动：
- 启动前端、后端、数据库及 Nginx 服务
- 执行数据库迁移 (`migrate`)
- 从 `movies_crawled.csv` 导入初始电影数据 (`seed`)
- 初始化应用配置并预热（确保 CORS 等配置已加载）

**重要提示**：请等待后端容器完全启动，看到以下日志后再访问网页：
- `fpm is running, pid xxx`
- `ready to handle connections`

这些日志表示：
- ✅ 数据库迁移已完成
- ✅ 初始数据已导入完成
- ✅ 应用配置已初始化
- ✅ PHP-FPM 已准备好处理请求

可通过以下命令查看启动日志：

```bash
docker compose logs -f backend
```

等待看到类似以下完整日志后再访问：
```
✓ CORS configuration verified: 1 allowed origin(s)
✓ Application warmed up - CORS config loaded: 1 origin(s)
Starting PHP-FPM...
fpm is running, pid xxx
ready to handle connections
```

**注意**：如果过早访问网页（在初始化数据完成前），可能会遇到首次请求的跨域错误。

### 2. 访问服务

- **Web UI**: [http://localhost:3000](http://localhost:3000)
- **Backend API**: [http://localhost:8000](http://localhost:8000)
- **Database**: `localhost:3306` (库名: `1` / 用户: `1` / 密码: `1`)

## 📊 数据管理

### 初始数据

系统首次启动时会自动从 `storage/movies_crawled.csv` 导入电影数据。

### CSV 批量导入

支持通过 Web 界面上传 CSV 文件批量导入电影数据：

- 支持 19 个字段的完整数据导入
- 自动数据清理和验证（UTF-8 编码、字段长度、评分范围等）
- 基于"标题+年份"智能去重
- 流式处理，支持大文件导入

**测试导入**：项目根目录提供了 `example_movies_crawled.csv` 测试文件，您可以通过 Web 界面上传此文件来测试 CSV 导入功能。

## 🎨 核心特性

- **独立影片详情页** (`/movies/:id`)：包含剧情简介、主创阵容（导演/编剧/主演）、放映年份、适合人群、来源单位、评分与外链、获奖情况，以及同类型/同年代的相关推荐
- **海报占位兜底**：无海报或图片加载失败时统一显示占位卡片，不会出现破图
- **分享与返回**：详情页底部提供「返回列表」（保留搜索词与分页位置）与「复制分享链接」
- **上下架管理**：后台 (`/admin`) 可将影片下架；下架影片不会出现在公开列表中，直接访问其链接也返回 404
- **实时智能搜索**: 支持对片名、导演、演员进行实时筛选（带防抖优化，状态同步到 URL）
- **图片代理**: 自动处理图片 CORS 和 403 错误，确保海报正常显示
- **响应式暗黑布局**: 完美适配移动端与桌面端
- **稳定分页**: 支持大数据量的稳定分页展示

## 🔐 后台管理

访问 [http://localhost:3000/admin](http://localhost:3000/admin) 登录（默认账号见下），可搜索影片并执行上架/下架操作：

- 默认账号：`admin@cinevault.local`
- 默认密码：`admin123456`（由 `AdminUserSeeder` 创建，生产环境请修改）

下架后的影片：
- 不出现在公开列表 `GET /api/movies`
- 详情接口 `GET /api/movies/{id}` 与不存在的影片一样返回 **404**，不对外暴露其存在性
- 不会出现在其他影片的「相关推荐」中

## 📁 目录结构

```
cinevault/
├── frontend/          # Vue 3 前端源码
├── backend/           # Laravel 后端源码
│   └── storage/       # 存储目录（包含 movies_crawled.csv）
├── nginx/             # Nginx 配置
├── example_movies_crawled.csv # CSV 测试文件（用于测试导入功能）
└── docker-compose.yml # Docker 编排配置
```

## 🔧 开发说明

### 重新导入数据

如果需要重新导入初始数据：

```bash
# 进入后端容器
docker exec -it cinevault_backend bash

# 清空并重新导入数据
php artisan db:seed --class=MovieSeeder
```

### 数据库迁移

```bash
docker exec -it cinevault_backend php artisan migrate
```

## 📝 数据字段说明

CSV 文件应包含以下字段（按顺序）：

- `title` - 原片名
- `translated_title` - 译名
- `year` - 年代
- `release_date` - 上映日期
- `country` - 产地
- `language` - 语言
- `runtime` - 片长
- `director` - 导演
- `writer` - 编剧
- `actors` - 主演
- `genre` - 类别
- `rating` - 豆瓣评分（0.0-10.0）
- `imdb_rating` - IMDb 评分
- `imdb_link` - IMDb 链接
- `douban_link` - 豆瓣链接
- `poster_url` - 封面海报 URL
- `description` - 剧情简介
- `awards` - 获奖情况
- `screenshots` - 电影截图 URL（逗号分隔）
- `target_audience` - 适合人群（可选，如：家庭 / 青少年 / 成人，多个用逗号分隔）
- `source_organization` - 来源单位（可选，如：XX电影资料馆）
- `is_published` - 上架状态（可选，默认上架；填 `0`/`下架`/`false`/`offline` 导入即为下架状态）

## 🐛 故障排除

- **图片无法显示**: 系统已集成图片代理功能，自动处理跨域问题
- **CSV 导入失败**: 检查文件编码是否为 UTF-8，确保必填字段（title, year）存在
- **数据库连接失败**: 确认 Docker 容器正常运行，检查 `.env` 配置
- **前端无法访问 (ERR_CONNECTION_REFUSED)**:
  - 确认前端容器正在运行：`docker compose ps frontend`
  - 检查前端日志：`docker compose logs frontend`
  - 等待 Vite 服务器完全启动（看到 `VITE ready` 和 `Local: http://localhost:3000/` 日志）
  - 如果使用 WSL2，尝试使用 `127.0.0.1:3000` 而不是 `localhost:3000`
  - 重启前端服务：`docker compose restart frontend`