# 🚀 GUÍA DE MIGRACIONES Y SEEDERS - COOKSHARE

## 📋 **Descripción General**

Este proyecto incluye **migraciones y seeders completos** para desplegar la base de datos de CookShare en cualquier entorno (desarrollo, staging, producción).

### **✅ Ventajas sobre el script SQL:**
- 🔄 **Versionado** de la base de datos
- ↩️ **Rollback** si algo sale mal
- 🚀 **Despliegue automático** en cualquier servidor
- 🔒 **Consistencia** entre entornos
- 📦 **Integración** con Laravel

---

## 🏗️ **ESTRUCTURA DE MIGRACIONES**

### **📁 Archivos creados:**

```
database/migrations/
├── 2025_07_31_175244_create_usuarios_table.php
├── 2025_07_31_175259_create_categorias_table.php
├── 2025_07_31_175305_create_recetas_table.php
├── 2025_07_31_175309_create_ingredientes_table.php
├── 2025_07_31_175314_create_receta_ingredientes_table.php
├── 2025_07_31_175518_create_valoraciones_table.php
├── 2025_07_31_175523_create_comentarios_table.php
├── 2025_07_31_175527_create_favoritos_table.php
├── 2025_07_31_175531_create_etiquetas_table.php
└── 2025_07_31_175535_create_receta_etiquetas_table.php
```

### **📊 Tablas incluidas:**
- ✅ **usuarios** - Gestión de usuarios
- ✅ **categorias** - Categorías de recetas
- ✅ **recetas** - Recetas principales
- ✅ **ingredientes** - Ingredientes base
- ✅ **receta_ingredientes** - Relación receta-ingrediente
- ✅ **valoraciones** - Valoraciones de usuarios
- ✅ **comentarios** - Comentarios en recetas
- ✅ **favoritos** - Recetas favoritas
- ✅ **etiquetas** - Etiquetas/tags
- ✅ **receta_etiquetas** - Relación receta-etiqueta

---

## 🌱 **ESTRUCTURA DE SEEDERS**

### **📁 Archivos creados:**

```
database/seeders/
├── DatabaseSeeder.php (actualizado)
├── CategoriasSeeder.php
├── UsuariosSeeder.php
├── IngredientesSeeder.php
├── EtiquetasSeeder.php
└── DatosCompletosSeeder.php
```

### **📊 Datos incluidos:**
- ✅ **8 categorías** (Desayunos, Platos Principales, etc.)
- ✅ **6 usuarios** con perfiles realistas
- ✅ **20 ingredientes** básicos
- ✅ **8 etiquetas** (Rápido, Vegetariano, etc.)
- ✅ **10 recetas** completas con imágenes
- ✅ **Ingredientes** para cada receta
- ✅ **Etiquetas** para cada receta
- ✅ **Valoraciones** de usuarios
- ✅ **Comentarios** realistas
- ✅ **Favoritos** distribuidos

---

## 🚀 **COMANDOS PARA DESPLEGAR**

### **1. Ejecutar migraciones (crear tablas):**
```bash
php artisan migrate
```

### **2. Ejecutar seeders (poblar datos):**
```bash
php artisan db:seed
```

### **3. Ejecutar migraciones y seeders juntos:**
```bash
php artisan migrate --seed
```

### **4. Refrescar base de datos (eliminar y recrear):**
```bash
php artisan migrate:fresh --seed
```

### **5. Rollback de migraciones:**
```bash
php artisan migrate:rollback
```

### **6. Ver estado de migraciones:**
```bash
php artisan migrate:status
```

---

## 🐳 **DESPLIEGUE EN DOCKER**

### **1. Con Docker Compose:**
```bash
# Ejecutar migraciones
docker-compose exec laravel.test php artisan migrate

# Ejecutar seeders
docker-compose exec laravel.test php artisan db:seed

# O todo junto
docker-compose exec laravel.test php artisan migrate --seed
```

### **2. Refrescar base de datos en Docker:**
```bash
docker-compose exec laravel.test php artisan migrate:fresh --seed
```

---

## 🌍 **DESPLIEGUE EN PRODUCCIÓN**

### **1. Configurar variables de entorno:**
```env
DB_CONNECTION=mysql
DB_HOST=tu_host
DB_PORT=3306
DB_DATABASE=cookshare
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### **2. Ejecutar migraciones:**
```bash
php artisan migrate --force
```

### **3. Ejecutar seeders (opcional en producción):**
```bash
php artisan db:seed --force
```

### **4. Verificar estado:**
```bash
php artisan migrate:status
```

---

## 🔧 **COMANDOS ÚTILES**

### **Ejecutar seeders individuales:**
```bash
# Solo categorías
php artisan db:seed --class=CategoriasSeeder

# Solo usuarios
php artisan db:seed --class=UsuariosSeeder

# Solo datos completos
php artisan db:seed --class=DatosCompletosSeeder
```

### **Crear migraciones adicionales:**
```bash
php artisan make:migration add_nuevo_campo_to_tabla
```

### **Crear seeders adicionales:**
```bash
php artisan make:seeder NuevoDatosSeeder
```

---

## 📊 **DATOS DE EJEMPLO INCLUIDOS**

### **👥 Usuarios:**
- `chef_maria` - Chef profesional
- `cocina_casera` - Amante de cocina tradicional
- `veggie_lover` - Especialista vegetariana
- `dulce_tentacion` - Repostero creativo
- `cocinero_novato` - Aprendiendo a cocinar
- `masterchef_home` - Cocinero amateur

### **🍽️ Recetas:**
1. **Tortilla Española Clásica** - Intermedio
2. **Pancakes Americanos** - Fácil
3. **Tarta de Chocolate** - Difícil
4. **Ensalada César** - Fácil
5. **Paella Valenciana** - Difícil
6. **Gazpacho Andaluz** - Fácil
7. **Pasta Carbonara** - Intermedio
8. **Brownie de Chocolate** - Fácil
9. **Croquetas de Jamón** - Intermedio
10. **Smoothie Verde** - Fácil

### **🏷️ Etiquetas:**
- Rápido, Vegetariano, Sin Gluten
- Bajo en Calorías, Tradicional
- Fácil, Gourmet, Económico

---

## ⚠️ **NOTAS IMPORTANTES**

### **🔒 Seguridad:**
- Las contraseñas están hasheadas con `Hash::make()`
- Todos los usuarios tienen password: `password123`
- En producción, cambiar las contraseñas

### **🖼️ Imágenes:**
- Las imágenes son URLs de Pexels (gratuitas)
- En producción, considerar imágenes locales
- Las URLs están optimizadas para rendimiento

### **📈 Rendimiento:**
- Índices creados para consultas frecuentes
- Relaciones optimizadas con foreign keys
- Restricciones para mantener integridad

### **🔄 Mantenimiento:**
- Las migraciones son reversibles
- Los seeders se pueden ejecutar múltiples veces
- Usar `migrate:fresh` para desarrollo

---

## 🎯 **FLUJO DE DESPLIEGUE RECOMENDADO**

### **1. Desarrollo local:**
```bash
php artisan migrate:fresh --seed
```

### **2. Staging:**
```bash
php artisan migrate --seed
```

### **3. Producción:**
```bash
php artisan migrate --force
# Opcional: php artisan db:seed --force
```

---

## 🚨 **SOLUCIÓN DE PROBLEMAS**

### **Error de conexión a base de datos:**
```bash
# Verificar configuración
php artisan config:clear
php artisan cache:clear
```

### **Error en migraciones:**
```bash
# Ver estado
php artisan migrate:status

# Rollback y reintentar
php artisan migrate:rollback
php artisan migrate
```

### **Error en seeders:**
```bash
# Ejecutar individualmente
php artisan db:seed --class=CategoriasSeeder
```

---

## 📞 **SOPORTE**

Si tienes problemas con las migraciones o seeders:

1. **Verificar logs:** `storage/logs/laravel.log`
2. **Verificar configuración:** `.env`
3. **Limpiar cache:** `php artisan config:clear`
4. **Revisar dependencias:** `composer install`

---

**¡Tu base de datos CookShare está lista para desplegar en cualquier entorno! 🎉** 