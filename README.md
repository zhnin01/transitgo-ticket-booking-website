# TransitGo: Ticket Booking System

A train and bus ticket booking website. Passengers can search trips, pick a seat on a seat map, book and cancel. Admin staff can manage routes and bookings.

> **About this project:** the database design started as a university SQL project. The original university files are not included here. `database/schema.sql` recreates the tables and adds made-up demo data. The website (PHP API and JavaScript frontend) was built with the help of an AI assistant, and I ran, tested and debugged each step myself. It is a learning project: it runs locally and has no real users or payments.

<!-- Add screenshots here, for example: ![Seat map](docs/seat-map.png) -->

## Features
- **Search** routes by city and date, with live counts of booked seats
- **Seat map**: click a free seat; taken seats are greyed out
- **Accounts**: register, log in, edit details, change password
- **My bookings**: view and cancel your own bookings
- **Admin area**: dashboard stats, mark bookings as paid, cancel bookings, add, edit and deactivate routes

## Tech stack
- **Frontend:** HTML, CSS, JavaScript (`fetch` calls to the API, no framework)
- **Backend:** PHP 8 JSON API using PDO
- **Database:** MySQL, InnoDB tables with foreign keys (`passengers`, `routes`, `bookings`)
- **Run with:** XAMPP (Apache + MySQL)

## How it works
```
Browser (frontend/)  →  PHP API (backend/api/)  →  MySQL
   pages + JavaScript        rules + validation        passengers, routes, bookings
                ←  JSON  ←                    ←  rows
```
The browser never talks to the database. It calls the API, which checks the rules and then queries MySQL.

## Security and design choices
- Passwords are hashed with `password_hash()`; logins use PHP sessions
- All SQL uses prepared statements (protects against SQL injection)
- Admin actions are checked on the server, not just hidden in the page
- Input is validated (dates, seats, field lengths) and output is escaped
- Double booking is prevented by the database: a unique key on route, date and seat (cancelled seats are excluded)

## Setup (XAMPP on Windows)
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. In phpMyAdmin (`http://localhost/phpmyadmin`), create an empty database called `transport`.
3. Select it, open **Import**, and import `database/schema.sql`.
4. Copy this project folder into `C:\xampp\htdocs\` and name it `transitgo`.
5. If your MySQL root user has a password, set it in `backend/config.php`.
6. Open `http://localhost/transitgo/`.

**Demo login:** `ava@example.com` / `password123` (admin). All demo accounts use `password123`. These are for local demos only. Change them before putting the site anywhere public.

## Project structure
```
backend/      PHP API: config.php and api/*.php (login, routes, seats, book, admin and more)
frontend/     HTML pages, css/, js/
database/     schema.sql (tables, foreign keys, seat rule and made-up demo data)
```

## Limitations and next steps
- No automated tests or CI/CD yet (tested manually)
- No HTTPS, login attempt limits or CSRF tokens yet, so it is not ready for the public internet
- No real payments, password reset by email or deployment
