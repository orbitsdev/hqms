# MySQL Database & User Setup

Guide for creating MySQL databases, users, and managing privileges.

## Quick Setup (Copy-Paste Ready)

```sql
-- Connect to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE hqms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with password
CREATE USER 'caretime_user'@'localhost' IDENTIFIED BY 'caretime_password';

-- Grant all privileges on the database
GRANT ALL PRIVILEGES ON hqms.* TO 'caretime_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

## Step-by-Step Commands

### 1. Connect to MySQL

As root (no password on fresh install):
```bash
sudo mysql
```

Or with password:
```bash
mysql -u root -p
```

### 2. Create Database

```sql
CREATE DATABASE hqms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Verify:
```sql
SHOW DATABASES;
```

### 3. Create User

Basic syntax:
```sql
CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';
```

Example:
```sql
CREATE USER 'caretime_user'@'localhost' IDENTIFIED BY 'caretime_password';
```

### 4. Grant Privileges

Grant all privileges on specific database:
```sql
GRANT ALL PRIVILEGES ON hqms.* TO 'caretime_user'@'localhost';
```

Apply changes:
```sql
FLUSH PRIVILEGES;
```

### 5. Verify User & Privileges

Check user exists:
```sql
SELECT User, Host FROM mysql.user;
```

Check user privileges:
```sql
SHOW GRANTS FOR 'caretime_user'@'localhost';
```

## Common Operations

### Change User Password

```sql
ALTER USER 'caretime_user'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;
```

### Delete User

```sql
DROP USER 'caretime_user'@'localhost';
```

### Delete Database

```sql
DROP DATABASE hqms;
```

### Revoke Privileges

```sql
REVOKE ALL PRIVILEGES ON hqms.* FROM 'caretime_user'@'localhost';
FLUSH PRIVILEGES;
```

## Privilege Types

| Privilege | Description |
|-----------|-------------|
| `ALL PRIVILEGES` | Full access to database |
| `SELECT` | Read data |
| `INSERT` | Add new data |
| `UPDATE` | Modify existing data |
| `DELETE` | Remove data |
| `CREATE` | Create tables |
| `DROP` | Delete tables |
| `INDEX` | Create/drop indexes |
| `ALTER` | Modify table structure |

### Grant Specific Privileges Only

```sql
-- Read-only user
GRANT SELECT ON hqms.* TO 'readonly_user'@'localhost';

-- Read and write only
GRANT SELECT, INSERT, UPDATE, DELETE ON hqms.* TO 'app_user'@'localhost';
```

## Remote Access (If Needed)

By default, users with `'localhost'` can only connect from the server itself.

### Allow from specific IP:
```sql
CREATE USER 'caretime_user'@'192.168.1.100' IDENTIFIED BY 'caretime_password';
GRANT ALL PRIVILEGES ON hqms.* TO 'caretime_user'@'192.168.1.100';
```

### Allow from any IP (NOT recommended for production):
```sql
CREATE USER 'caretime_user'@'%' IDENTIFIED BY 'caretime_password';
GRANT ALL PRIVILEGES ON hqms.* TO 'caretime_user'@'%';
```

**Note:** You also need to edit MySQL config to allow remote connections:
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Change: bind-address = 0.0.0.0
sudo systemctl restart mysql
```

## Laravel .env Configuration

After creating the database and user, update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hqms
DB_USERNAME=caretime_user
DB_PASSWORD=caretime_password
```

## Test Connection

From command line:
```bash
mysql -u caretime_user -p hqms
# Enter password when prompted
```

If successful, you'll see:
```
mysql>
```

From Laravel:
```bash
cd /var/www/hqms
php artisan migrate:status
```

## Troubleshooting

### Access Denied Error

```
ERROR 1045 (28000): Access denied for user 'caretime_user'@'localhost'
```

**Fix:** Check password and privileges:
```sql
-- As root
ALTER USER 'caretime_user'@'localhost' IDENTIFIED BY 'caretime_password';
GRANT ALL PRIVILEGES ON hqms.* TO 'caretime_user'@'localhost';
FLUSH PRIVILEGES;
```

### Can't Connect to MySQL Server

```bash
# Check if MySQL is running
sudo systemctl status mysql

# Start if not running
sudo systemctl start mysql
```

### Database Doesn't Exist

```
ERROR 1049 (42000): Unknown database 'hqms'
```

**Fix:** Create the database first:
```sql
CREATE DATABASE hqms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Security Best Practices

1. **Use strong passwords** - At least 16 characters with mixed case, numbers, symbols
2. **Don't use root for applications** - Always create a dedicated user
3. **Limit privileges** - Only grant what's needed
4. **Use localhost** - Don't allow remote access unless absolutely necessary
5. **Regular backups** - See [07-MAINTENANCE.md](./07-MAINTENANCE.md)

## Quick Reference

```sql
-- Show all databases
SHOW DATABASES;

-- Show all users
SELECT User, Host FROM mysql.user;

-- Show current user
SELECT USER();

-- Show privileges for current user
SHOW GRANTS;

-- Use specific database
USE hqms;

-- Show tables in current database
SHOW TABLES;
```
