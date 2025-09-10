# Restaurant POS System

A modern restaurant Point of Sale (POS) system built with PHP, HTML, CSS, and Bootstrap. This system allows restaurant administrators to manage menu categories and items with detailed information, and provides a responsive interface for viewing and ordering from the menu.

## Features

- **Admin Dashboard**: Overview of restaurant statistics
- **Menu Category Management**: Add, edit, and delete menu categories
- **Menu Item Management**: Add, edit, and delete menu items with detailed information:
  - Item Name
  - Price
  - Veg/Non-Veg tag
  - Age restriction (18+)
  - Spice level
  - Portion size
  - Preparation time
  - Image upload
- **Responsive Menu Display**: View menu items in a card/grid layout with images
- **Category Filtering**: Filter menu items by category
- **Order Management**: Basic order tracking functionality

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/MAMP or similar local development environment

## Installation

1. Clone or download this repository to your web server's document root (e.g., `htdocs` for XAMPP)
2. Make sure your web server and MySQL are running
3. Access the application through your web browser (e.g., `http://localhost/restaurant_pos`)
4. The system will automatically create the database and tables on first run
5. Log in with the default admin credentials:
   - Username: `admin`
   - Password: `admin123`

## Directory Structure

```
restaurant_pos/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── config/
│   └── database.php
├── includes/
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── pages/
│   ├── 404.php
│   ├── categories.php
│   ├── dashboard.php
│   ├── menu.php
│   ├── menu_items.php
│   ├── orders.php
│   └── profile.php
├── uploads/
│   └── menu/
├── index.php
├── login.php
├── logout.php
└── README.md
```

## Usage

### Admin Functions

1. **Login**: Access the system using admin credentials
2. **Dashboard**: View restaurant statistics
3. **Categories**: Manage menu categories
4. **Menu Items**: Add and manage menu items with detailed information
5. **Profile**: Update your account information

### Menu Display

1. Navigate to the "Menu" section
2. Use the category tabs to filter items by category
3. View detailed information about each menu item
4. Click "Add to Order" to add items to the current order

## Security Features

- Password hashing for user authentication
- Input sanitization to prevent SQL injection
- Role-based access control
- Session management

## Customization

You can customize the system by:

1. Modifying the CSS in `assets/css/style.css`
2. Adding additional fields to menu items in the database schema
3. Extending the order management functionality

## License

This project is open-source and available for personal and commercial use.

## Support

For questions or support, please contact the developer.