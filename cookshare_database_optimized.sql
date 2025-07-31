-- Base de Datos CookShare - Versión Optimizada
-- Sistema completo para compartir recetas de cocina

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS cookshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cookshare;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Removido UNIQUE ya que passwords pueden repetirse
    token VARCHAR(255) UNIQUE, -- Cambiado a NULL para permitir usuarios sin token activo
    nombre_completo VARCHAR(100) NOT NULL,
    bio TEXT,
    foto_perfil VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_usuarios_activo (activo),
    INDEX idx_usuarios_nombre (nombre_usuario)
);

-- Tabla de categorías de recetas
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    icono VARCHAR(100),
    activa BOOLEAN DEFAULT TRUE
);

-- Tabla de recetas
CREATE TABLE recetas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    tiempo_preparacion INT NOT NULL, -- en minutos
    tiempo_coccion INT DEFAULT 0, -- en minutos
    porciones INT NOT NULL,
    dificultad ENUM('Fácil', 'Intermedio', 'Difícil') NOT NULL,
    foto_principal LONGTEXT, -- Cambiado de longtext a LONGTEXT para consistencia
    instrucciones LONGTEXT NOT NULL,
    usuario_id INT NOT NULL,
    categoria_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    INDEX idx_recetas_activa (activa),
    INDEX idx_recetas_fecha (fecha_creacion),
    INDEX idx_recetas_dificultad (dificultad)
);

-- Tabla de ingredientes
CREATE TABLE ingredientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL, -- gramos, mililitros, unidades, etc.
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de relación receta-ingredientes
CREATE TABLE receta_ingredientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    receta_id INT NOT NULL,
    ingrediente_id INT NOT NULL,
    cantidad DECIMAL(8,2) NOT NULL,
    notas VARCHAR(100), -- opcional, ej: "picado finamente"
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE,
    FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id),
    UNIQUE KEY unique_receta_ingrediente (receta_id, ingrediente_id)
);

-- Tabla de valoraciones
CREATE TABLE valoraciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    receta_id INT NOT NULL,
    usuario_id INT NOT NULL,
    puntuacion INT NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
    fecha_valoracion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_receta (usuario_id, receta_id),
    INDEX idx_valoraciones_puntuacion (puntuacion)
);

-- Tabla de comentarios
CREATE TABLE comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    receta_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_comentarios_activo (activo),
    INDEX idx_comentarios_fecha (fecha_comentario)
);

-- Tabla de recetas favoritas
CREATE TABLE favoritos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    receta_id INT NOT NULL,
    fecha_favorito TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorito (usuario_id, receta_id),
    INDEX idx_favoritos_fecha (fecha_favorito)
);

-- Tabla de etiquetas/tags
CREATE TABLE etiquetas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    color VARCHAR(7) DEFAULT '#007bff', -- código hexadecimal
    activa BOOLEAN DEFAULT TRUE
);

-- Tabla de relación receta-etiquetas
CREATE TABLE receta_etiquetas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    receta_id INT NOT NULL,
    etiqueta_id INT NOT NULL,
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE,
    FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id),
    UNIQUE KEY unique_receta_etiqueta (receta_id, etiqueta_id)
);

-- INSERCIÓN DE DATOS DE EJEMPLO

-- Insertar categorías
INSERT INTO categorias (nombre, descripcion, icono) VALUES
('Desayunos', 'Recetas para comenzar el día', '🍳'),
('Platos Principales', 'Comidas principales del día', '🍽️'),
('Postres', 'Dulces y postres deliciosos', '🍰'),
('Aperitivos', 'Entrantes y bocadillos', '🥗'),
('Bebidas', 'Bebidas y cócteles', '🥤'),
('Sopas', 'Sopas y caldos', '🍲'),
('Ensaladas', 'Ensaladas frescas y saludables', '🥙'),
('Pasta', 'Platos de pasta italiana', '🍝');

-- Insertar usuarios con imágenes reales de perfiles
INSERT INTO usuarios (nombre_usuario, email, password, nombre_completo, bio, foto_perfil) VALUES
('chef_maria', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González', 'Chef profesional con 15 años de experiencia', 'https://images.pexels.com/photos/3338497/pexels-photo-3338497.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop'),
('cocina_casera', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'Amante de la cocina tradicional española', 'https://images.pexels.com/photos/8629131/pexels-photo-8629131.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop'),
('veggie_lover', 'ana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Martín', 'Especialista en cocina vegetariana y vegana', 'https://images.pexels.com/photos/3771106/pexels-photo-3771106.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop'),
('dulce_tentacion', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Ruiz', 'Repostero creativo e innovador', 'https://images.pexels.com/photos/3814446/pexels-photo-3814446.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop'),
('cocinero_novato', 'lucia@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lucía Fernández', 'Aprendiendo a cocinar paso a paso', 'https://images.pexels.com/photos/4253302/pexels-photo-4253302.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop'),
('masterchef_home', 'diego@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Diego Sánchez', 'Cocinero amateur con grandes ambiciones', 'https://images.pexels.com/photos/4253312/pexels-photo-4253312.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop');

-- Insertar ingredientes
INSERT INTO ingredientes (nombre, unidad_medida) VALUES
('Huevos', 'unidades'),
('Harina', 'gramos'),
('Azúcar', 'gramos'),
('Leche', 'mililitros'),
('Mantequilla', 'gramos'),
('Tomate', 'unidades'),
('Cebolla', 'unidades'),
('Ajo', 'dientes'),
('Aceite de oliva', 'mililitros'),
('Sal', 'gramos'),
('Pimienta negra', 'gramos'),
('Queso', 'gramos'),
('Pollo', 'gramos'),
('Arroz', 'gramos'),
('Pasta', 'gramos'),
('Chocolate', 'gramos'),
('Vainilla', 'mililitros'),
('Limón', 'unidades'),
('Perejil', 'gramos'),
('Jamón', 'gramos');

-- Insertar etiquetas
INSERT INTO etiquetas (nombre, color) VALUES
('Rápido', '#28a745'),
('Vegetariano', '#17a2b8'),
('Sin Gluten', '#ffc107'),
('Bajo en Calorías', '#6f42c1'),
('Tradicional', '#dc3545'),
('Fácil', '#20c997'),
('Gourmet', '#fd7e14'),
('Económico', '#6c757d');

-- Insertar recetas con URLs de imágenes reales
INSERT INTO recetas (titulo, descripcion, tiempo_preparacion, tiempo_coccion, porciones, dificultad, foto_principal, instrucciones, usuario_id, categoria_id) VALUES
('Tortilla Española Clásica', 'La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera', 15, 20, 4, 'Intermedio', 'https://images.pexels.com/photos/7625439/pexels-photo-7625439.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Pelar y cortar las patatas en láminas finas. 2. Freír las patatas en aceite abundante. 3. Batir los huevos y mezclar con las patatas. 4. Cuajar en la sartén por ambos lados.', 2, 2),

('Pancakes Americanos', 'Esponjosos pancakes perfectos para el desayuno', 10, 15, 3, 'Fácil', 'https://images.pexels.com/photos/376464/pexels-photo-376464.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Mezclar ingredientes secos. 2. Batir huevos con leche. 3. Combinar ambas mezclas. 4. Cocinar en sartén caliente hasta que estén dorados.', 5, 1),

('Tarta de Chocolate', 'Rica tarta de chocolate con ganache cremoso', 30, 45, 8, 'Difícil', 'https://images.pexels.com/photos/291528/pexels-photo-291528.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Preparar la masa quebrada. 2. Hacer el relleno de chocolate. 3. Hornear la base. 4. Añadir el relleno y decorar con ganache.', 4, 3),

('Ensalada César', 'Fresca ensalada con pollo, crutones y aderezo césar', 20, 0, 2, 'Fácil', 'https://images.pexels.com/photos/2097090/pexels-photo-2097090.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Lavar y cortar la lechuga. 2. Preparar los crutones. 3. Cocinar el pollo a la plancha. 4. Mezclar con el aderezo césar.', 3, 7),

('Paella Valenciana', 'La tradicional paella española con pollo y verduras', 20, 40, 6, 'Difícil', 'https://images.pexels.com/photos/16743489/pexels-photo-16743489.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Sofreír el pollo. 2. Añadir las verduras. 3. Incorporar el arroz y el caldo. 4. Cocinar sin remover hasta que esté listo.', 1, 2),

('Gazpacho Andaluz', 'Sopa fría perfecta para el verano', 15, 0, 4, 'Fácil', 'https://images.pexels.com/photos/5737241/pexels-photo-5737241.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Triturar todos los vegetales. 2. Añadir aceite y vinagre. 3. Salpimentar al gusto. 4. Refrigerar antes de servir.', 2, 6),

('Pasta Carbonara', 'Cremosa pasta italiana con bacon y huevo', 10, 15, 4, 'Intermedio', 'https://images.pexels.com/photos/4518843/pexels-photo-4518843.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Cocinar la pasta al dente. 2. Freír el bacon. 3. Batir huevos con queso. 4. Mezclar todo fuera del fuego.', 6, 8),

('Brownie de Chocolate', 'Intenso brownie con nueces', 15, 30, 9, 'Fácil', 'https://images.pexels.com/photos/887853/pexels-photo-887853.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Derretir chocolate con mantequilla. 2. Batir huevos con azúcar. 3. Mezclar con harina. 4. Hornear hasta que esté firme.', 4, 3),

('Croquetas de Jamón', 'Cremosas croquetas caseras', 45, 10, 20, 'Intermedio', 'https://images.pexels.com/photos/12737543/pexels-photo-12737543.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Hacer la bechamel. 2. Añadir jamón picado. 3. Enfriar y formar las croquetas. 4. Rebozar y freír.', 1, 4),

('Smoothie Verde', 'Batido saludable con espinacas y frutas', 5, 0, 2, 'Fácil', 'https://images.pexels.com/photos/1092730/pexels-photo-1092730.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80', '1. Lavar las espinacas. 2. Pelar y trocear las frutas. 3. Batir todo con agua o leche vegetal. 4. Servir inmediatamente.', 3, 5);

-- Insertar ingredientes para las recetas
INSERT INTO receta_ingredientes (receta_id, ingrediente_id, cantidad, notas) VALUES
-- Tortilla Española
(1, 1, 6, 'huevos grandes'),
(1, 7, 2, 'cebollas medianas'),
(1, 9, 150, 'aceite de oliva virgen'),
(1, 10, 5, 'sal al gusto'),
-- Pancakes
(2, 1, 2, 'huevos batidos'),
(2, 2, 200, 'harina común'),
(2, 4, 250, 'leche entera'),
(2, 3, 30, 'azúcar'),
(2, 5, 50, 'mantequilla derretida'),
-- Tarta de Chocolate
(3, 16, 200, 'chocolate negro'),
(3, 1, 4, 'huevos'),
(3, 3, 150, 'azúcar'),
(3, 5, 100, 'mantequilla'),
(3, 2, 100, 'harina'),
-- Ensalada César
(4, 13, 300, 'pechuga de pollo'),
(4, 12, 100, 'queso parmesano'),
(4, 9, 50, 'aceite de oliva'),
-- Paella Valenciana
(5, 14, 400, 'arroz bomba'),
(5, 13, 500, 'pollo troceado'),
(5, 6, 3, 'tomates maduros'),
(5, 9, 100, 'aceite de oliva'),
-- Gazpacho
(6, 6, 6, 'tomates maduros'),
(6, 7, 1, 'cebolla pequeña'),
(6, 8, 2, 'dientes de ajo'),
(6, 9, 80, 'aceite de oliva virgen'),
-- Pasta Carbonara
(7, 15, 400, 'espaguetis'),
(7, 1, 4, 'huevos'),
(7, 12, 100, 'queso pecorino'),
(7, 20, 150, 'panceta o bacon'),
-- Brownie
(8, 16, 150, 'chocolate negro'),
(8, 1, 3, 'huevos'),
(8, 3, 120, 'azúcar moreno'),
(8, 2, 80, 'harina'),
(8, 5, 80, 'mantequilla'),
-- Croquetas de Jamón
(9, 20, 200, 'jamón serrano picado'),
(9, 4, 500, 'leche entera'),
(9, 2, 80, 'harina'),
(9, 5, 80, 'mantequilla'),
(9, 1, 2, 'huevos para rebozar'),
-- Smoothie Verde
(10, 4, 200, 'leche de almendras'),
(10, 18, 1, 'limón exprimido');

-- Insertar etiquetas para las recetas
INSERT INTO receta_etiquetas (receta_id, etiqueta_id) VALUES
(1, 5), (1, 6), -- Tortilla: Tradicional, Fácil
(2, 1), (2, 6), -- Pancakes: Rápido, Fácil
(3, 7), (3, 3), -- Tarta: Gourmet, Difícil
(4, 1), (4, 4), -- César: Rápido, Bajo en Calorías
(5, 5), (5, 7), -- Paella: Tradicional, Gourmet
(6, 2), (6, 4), (6, 1), -- Gazpacho: Vegetariano, Bajo en Calorías, Rápido
(7, 1), (7, 5), -- Carbonara: Rápido, Tradicional
(8, 6), (8, 8), -- Brownie: Fácil, Económico
(9, 5), (9, 7), -- Croquetas: Tradicional, Gourmet
(10, 2), (10, 4), (10, 1); -- Smoothie: Vegetariano, Bajo en Calorías, Rápido

-- Insertar valoraciones
INSERT INTO valoraciones (receta_id, usuario_id, puntuacion) VALUES
(1, 1, 5), (1, 3, 4), (1, 4, 5),
(2, 2, 5), (2, 4, 4), (2, 6, 5),
(3, 1, 5), (3, 2, 4), (3, 5, 5),
(4, 2, 4), (4, 5, 3), (4, 6, 4),
(5, 3, 5), (5, 4, 5), (5, 6, 4),
(6, 1, 4), (6, 4, 5), (6, 5, 4),
(7, 2, 5), (7, 3, 4), (7, 5, 5),
(8, 1, 5), (8, 3, 5), (8, 6, 4),
(9, 2, 4), (9, 5, 5), (9, 6, 4),
(10, 1, 4), (10, 2, 3), (10, 4, 5);

-- Insertar comentarios
INSERT INTO comentarios (receta_id, usuario_id, comentario) VALUES
(1, 1, '¡Excelente receta! La tortilla quedó perfecta, cremosa por dentro.'),
(1, 3, 'Muy buena explicación paso a paso. La repetiré seguro.'),
(2, 4, 'Mis hijos los adoran. Perfectos para el desayuno del domingo.'),
(3, 2, 'Un poco complicada pero el resultado vale la pena. ¡Espectacular!'),
(5, 6, 'La mejor paella que he probado. Gracias por compartir la receta.'),
(6, 4, 'Perfecta para el verano. Muy refrescante y fácil de hacer.'),
(7, 3, 'Quedó deliciosa, aunque tuve que practicar varias veces la técnica.'),
(8, 6, 'Brownies increíbles, muy chocolateados. Los niños están encantados.'),
(9, 5, 'Las croquetas quedaron perfectas. El truco está en la bechamel.'),
(10, 2, 'Excelente manera de incluir verduras en la dieta. Muy sabroso.');

-- Insertar favoritos
INSERT INTO favoritos (usuario_id, receta_id) VALUES
(1, 3), (1, 5), (1, 7),
(2, 1), (2, 6), (2, 8),
(3, 2), (3, 4), (3, 10),
(4, 1), (4, 3), (4, 9),
(5, 2), (5, 6), (5, 7),
(6, 1), (6, 4), (6, 8);

-- Crear índices adicionales para mejorar el rendimiento
CREATE INDEX idx_recetas_usuario_activa ON recetas(usuario_id, activa);
CREATE INDEX idx_valoraciones_receta_puntuacion ON valoraciones(receta_id, puntuacion);
CREATE INDEX idx_comentarios_receta_activo ON comentarios(receta_id, activo);
CREATE INDEX idx_favoritos_usuario_fecha ON favoritos(usuario_id, fecha_favorito);

-- Crear vista para estadísticas de recetas
CREATE VIEW vista_estadisticas_recetas AS
SELECT 
    r.id,
    r.titulo,
    r.dificultad,
    COUNT(DISTINCT v.id) as total_valoraciones,
    AVG(v.puntuacion) as promedio_valoracion,
    COUNT(DISTINCT c.id) as total_comentarios,
    COUNT(DISTINCT f.id) as total_favoritos
FROM recetas r
LEFT JOIN valoraciones v ON r.id = v.receta_id
LEFT JOIN comentarios c ON r.id = c.receta_id AND c.activo = TRUE
LEFT JOIN favoritos f ON r.id = f.receta_id
WHERE r.activa = TRUE
GROUP BY r.id, r.titulo, r.dificultad;

-- Crear procedimiento para obtener recetas populares
DELIMITER //
CREATE PROCEDURE ObtenerRecetasPopulares(IN limite INT)
BEGIN
    SELECT 
        r.id,
        r.titulo,
        r.descripcion,
        r.dificultad,
        r.foto_principal,
        u.nombre_usuario,
        u.foto_perfil,
        COUNT(DISTINCT f.id) as total_favoritos,
        AVG(v.puntuacion) as promedio_valoracion
    FROM recetas r
    JOIN usuarios u ON r.usuario_id = u.id
    LEFT JOIN favoritos f ON r.id = f.receta_id
    LEFT JOIN valoraciones v ON r.id = v.receta_id
    WHERE r.activa = TRUE
    GROUP BY r.id, r.titulo, r.descripcion, r.dificultad, r.foto_principal, u.nombre_usuario, u.foto_perfil
    ORDER BY total_favoritos DESC, promedio_valoracion DESC
    LIMIT limite;
END //
DELIMITER ;

-- Mostrar información de la base de datos
SELECT 'Base de datos CookShare creada exitosamente' as mensaje;
SELECT COUNT(*) as total_usuarios FROM usuarios;
SELECT COUNT(*) as total_recetas FROM recetas;
SELECT COUNT(*) as total_ingredientes FROM ingredientes;
SELECT COUNT(*) as total_valoraciones FROM valoraciones;
SELECT COUNT(*) as total_comentarios FROM comentarios;
SELECT COUNT(*) as total_favoritos FROM favoritos; 