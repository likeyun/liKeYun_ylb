## 使用 Docker 启动

- 1) 复制（可选）环境变量文件
  - 如需修改数据库密码/库名，在项目根创建 `.env`，例如：
```
MYSQL_ROOT_PASSWORD=rootpass
MYSQL_DATABASE=likeyun
MYSQL_USER=likeyun
MYSQL_PASSWORD=likeyunpass
```
  - 若不创建，则使用 docker-compose.yml 中的默认值。

- 2) 启动服务
```
docker compose up -d --build
```
服务启动后：
- 站点：http://localhost:8080
- phpMyAdmin：http://localhost:8081  （主机：db，用户/密码见 .env 或默认）

- 3) 首次安装
  - 浏览器访问 `http://localhost:8080/install/` 完成安装向导（填写上面数据库信息）
  - 安装完成后，登录后台：`http://localhost:8080/console/index.html`

- 4) 常用命令
```
# 查看日志
docker compose logs -f

# 进入 PHP 容器
docker compose exec php sh

# 重启服务
docker compose restart

# 停止并清理
docker compose down

# 停止并清理（包含数据库卷）
docker compose down -v
```

- 5) 数据持久化
  - MySQL 数据保存在命名卷 `db_data` 中，`down -v` 会清空。

- 6) Windows 提示
  - 首次启动可能较慢，请耐心等待 MySQL 初始化完成（查看 logs）。
  - 如需修改对外端口，可编辑 `docker-compose.yml` 中的 `8080:80`、`8081:80`、`3306:3306`。 