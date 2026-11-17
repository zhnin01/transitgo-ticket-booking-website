-- TransitGo: database schema and made-up demo data.
-- 1. In phpMyAdmin, create an empty database called `transport`.
-- 2. Select it, open the Import tab, and import this file.
-- All people, routes and bookings below are invented for demonstration.
-- Demo login for every account: password123   (admin: ava@example.com)

SET NAMES utf8mb4;

DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS passengers;
DROP TABLE IF EXISTS routes;

CREATE TABLE passengers (
  passenger_id    INT NOT NULL AUTO_INCREMENT,
  first_name      VARCHAR(50)  NOT NULL,
  last_name       VARCHAR(50)  NOT NULL,
  email           VARCHAR(100) NOT NULL,
  phone_no        VARCHAR(20)  NOT NULL,
  street_name     VARCHAR(100) NOT NULL,
  city            VARCHAR(20)  NOT NULL,
  postcode        VARCHAR(10)  NOT NULL,
  country         VARCHAR(20)  NOT NULL DEFAULT 'UK',
  date_registered DATE DEFAULT NULL,
  password_hash   VARCHAR(255) DEFAULT NULL,
  is_admin        TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (passenger_id),
  UNIQUE KEY uniq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE routes (
  route_id       INT NOT NULL AUTO_INCREMENT,
  start_city     VARCHAR(50) NOT NULL,
  end_city       VARCHAR(50) NOT NULL,
  distance       DECIMAL(6,2) DEFAULT NULL,
  time_taken     TIME DEFAULT NULL,
  transport_type VARCHAR(50) DEFAULT NULL,
  price          DECIMAL(8,2) DEFAULT NULL,
  departure_time TIME DEFAULT NULL,
  arrival_time   TIME DEFAULT NULL,
  route_status   VARCHAR(20) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (route_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookings (
  booking_id        INT NOT NULL AUTO_INCREMENT,
  passenger_id      INT NOT NULL,
  route_id          INT NOT NULL,
  travel_date       DATE NOT NULL,
  seat_number       VARCHAR(5) NOT NULL,
  booking_status    VARCHAR(20) NOT NULL DEFAULT 'Confirmed',
  booking_date      DATE DEFAULT NULL,
  payment_status    VARCHAR(20) DEFAULT NULL,
  payment_method    VARCHAR(20) DEFAULT NULL,
  total_amount      DECIMAL(8,2) DEFAULT NULL,
  luggage_count     INT DEFAULT 0,
  cancellation_date DATE DEFAULT NULL,
  -- NULL when cancelled, so a cancelled seat can be booked again
  active_seat VARCHAR(5) GENERATED ALWAYS AS (IF(booking_status = 'Cancelled', NULL, seat_number)) STORED,
  PRIMARY KEY (booking_id),
  UNIQUE KEY uniq_active_seat (route_id, travel_date, active_seat),
  KEY idx_passenger (passenger_id),
  CONSTRAINT fk_bookings_passenger FOREIGN KEY (passenger_id) REFERENCES passengers (passenger_id),
  CONSTRAINT fk_bookings_route FOREIGN KEY (route_id) REFERENCES routes (route_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo data (invented). Every account's password is: password123
INSERT INTO passengers (passenger_id, first_name, last_name, email, phone_no, street_name, city, postcode, country, date_registered, password_hash, is_admin) VALUES
(1, 'Ava',   'Morgan',  'ava@example.com',   '07700900101', '12 Rowan Close',   'Bristol',    'BS8 1AA',  'UK', '2026-09-01', '$6$Tr4nsitG0Salt$JbMDsX9m/InadeF86BRH8s/KIA79.QIbQAW5WJkGnBDfQB3WOIuJhYW0d6CD75gdDqqg1uWHjxyxfhmmyD5Y7/', 1),
(2, 'Ben',   'Carter',  'ben@example.com',   '07700900102', '4 Mill Lane',      'Leeds',      'LS2 9JT',  'UK', '2026-09-03', '$6$Tr4nsitG0Salt$JbMDsX9m/InadeF86BRH8s/KIA79.QIbQAW5WJkGnBDfQB3WOIuJhYW0d6CD75gdDqqg1uWHjxyxfhmmyD5Y7/', 0),
(3, 'Chloe', 'Evans',   'chloe@example.com', '07700900103', '27 Station Road',  'Cardiff',    'CF10 1BH', 'UK', '2026-09-10', '$6$Tr4nsitG0Salt$JbMDsX9m/InadeF86BRH8s/KIA79.QIbQAW5WJkGnBDfQB3WOIuJhYW0d6CD75gdDqqg1uWHjxyxfhmmyD5Y7/', 0),
(4, 'Dev',   'Patel',   'dev@example.com',   '07700900104', '8 Elm Grove',      'Manchester', 'M1 4BT',   'UK', '2026-09-15', '$6$Tr4nsitG0Salt$JbMDsX9m/InadeF86BRH8s/KIA79.QIbQAW5WJkGnBDfQB3WOIuJhYW0d6CD75gdDqqg1uWHjxyxfhmmyD5Y7/', 0),
(5, 'Ella',  'Brooks',  'ella@example.com',  '07700900105', '31 Harbour View',  'Edinburgh',  'EH6 6QW',  'UK', '2026-10-02', '$6$Tr4nsitG0Salt$JbMDsX9m/InadeF86BRH8s/KIA79.QIbQAW5WJkGnBDfQB3WOIuJhYW0d6CD75gdDqqg1uWHjxyxfhmmyD5Y7/', 0),
(6, 'Finn',  'Walker',  'finn@example.com',  '07700900106', '15 Orchard Way',   'Oxford',     'OX2 6PE',  'UK', '2026-10-09', '$6$Tr4nsitG0Salt$JbMDsX9m/InadeF86BRH8s/KIA79.QIbQAW5WJkGnBDfQB3WOIuJhYW0d6CD75gdDqqg1uWHjxyxfhmmyD5Y7/', 0);

INSERT INTO routes (route_id, start_city, end_city, distance, time_taken, transport_type, price, departure_time, arrival_time, route_status) VALUES
(1, 'London',     'Brighton',   85.00, '01:00:00', 'Train', 16.00, '07:45:00', '08:45:00', 'Active'),
(2, 'London',     'Cambridge',  95.00, '01:15:00', 'Train', 18.50, '09:10:00', '10:25:00', 'Active'),
(3, 'Bristol',    'Cardiff',    72.00, '00:55:00', 'Train', 14.50, '08:15:00', '09:10:00', 'Active'),
(4, 'Leeds',      'York',       40.00, '00:30:00', 'Train',  9.00, '10:00:00', '10:30:00', 'Active'),
(5, 'Manchester', 'Liverpool',  56.00, '01:10:00', 'Bus',    8.50, '11:20:00', '12:30:00', 'Active'),
(6, 'Edinburgh',  'Glasgow',    75.00, '01:20:00', 'Bus',   10.00, '13:00:00', '14:20:00', 'Active'),
(7, 'Birmingham', 'Nottingham', 80.00, '01:30:00', 'Bus',   11.00, '14:15:00', '15:45:00', 'Active'),
(8, 'Oxford',     'Reading',    40.00, '00:25:00', 'Train',  7.50, '16:30:00', '16:55:00', 'Active');

INSERT INTO bookings (booking_id, passenger_id, route_id, travel_date, seat_number, booking_status, booking_date, payment_status, payment_method, total_amount, luggage_count, cancellation_date) VALUES
(1, 2, 4, '2026-12-10', 'A1', 'Confirmed', '2026-11-01', 'Paid',    'Card', 9.00,  1, NULL),
(2, 3, 3, '2026-12-11', 'B4', 'Confirmed', '2026-11-02', 'Pending', 'Cash', 14.50, 0, NULL),
(3, 4, 5, '2026-12-12', 'C2', 'Cancelled', '2026-11-03', 'Pending', 'Card', 8.50,  1, '2026-11-05'),
(4, 5, 5, '2026-12-12', 'C2', 'Confirmed', '2026-11-06', 'Paid',    'Card', 8.50,  2, NULL),
(5, 6, 8, '2026-12-14', 'D3', 'Confirmed', '2026-11-07', 'Paid',    'Card', 7.50,  1, NULL),
(6, 1, 1, '2026-12-15', 'A2', 'Confirmed', '2026-11-08', 'Pending', 'Cash', 16.00, 2, NULL);
