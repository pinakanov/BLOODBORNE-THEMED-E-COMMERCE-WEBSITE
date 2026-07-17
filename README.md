# BLOODBORNE-THEMED-E-COMMERCE-WEBSITE
A full-stack PHP web application that simulates an e-commerce storefront themed around FromSoftware's Bloodborne, built as a class project demonstrating server-side PHP, relational database design, and session-based authentication.
Tech Stack

• Backend: PHP (procedural), mysqli for database access

• Database: MySQL via XAMPP

• Frontend: Bootstrap 5.3.3, custom CSS (Cinzel + EB Garamond via Google Fonts), vanilla JS

• Auth: PHP sessions, md5 password hashing 

Core Features

• Hunter registration/login - account creation with email/username uniqueness checks; a secret contract code (OLDBLOOD-7734) grants admin ("Gehrman") privileges at signup

• Role-based access - regular "Hunters" browse and shop; "Gehrman" (admin) manages inventory

• Shop - browse in-stock items styled as an item/notes list, with an "Acquire" action to add to cart

• Blood Echoes — the primary currency, spent to purchase items in the Workshop

• Insight — a secondary currency/stat that gates access to rare or "eldritch" items — things a normal Hunter can't see or buy until they've gained enough awareness of the hidden truths (could be special/limited-stock items requiring enoigh Insight rather than Blood Echoes)

• Cart & Checkout - session-based cart ($_SESSION['cart']), converts to an order on checkout and decrements stock

• Order history - per-hunter list of past purchases

• Admin Inventory Management - add/edit/delete items, image upload support (images/items) *make sure that images/items/ exist.

• Themed UI - dark gothic Bootstrap overrides (custom CSS variables for blood-red, bone, glow accents), Bloodborne-authentic terminology throughout (Blood Echoes, Insight, Hunter's Dream, etc.)

Database Schema (bloodborne)

• hunters - hunter_id, username, email, password, role, blood_echoes, insight, date_created

• items - item_id, item_name, description, price, stock, img_path, date_created

• orders - order_id, hunter_id, item_id, order_quantity, date_created
