# PHITSOL Partners Portal

PHITSOL INC. - Leading IT solutions provider platform with admin dashboard and partner management system.

## Overview

This is a comprehensive web application for PHITSOL INC., featuring:
- Admin dashboard for content and user management
- Partner portal with authentication system
- Blog management system
- Product catalog and purchase order management
- MySQL/MariaDB database backend

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB 10.6+
- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5
- **Package Manager**: Composer

## Requirements

- PHP >= 7.4
- MySQL/MariaDB 10.6+
- Apache 2.4+ (or compatible web server)
- Composer (for dependency management)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/devphitsol/phitsolver2.git
cd phitsolver2
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
- Copy `config.env.example` to `config.env` (if exists)
- Update `config.env` with your database credentials:
  - `MYSQL_HOST`
  - `MYSQL_DATABASE`
  - `MYSQL_USERNAME`
  - `MYSQL_PASSWORD`

4. Import database schema:
```bash
mysql -u username -p database_name < database/mysql_schema.sql
```

5. Set up admin account:
- Navigate to `/admin/setup_admin.php` in your browser
- Follow the setup wizard to create the first admin account

## Configuration

### Database Configuration

Edit `config.env` file with your database settings:

```env
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_DATABASE=your_database_name
MYSQL_USERNAME=your_username
MYSQL_PASSWORD=your_password
```

### Production (cPanel) Settings

For cPanel hosting environments:
- Database name format: `cpaneluser_dbname`
- Username format: `cpaneluser_dbuser`
- Host is usually `localhost` on cPanel servers

## Directory Structure

```
phitsolver2/
├── admin/           # Admin dashboard
├── app/             # Application core
│   ├── Config/      # Configuration classes
│   ├── Controllers/ # Controller classes
│   ├── Models/      # Model classes
│   └── Utils/       # Utility classes
├── public/          # Public-facing pages
├── config/          # Configuration files
├── database/        # Database schema and migrations
└── vendor/          # Composer dependencies
```

## Features

- **User Management**: Admin, super admin, employee, and business user roles
- **Blog System**: Create, edit, and manage blog posts
- **Product Catalog**: Product management and purchase orders
- **Partner Portal**: Business partner authentication and dashboard
- **Security**: Password hashing, JWT authentication, rate limiting

## Security Notes

⚠️ **Important**: 
- Never commit `config.env` file to version control
- Ensure sensitive files are excluded via `.gitignore`
- Use strong passwords for database and admin accounts
- Keep dependencies updated for security patches

## License

Copyright © PHITSOL INC. All rights reserved.

## Support

For support and inquiries, visit [phitsol.com](https://phitsol.com)

