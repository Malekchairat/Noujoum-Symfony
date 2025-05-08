🌟 Noujoum - Fan Events & Merchandise Platform
Noujoum is a web application built with Symfony, designed to connect fan communities by offering a platform for publishing events (concerts, fan meets) and selling merchandise. The platform includes user management, ticket reservations, product purchases, and more — all accessible to fans and administrators.

📁 Project Structure
This project is divided into 5 main management modules:

Gestion des utilisateurs – Fan/Admin, Favorites

Gestion des événements – Events, Tickets

Gestion des produits – Products, Promotions

Gestion des commandes – Cart, Orders

Gestion des réclamations – Complaints, Feedback

Each member handled one section, implementing both basic (CRUD) and advanced features.

🔧 Tech Stack
Backend: PHP 8+, Symfony 6

Database: MySQL

Frontend: Twig, Bootstrap, HTML/CSS

Tools: Doctrine ORM, Git, GitHub, Postman

Extras: Google Calendar API, Leaflet Map

🚀 Features
✅ User registration, login, and profile management

✅ Event creation, modification, filtering, and ticket reservation

✅ Product browsing and purchase system

✅ Cart and order history

✅ Admin dashboards

✅ Complaint and feedback system

✅ Google Calendar integration

✅ Interactive maps (Leaflet)

⚙️ Installation & Setup
bash
Copy
Edit
git clone https://github.com/YourTeam/Noujoum.git
cd Noujoum
composer install
cp .env.example .env
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
symfony server:start
Make sure to configure your .env file with your database credentials.

👥 Team Members
Name	Module
Member 1	User Management
Member 2	Event Management
Member 3	Product & Promotions
Member 4	Orders & Cart
Member 5	Complaints & Feedback



📌 Future Improvements
Payment gateway integration

Notification system

Mobile version (FlutterFlow)

Role-based access control

🤝 Contribution
Contributions are welcome! Clone the repo, create a new branch, and submit a pull request.

📄 License
MIT License — Feel free to use and modify.

