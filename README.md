# Property Rental Management System (PRMS)

A comprehensive property rental management system built with PHP.

## Project Structure

```
prms/
├── app/
│   ├── controllers/    # Application controllers
│   ├── models/         # Data models
│   └── views/          # View templates
├── config/
│   └── database.php    # Database configuration
├── public/
│   └── index.php       # Application entry point
├── routes/
│   └── web.php         # Web routes definition
├── storage/            # File storage (uploads, logs, etc.)
├── tests/              # Unit and integration tests
└── README.md           # This file
```

## Getting Started

1. Configure your database settings in `config/database.php`
2. Set up your web server to point to the `public/` directory
3. Access the application through your web browser

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Development

This project follows the MVC (Model-View-Controller) architecture pattern.

- **Models**: Located in `app/models/` - Handle data and business logic
- **Views**: Located in `app/views/` - Handle presentation layer
- **Controllers**: Located in `app/controllers/` - Handle request/response logic

## License

Proprietary - All rights reserved
