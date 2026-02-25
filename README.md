# Inventory Management System

A modern inventory management system built with Laravel 12, featuring product management, sales tracking, expense management, journal entries, and comprehensive financial reporting.

## Features

### Product Management
- Create and manage products with purchase and sell prices
- Track opening stock quantities
- View product inventory levels

### Sales Management
- Record sales transactions with quantity and unit pricing
- Automatic calculation of subtotals, VAT, and discounts
- Track paid and due amounts
- Export sales data to CSV

### Inventory Transactions
- Track inventory movements (purchases, sales, opening stock)
- Record transaction dates and notes
- Automatic total amount calculations

### Expense Tracking
- Record and categorize business expenses
- Track expense amounts and dates
- View expense history

### Financial Reporting
- Generate comprehensive financial reports
- View journal entries for accounting purposes
- Export financial data to CSV
- Track profit and loss

### Journal Entries
- Double-entry bookkeeping system
- Support for debit and credit entries
- Link entries to sales and products
- Auto-generated entry numbers

### OAuth 2.0 Authentication
- Built-in OAuth 2.0 server for API authentication
- Support for authorization codes, refresh tokens, and access tokens

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- Laravel 12.x

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd inventory-ms
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure your database in `.env` file

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Install frontend dependencies:
   ```bash
   npm install
   npm run build
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

## Demo Data

Initialize demo data to test the application:

```
Visit: /inventory/init-demo
```

## Routes

| Route | Description |
|-------|-------------|
| `/inventory/dashboard` | Main dashboard |
| `/inventory/products` | Product management |
| `/inventory/sales` | Sales transactions |
| `/inventory/expense/create` | Create expense |
| `/inventory/journal-entries` | View journal entries |
| `/inventory/financial-report` | Financial reports |
| `/oauth/authorize` | OAuth authorization |
| `/oauth/token` | OAuth token endpoint |

## Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade Templates, Bootstrap
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Auth + OAuth 2.0

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
