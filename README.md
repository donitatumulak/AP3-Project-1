# 🏥 Clinic Management System

[![PHP](https://img.shields.io/badge/PHP-8.0+-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)](https://www.mysql.com/)

A **PHP + MySQL web application** to manage clinic operations: doctor schedules, patient records, appointments, services and many more. Designed for local deployment using **XAMPP**.

---

## ⚡ Features

- **Patient Management**: Add, view, update patient records.  
- **Doctor Management**: Manage doctor profiles, specializations, and schedules.  
- **Appointments**: Book, view, and update appointments with pagination.  
- **Services and Specialization Management**: CRUD operations for clinic services.  
- **User Roles**: Superadmin, staff, and doctor roles with access restrictions.  
- **Database Integration**: MySQL database with sample data (`sql/ap3_clinic_system(2).sql`).
- and more! explore this project for more features!

---

## 🛠️ Quick Setup

1. **Install XAMPP** and start **Apache & MySQL**.  
2. Copy the project folder to: C:\xampp\htdocs\clinic_system (or whatever folder name you'd like to use).
3. Import the database:  
- Open **phpMyAdmin** → Import → `sql/ap3_clinic_system(2).sql.  
4. Configure the database connection:  
```php
config/Database.php
$host = 'localhost';
$user = 'your_db_user';
$pass = 'your_db_password';
$db   = 'clinic_db';
5. Open the system in your browser: http://localhost/clinic_system/ (sample only)

---

## 📁 Project Structure 
clinic_system/
├── pages/          # Frontend pages
├── handlers/       # PHP CRUD scripts
├── config/         # Database config
├── sql/            # Database SQL dump
├── assets/         # CSS, JS, images
├── index.php       # Main entry point
└── README.md       # Documentation
and many more! check out the actual folder for the complete project structure. :)

---

## ⚠️ Notes
1. Only use sample data; do not upload real patient information.
2. Tested with PHP 8+ and MySQL 8+.
3. Keep sensitive credentials safe — don’t commit real passwords.

---

## 📝 Authors
**Donita Tumulak**
**Therese Marie Rosalijos** 
**Fiona Maye Menao**
– BS Information Systems 3rd year students, project developed as part of coursework.
- AP3: Web Development 2025
