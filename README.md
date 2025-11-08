# Taste of Africa - Registration & Login System

A complete user registration and authentication system for the "Taste of Africa" restaurant discovery platform.

## 🚀 Features

### Registration System
- **Complete User Registration** with country and city selection
- **Country Dropdown** with 60+ countries
- **Form Validation** (client-side and server-side)
- **Password Security** with hashing
- **Email Validation** and duplicate checking
- **Role Selection** (Customer or Restaurant Owner)

### Login System
- **Secure Authentication** with session management
- **Password Verification** using PHP's password_verify()
- **Session Variables** for user data persistence
- **Error Handling** with detailed messages
- **Auto-redirect** to dashboard after login

### User Interface
- **Beautiful Landing Page** with modern gradient design
- **Responsive Design** for all devices
- **Animated Elements** and smooth transitions
- **Professional Dashboard** for logged-in users
- **Glass-morphism Effects** for modern UI

## 📁 Project Structure

```
register_sample/
├── actions/
│   ├── login_customer_action.php    # Login form handler
│   └── register_user_action.php     # Registration form handler
├── classes/
│   ├── customer_class.php           # Customer model with login functionality
│   └── user_class.php              # Legacy user class (updated)
├── controllers/
│   ├── customer_controller.php      # Customer controller
│   └── user_controller.php         # User controller (updated)
├── db/
│   └── dbforlab.sql                # Database schema
├── js/
│   ├── login.js                    # Login form validation & AJAX
│   └── register.js                 # Registration form validation & AJAX
├── login/
│   ├── login.php                   # Login form
│   ├── logout.php                  # Logout functionality
│   └── register.php                # Registration form
├── settings/
│   ├── core.php                    # Core settings
│   ├── db_class.php                # Database connection class
│   └── db_cred.php                 # Database credentials
├── dashboard.php                   # User dashboard
├── index.php                       # Landing page
└── README.md                       # This file
```

## 🛠️ Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/monicah-pirisi/week2_part2.git
   ```

2. **Set up the database:**
   - Import `db/dbforlab.sql` into your MySQL database
   - Update database credentials in `settings/db_cred.php`

3. **Configure the environment:**
   - Ensure PHP 7.4+ is installed
   - Enable MySQLi extension
   - Set up a local server (XAMPP, WAMP, or similar)

4. **Access the application:**
   - Navigate to `http://localhost/your-project-path/`
   - Register a new account or login

## 🎯 User Flow

1. **Landing Page** → Beautiful welcome page with registration/login options
2. **Registration** → Complete form with country/city selection
3. **Login** → Secure authentication with session management
4. **Dashboard** → Personalized user dashboard with profile information
5. **Logout** → Secure session destruction

## 🔐 Security Features

- **Password Hashing** using PHP's `password_hash()` and `password_verify()`
- **SQL Injection Protection** with prepared statements
- **XSS Protection** with proper data sanitization
- **Session Management** with secure session handling
- **Input Validation** on both client and server side

## 🎨 Design Features

- **Modern UI** with gradient backgrounds and glass-morphism effects
- **Responsive Design** that works on all devices
- **Smooth Animations** using CSS animations and transitions
- **Professional Typography** with clean, readable fonts
- **Consistent Color Scheme** using brand colors (#D19C97, #b77a7a)

## 📱 Technologies Used

- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript (jQuery)
- **UI Framework:** Bootstrap 5.3
- **Icons:** Font Awesome 6.4
- **Animations:** Animate.css
- **Notifications:** SweetAlert2

## 🗄️ Database Schema

The system uses a `customer` table with the following structure:
- `customer_id` (Primary Key)
- `customer_name`
- `customer_email` (Unique)
- `customer_pass` (Hashed)
- `customer_contact`
- `customer_country`
- `customer_city`
- `customer_image`
- `user_role` (1=Customer, 2=Restaurant Owner)

## 🚀 Getting Started

1. **Register a new account:**
   - Fill out the registration form
   - Select your country from the dropdown
   - Enter your city
   - Choose your role (Customer or Restaurant Owner)

2. **Login to your account:**
   - Use your email and password
   - You'll be redirected to your personalized dashboard

3. **Explore your dashboard:**
   - View your profile information
   - Access role-specific features
   - Logout when done

## 🔧 Configuration

### Database Settings
Update `settings/db_cred.php` with your database credentials:
```php
define("SERVER", "localhost");
define("USERNAME", "your_username");
define("PASSWD", "your_password");
define("DATABASE", "your_database_name");
```

## 📝 License

This project is part of an educational assignment for e-commerce lab work.

## 👥 Contributing

This is a learning project. Feel free to fork and experiment with the code!

## 📞 Support

For questions or issues, please refer to the course materials or contact your instructor.

---

**Note:** This is a complete authentication system with modern UI/UX design, following best practices for security and user experience.
"# week2_part2" 
