# Library Management System

## Overview
The Library Management System is an application designed to streamline the operations of a library by automating the process of managing books, members, and transactions. This project aims to make library management simpler and more efficient.

## Description
This system allows users to add new books, track the lending of books, manage member registrations, and oversee the return process. With a user-friendly interface, it facilitates both staff and member interactions with the library.

## Technologies Used
- **Programming Language**: Python
- **Framework**: Django
- **Database**: SQLite/PostgreSQL
- **Frontend**: HTML, CSS, JavaScript
- **Version Control**: Git & GitHub

## Features
- User authentication and authorization
- Book management (add, update, delete books)
- Member management (register, edit member details)
- Loan management (checkout and return books)
- Search functionality for books and members
- Reports on transactions and available books

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/cushionfalls/Library_management_system.git
   ```
2. Navigate into the project directory:
   ```bash
   cd Library_management_system
   ```
3. Install the required packages:
   ```bash
   pip install -r requirements.txt
   ```
4. Apply migrations:
   ```bash
   python manage.py migrate
   ```
5. Create a superuser (for admin access):
   ```bash
   python manage.py createsuperuser
   ```
6. Run the application:
   ```bash
   python manage.py runserver
   ```

## Usage
- Access the application by navigating to `http://127.0.0.1:8000/` in your web browser.
- Use the admin credentials to log in as an administrator.
- Navigate through the features to manage the library effectively.

## Conclusion
The Library Management System is an essential tool for modern libraries looking to enhance their operational efficiency and user experience. Whether you are managing a small community library or a larger institution, this system meets your needs.