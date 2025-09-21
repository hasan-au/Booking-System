# 💄 Salon Booking System

A modern, feature-rich salon booking management system built with Laravel 11, Inertia.js, Vue 3, and Filament v4. This application provides a seamless booking experience for customers and a powerful admin panel for salon management.

## 🚀 Features

### 🎨 Customer Features
- **Modern Booking Interface**: Intuitive multi-step booking process with real-time availability checking
- **Service Selection**: Browse available salon services with pricing and duration
- **Staff Selection**: Choose preferred stylists/employees with their profiles and ratings
- **Time Slot Management**: Real-time availability checking with 15-minute intervals
- **Responsive Design**: Fully responsive design built with Tailwind CSS
- **Booking Confirmations**: Automated email notifications for booking confirmations

### 👩‍💼 Admin Features (Filament v4)
- **Comprehensive Dashboard**: Modern admin panel powered by Filament v4
- **Employee Management**: Manage staff profiles, work hours, and service assignments
- **Service Management**: Create and manage services with pricing and duration
- **Booking Management**: View, edit, and manage all bookings with status tracking
- **Schedule Management**: Handle employee breaks and days off
- **Testimonial Management**: Manage customer testimonials and reviews

### 🔧 Technical Features
- **Smart Availability Engine**: Intelligent scheduling that considers work hours, breaks, and existing bookings
- **Overlap Prevention**: Robust booking conflict detection and prevention
- **Status Management**: Comprehensive booking status workflow (Pending → Confirmed → Completed/Cancelled)
- **Email Notifications**: Automated notifications for customers and administrators
- **UUID Generation**: Secure booking identification system
- **Timezone Support**: Full timezone handling for accurate scheduling
- **Database Optimization**: Efficient queries with proper indexing and relationships

## 🛠 Tech Stack

### Backend
- **Laravel 11**: Latest Laravel framework with modern PHP 8.2+ features
- **MySQL/SQLite**: Flexible database support with migrations and seeders
- **Spatie Period**: Advanced date period handling for scheduling
- **Laravel Sanctum**: API authentication (if needed)
- **Laravel Breeze**: Authentication scaffolding
- **Queue System**: Background job processing for notifications

### Frontend
- **Inertia.js 2.0**: Modern SPA experience without API complexity
- **Vue 3**: Reactive frontend framework with Composition API
- **Tailwind CSS**: Utility-first CSS framework for rapid UI development
- **Vue Datepicker**: Advanced date/time selection components
- **Vue Toastification**: Beautiful toast notifications
- **Ziggy**: Laravel route generation for frontend

### Admin Panel
- **Filament v4**: Modern PHP admin panel with rich components
- **Resource Management**: CRUD operations with advanced filtering
- **Form Builder**: Dynamic form generation with validation
- **Table Builder**: Advanced data tables with sorting and filtering

### Development & Testing
- **Pest PHP**: Modern testing framework with elegant syntax
- **Laravel Pint**: Code style fixing
- **Laravel Pail**: Log viewer
- **Vite**: Modern build tool for assets
- **Laravel Sail**: Docker development environment

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL/SQLite database
- Git

## 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/salon-booking-system.git
   cd salon-booking-system
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database configuration**
   
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=salon_booking
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

   Or use SQLite (already configured):
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/database/database.sqlite
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Create storage symlink**
   ```bash
   php artisan storage:link
   ```

8. **Build assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

## 🚀 Running the Application

### Development Environment

Start all services concurrently:
```bash
composer run dev
```

This command starts:
- Laravel development server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Log viewer (`php artisan pail`)
- Vite development server (`npm run dev`)

### Individual Services

**Laravel Server:**
```bash
php artisan serve
```

**Frontend Assets (Development):**
```bash
npm run dev
```

**Queue Worker:**
```bash
php artisan queue:work
```

**Log Monitoring:**
```bash
php artisan pail
```

## 👤 Admin Panel Access

Create an admin user:
```bash
php artisan make:filament-user
```

Access the admin panel at: `http://localhost:8000/admin`

## 🧪 Testing

This project uses **Pest PHP** for testing with comprehensive test coverage.

**Run all tests:**
```bash
php artisan test
```

**Run specific test suites:**
```bash
# Feature tests
php artisan test --testsuite=Feature

# Unit tests  
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

**Test Categories:**
- **Booking Management**: Core booking creation, updates, and validation
- **Availability Engine**: Time slot generation and conflict detection
- **Employee Scheduling**: Work hours, breaks, and days off
- **Overlap Prevention**: Concurrent booking conflict resolution
- **Status Management**: Booking workflow and state transitions

## 📊 Database Schema

### Core Models

**Users**: Customer authentication and profiles
**Employees**: Staff management with work schedules
**Services**: Salon services with pricing and duration
**Bookings**: Appointment management with status tracking
**EmployeeDayOffs**: Manage staff availability
**EmployeeBreaks**: Handle break times and lunch periods
**Testimonials**: Customer reviews and feedback

### Key Relationships
- Employees ↔ Services (Many-to-Many)
- Bookings → Employee, Service, User (Belongs-To)
- Employee → EmployeeDayOffs, EmployeeBreaks (Has-Many)

## 📧 Email Configuration

Configure email settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yoursalon.com
MAIL_FROM_NAME="${APP_NAME}"
```

Set admin email for notifications:
```env
ADMIN_EMAIL=admin@yoursalon.com
```

## 🎯 API Endpoints

### Public Routes
- `GET /` - Home page with booking interface
- `GET /check-availability` - Real-time availability checking
- `POST /book-appointment` - Create new booking

### Authentication Routes
- `GET /dashboard` - User dashboard (auth required)
- Profile management routes

### Admin Routes
- `/admin/*` - Filament admin panel routes

## 🔐 Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Input Validation**: Comprehensive request validation
- **SQL Injection Prevention**: Eloquent ORM with parameter binding
- **XSS Protection**: Automatic output escaping
- **Rate Limiting**: API endpoint protection
- **Secure Headers**: Security headers configuration

## 🎨 Customization

### Frontend Styling
- Modify `tailwind.config.js` for design system changes
- Update Vue components in `resources/js/Components/`
- Customize layouts in `resources/js/Layouts/`

### Admin Panel
- Extend Filament resources in `app/Filament/Resources/`
- Customize forms and tables per resource
- Add custom pages and widgets

### Business Logic
- Availability logic in `app/Services/AvailabilityService.php`
- Booking management in `app/Services/BookingService.php`
- Custom validation rules in `app/Rules/`

## 📝 Configuration

### Timezone Setup
```env
APP_TIMEZONE=your_timezone
```
```php
class AvailabilityService
{
    const CURRENT_TIMEZONE = 'Australia/Melbourne';
}
```
### Booking Settings
Modify availability service for:
- Slot intervals (default: 15 minutes)
- Lead time requirements
- Maximum booking advance time
- Break time handling

### Notification Settings
Configure in `config/mail.php` and notification classes:
- Customer booking confirmations
- Admin notifications
- Reminder emails (extend as needed)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use conventional commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The elegant PHP framework
- [Filament](https://filamentphp.com) - Beautiful admin panels for Laravel
- [Inertia.js](https://inertiajs.com) - The modern monolith
- [Vue.js](https://vuejs.org) - The progressive JavaScript framework
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Pest PHP](https://pestphp.com) - Elegant testing framework

## 📞 Support

For support and questions:
- Create an issue in the GitHub repository
- Check the documentation and tests for examples
- Review the Filament and Laravel documentation

---

**Built with ❤️ using Laravel, Inertia.js, Vue 3, and Filament v4**
