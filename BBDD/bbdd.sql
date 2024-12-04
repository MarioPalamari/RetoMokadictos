-- SQLBook: Code
CREATE DATABASE db_mokadictos;

USE db_mokadictos;

-- Tabla de Roles (opcional para escalabilidad)
CREATE TABLE tbl_roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL
);

-- Tabla de Usuarios (Camareros)
CREATE TABLE tbl_users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    pwd VARCHAR(255) NOT NULL,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES tbl_roles(role_id) ON DELETE SET NULL
);

-- Tabla de Salas
CREATE TABLE tbl_rooms (
    room_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    description TEXT
);

-- Tabla de Mesas
CREATE TABLE tbl_tables (
    table_id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    current_room_id INT,
    table_number INT NOT NULL,
    capacity INT NOT NULL,
    status ENUM('free', 'occupied') DEFAULT 'free',
    FOREIGN KEY (room_id) REFERENCES tbl_rooms(room_id) ON DELETE CASCADE,
    FOREIGN KEY (current_room_id) REFERENCES tbl_rooms(room_id) ON DELETE SET NULL
);

-- Tabla de Ocupaciones (Historial de uso de mesas)
CREATE TABLE tbl_occupations (
    occupation_id INT PRIMARY KEY AUTO_INCREMENT,
    table_id INT NOT NULL,
    user_id INT NOT NULL,
    start_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_time DATETIME DEFAULT NULL,
    FOREIGN KEY (table_id) REFERENCES tbl_tables(table_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES tbl_users(user_id) ON DELETE CASCADE
);

-- Tabla de Grupos de Mesas (para juntar mesas temporalmente)
CREATE TABLE tbl_table_groups (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,  -- Ahora user_id puede ser NULL
    status ENUM('active', 'completed') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES tbl_users(user_id) ON DELETE SET NULL
);

-- Tabla Relacional para unir mesas a los grupos
CREATE TABLE tbl_group_tables (
    group_id INT,
    table_id INT,
    PRIMARY KEY (group_id, table_id),
    FOREIGN KEY (group_id) REFERENCES tbl_table_groups(group_id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tbl_tables(table_id) ON DELETE CASCADE
);

-- Insertar roles en la tabla de roles
INSERT INTO tbl_roles (role_name) VALUES ('Camarero'), ('Administrador');

-- Insertar salas en la tabla de salas
INSERT INTO tbl_rooms (name, capacity, description) 
VALUES 
    ('Terraza 1', 5, 'Primera terraza exterior del restaurante'),
    ('Terraza 2', 10, 'Segunda terraza con vistas al parque'),
    ('Terraza 3', 5, 'Tercera terraza con ambiente acogedor'),
    ('Salón 1', 15, 'Salón principal del restaurante'),
    ('Salón 2', 20, 'Salón secundario con capacidad amplia'),
    ('Sala Privada 1', 4, 'Sala privada para eventos pequeños'),
    ('Sala Privada 2', 1, 'Sala privada con ambiente íntimo'),
    ('Sala Privada 3', 2, 'Sala privada para reuniones exclusivas'),
    ('Sala Privada 4', 8, 'Sala privada de tamaño mediano');

-- Insertar mesas en la tabla de mesas
INSERT INTO tbl_tables (room_id, current_room_id, table_number, capacity, status) VALUES
-- Terraza 1
(1, NULL, 1, 4, 'free'),
(1, NULL, 2, 4, 'free'),
(1, NULL, 3, 4, 'free'),
(1, NULL, 4, 4, 'free'),
(1, NULL, 5, 4, 'free'),

-- Terraza 2
(2, NULL, 1, 4, 'free'),
(2, NULL, 2, 4, 'free'),
(2, NULL, 3, 4, 'free'),
(2, NULL, 4, 4, 'free'),
(2, NULL, 5, 4, 'free'),
(2, NULL, 6, 4, 'free'),
(2, NULL, 7, 4, 'free'),
(2, NULL, 8, 4, 'free'),
(2, NULL, 9, 4, 'free'),
(2, NULL, 10, 4, 'free'),

-- Terraza 3
(3, NULL, 1, 4, 'free'),
(3, NULL, 2, 4, 'free'),
(3, NULL, 3, 4, 'free'),
(3, NULL, 4, 4, 'free'),
(3, NULL, 5, 4, 'free'),

-- Salon 1
(4, NULL, 1, 6, 'free'),
(4, NULL, 2, 6, 'free'),
(4, NULL, 3, 6, 'free'),
(4, NULL, 4, 6, 'free'),
(4, NULL, 5, 6, 'free'),
(4, NULL, 6, 6, 'free'),
(4, NULL, 7, 6, 'free'),
(4, NULL, 8, 6, 'free'),
(4, NULL, 9, 6, 'free'),
(4, NULL, 10, 6, 'free'),

-- Salon 2
(5, NULL, 1, 8, 'free'),
(5, NULL, 2, 8, 'free'),
(5, NULL, 3, 8, 'free'),
(5, NULL, 4, 8, 'free'),
(5, NULL, 5, 8, 'free'),
(5, NULL, 6, 8, 'free'),
(5, NULL, 7, 8, 'free'),
(5, NULL, 8, 8, 'free'),
(5, NULL, 9, 8, 'free'),
(5, NULL, 10, 8, 'free'),
(5, NULL, 11, 8, 'free'),
(5, NULL, 12, 8, 'free'),
(5, NULL, 13, 8, 'free'),
(5, NULL, 14, 8, 'free'),
(5, NULL, 15, 8, 'free'),

-- Sala Privada 1
(6, NULL, 1, 2, 'free'),
(6, NULL, 2, 2, 'free'),
(6, NULL, 3, 2, 'free'),
(6, NULL, 4, 2, 'free'),

-- Sala Privada 2
(7, NULL, 1, 2, 'free'),

-- Sala Privada 3
(8, NULL, 1, 2, 'free'),
(8, NULL, 2, 2, 'free'),

-- Sala Privada 4
(9, NULL, 1, 4, 'free'),
(9, NULL, 2, 4, 'free'),
(9, NULL, 3, 4, 'free'),
(9, NULL, 4, 4, 'free'),
(9, NULL, 5, 4, 'free'),
(9, NULL, 6, 4, 'free'),
(9, NULL, 7, 4, 'free'),
(9, NULL, 8, 4, 'free');



