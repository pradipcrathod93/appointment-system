Prerequisites
--------------
PHP: Ensure PHP (>= 8.1) is installed on your machine.
Composer: Make sure Composer is installed.
Node.js: Ensure Node.js and npm (Node Package Manager) are installed.
Database: Install a database server like MySQL or PostgreSQL.
Git: Make sure Git is installed.


Setup Instructions:
--------------------

1. Clone the Repository : gh repo clone pradipcrathod93/appointment-system
2. Navigate to Project folder : cd appointment-system
4. Install Dependencies : composer install npm install
5. Create database & config in .env file
6. Generate App Key (Optional) : php artisan key:generate
7. Migrate Database : php artisan migrate
8. Start local server : php artisan serve

Linux/Ubuntu Permission 
-------------------------

sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache


Appointment Booking Steps
-------------------------

1. Go to Register User and Login with System
2. Click Book Appointment Button
3. Fill Out the Appointment Form and Submit
