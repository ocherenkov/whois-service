# Whois Service Application

This is a Laravel-based application that provides a service to query WHOIS data for domain names. It fetches, parses, and caches WHOIS data for domains. The application uses standard Laravel functionalities and PHP features for processing.

---

## Features
- Fetch WHOIS records for a given domain.
- Parse WHOIS data for easy readability and processing.
- Cache WHOIS records to prevent repetitive querying and improve performance.
- Error handling for no WHOIS server or connection failures.

---

## Prerequisites

- **PHP**: Version 8.2 or later.
- **Laravel**: Version 11.42.1.
- **Database**: SQLite (pre-configured in the project).
- **Cache**: Database (pre-configured in the project).
- **Composer**: For dependency management.

---

## Installation

1. Clone the repository:
   ```bash
   git clone git@github.com:ocherenkov/whois-service.git
   cd whois-service
   ```

2. Install PHP dependencies using Composer:
   ```bash
   composer install
   ```

3. Set up the environment file:
   ```bash
   cp .env.example .env
   ```

   Modify `.env` as needed (e.g., configure database, cache driver, etc.).

4. Run database migrations:
   ```bash
   php artisan migrate
   ```

5. Start the application:
   ```bash
   php artisan serve
   ```

---

## Usage

### Querying WHOIS Data
To check the WHOIS data for a domain:
1. Use the `WhoisService` class method `lookup()` with a domain name as an input.
2. It fetches and parses the information, returning:
    - `raw`: Raw WHOIS data.
    - `parsed`: Parsed WHOIS information as an associative array.
    - `domain`: The domain name.

Example:
```php
$whoisService = new WhoisService();
$result = $whoisService->lookup('example.com');
print_r($result);
```

---

### Caching
- Results are cached automatically for 24 hours.
- Cached data avoids redundant requests, improving speed and reducing server load.

---

## Testing

This application uses PHPUnit for testing.

To run the test suite:
```bash
php artisan test
```

---

## Contact

Feel free to reach out if you have any questions or feedback:
- **Email**: [oleh.cherenkov@gmail.com](mailto:oleh.cherenkov@gmail.com)
- **GitHub Issues**: Open an issue in the repository!
