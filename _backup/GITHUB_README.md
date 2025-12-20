# Event Registration Module for Drupal 10

A fully custom Drupal 10 Event Registration System built using **Core APIs only**. This module demonstrates architecture with clean separation of concerns, comprehensive validation, and production-ready code.

## 🌟 Features

- **Dynamic AJAX Registration Form** - Cascading dropdowns (Category → Event Date → Event Name)
- **Event Management** - Create and manage events with registration windows
- **Email Notifications** - Automatic confirmation emails with optional admin notifications
- **CSV Export** - Memory-safe streaming export with Excel compatibility
- **International Support** - Unicode character validation for global names
- **Comprehensive Security** - XSS, SQL injection, and CSRF protection via Drupal Core
- **Admin Dashboard** - View, filter, and export registrations
- **Duplicate Prevention** - Multi-layer enforcement to prevent duplicate registrations

## 📋 Requirements

- Drupal 10.x
- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.3+

## 🚀 Quick Start

```bash
# Install module
cd /path/to/drupal/modules/custom
git clone https://github.com/yourusername/event_registration.git
drush en event_registration -y
drush cr

# Configure
# Visit: /admin/config/events/settings
# Create events: /admin/config/events/manage
# Public registration: /event/register
```

## 📖 Documentation

- **[Installation Guide](DEPLOYMENT.md)** - Complete deployment instructions
- **[Security Documentation](SECURITY.md)** - Security features and best practices
- **[User Guide](README.md)** - Comprehensive usage documentation

## 🏗️ Architecture

Built with clean architecture principles:

- **4-Layer Design** - Presentation, Service, Repository, Data
- **Service-Oriented** - Business logic in dedicated services
- **Repository Pattern** - Data access abstraction
- **Dependency Injection** - 100% DI, no static calls
- **PSR-4 Autoloading** - Modern PHP standards

## 🔒 Security

- ✅ XSS Protection (Twig auto-escaping)
- ✅ SQL Injection Prevention (PDO prepared statements)
- ✅ CSRF Protection (Form API tokens)
- ✅ Input Validation (Regex + length + type checks)
- ✅ Access Control (Permission-based routes)
- ✅ Security Headers (X-Frame-Options, X-Content-Type-Options)

## 📊 Code Quality

- **Drupal Coding Standards** - 100% compliant
- **Type Safety** - Comprehensive type hints
- **Error Handling** - Robust exception handling
- **Documentation** - Complete PHPDoc comments
- **No Dependencies** - Pure Drupal Core APIs

## 🎯 Use Cases

- Academic institutions managing event registrations
- Conference and seminar registration systems
- Workshop and training session management
- Cultural and sports event coordination
- Any scenario requiring structured event registration

## 📸 Screenshots

*(Add screenshots of your module in action)*

## 🤝 Contributing

Contributions are welcome! Please read our contributing guidelines before submitting pull requests.

## 📄 License

GPL-2.0-or-later

## 👨‍💻 Author

**Your Name**
- GitHub: [tanmaycooks](https://github.com/tanmaycooks)
- Email: ytanmay122005@gmail.com

## 🙏 Acknowledgments

Built following Drupal best practices and security guidelines.

## 📈 Stats

![Drupal](https://img.shields.io/badge/Drupal-10.x-0678BE?logo=drupal)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php)
![License](https://img.shields.io/badge/License-GPL--2.0-blue)
![Status](https://img.shields.io/badge/Status-Production--Ready-success)

---

**⭐ Star this repository if you find it useful!**
