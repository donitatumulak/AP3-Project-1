🏥 Clinic Management System
https://img.shields.io/badge/PHP-8.0+-blue
https://img.shields.io/badge/MySQL-8.0+-orange

A PHP + MySQL web application to manage clinic operations: doctor schedules, patient records, appointments, services and many more. Designed for local deployment using XAMPP.

⚡ Features
Patient Management: Add, view, update patient records.

Doctor Management: Manage doctor profiles, specializations, and schedules.

Appointments: Book, view, and update appointments with pagination.

Services and Specialization Management: CRUD operations for clinic services.

User Roles: Superadmin, staff, and doctor roles with access restrictions.

Database Integration: MySQL database with sample data (sql/ap3_clinic_system(2).sql).

and more! Explore this project for more features!

🛠️ Quick Setup
1. Install XAMPP
Download and install XAMPP, then start Apache & MySQL.

2. Copy Project Files
Copy the project folder to:
C:\xampp\htdocs\clinic_system (or whatever folder name you'd like to use).

3. Import Database
Open phpMyAdmin (http://localhost/phpmyadmin)

Click Import tab

Select the file: sql/ap3_clinic_system(2).sql

Click Go

4. Configure Database Connection
Edit config/Database.php with your database credentials:

php
$host = 'localhost';
$user = 'your_db_user';
$pass = 'your_db_password';
$db   = 'clinic_db';
5. Access the System
Open your browser and navigate to:
http://localhost/clinic_system/

📁 Project Structure
text
clinic_system/
├── pages/          # Frontend pages
├── handlers/       # PHP CRUD scripts
├── config/         # Database config
├── sql/            # Database SQL dump
├── assets/         # CSS, JS, images
├── index.php       # Main entry point
└── README.md       # Documentation
and many more! Check out the actual folder for the complete project structure. :)

⚠️ Notes
Only use sample data; do not upload real patient information.

Tested with PHP 8+ and MySQL 8+.

Keep sensitive credentials safe — don't commit real passwords.

This is a student project for educational purposes.

📝 Authors
Donita Tumulak
Therese Marie Rosalijos
Fiona Maye Menao

– BS Information Systems 3rd year students, project developed as part of coursework.
– AP3: Web Development 2025

🔐 Default Login Credentials
Check the database or documentation for default login credentials for testing purposes.

For questions or support, please contact the development team.
