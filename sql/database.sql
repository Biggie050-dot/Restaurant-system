
--- Database Schema: users is voor ADMIN, KASSA,KITCHEN
CREATE TABLE users (                                             
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE,                   
    password VARCHAR(255),
    role VARCHAR(50)
);
--- Database Schema: menu_items is voor de gerechten die in het menu staan
CREATE TABLE menu_items (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    price NUMERIC(10,2),
    category VARCHAR(50),
    image_path TEXT,
    is_active BOOLEAN DEFAULT true
);
--- Database Schema: orders is voor de bestellingen die geplaatst worden
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    table_number INT,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
--- Database Schema: order_items is voor de gerechten 
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INT REFERENCES orders(id),
    menu_item_id INT REFERENCES menu_items(id),
    quantity INT
);

