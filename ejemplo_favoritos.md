# Ejemplo de Total de Favoritos - Receta 1

## Estructura de Datos de Ejemplo

### Tabla `favoritos`
```sql
+----+----------+-----------+------------------+
| id | receta_id| usuario_id| fecha_favorito   |
+----+----------+-----------+------------------+
| 1  | 1        | 2         | 2025-07-29 10:00 |
| 2  | 1        | 3         | 2025-07-29 11:30 |
| 3  | 1        | 5         | 2025-07-29 14:15 |
| 4  | 2        | 2         | 2025-07-29 16:45 |
| 5  | 1        | 8         | 2025-07-29 18:20 |
+----+----------+-----------+------------------+
```

### Consulta SQL para contar favoritos de la receta 1
```sql
SELECT COUNT(*) as total_favoritos 
FROM favoritos 
WHERE receta_id = 1;
```

**Resultado: 4 favoritos**

## Ejemplo de Respuesta del Endpoint

### GET /api/personal_posts_preview
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Tortilla Española Clásica",
      "dificultad": "Intermedio",
      "foto_principal": "http://localhost/api/images/posts/1.jpg",
      "nombre_usuario": "chef_juan",
      "total_favoritos": 4
    },
    {
      "id": 2,
      "titulo": "Paella Valenciana",
      "dificultad": "Difícil",
      "foto_principal": "http://localhost/api/images/posts/2.jpg",
      "nombre_usuario": "chef_juan",
      "total_favoritos": 1
    }
  ]
}
```

### GET /api/personal_posts/1
```json
{
  "data": {
    "id": 1,
    "titulo": "Tortilla Española Clásica",
    "descripcion": "La auténtica tortilla de patatas española",
    "tiempo_preparacion": 15,
    "tiempo_coccion": 20,
    "porciones": 4,
    "dificultad": "Intermedio",
    "foto_principal": "http://localhost/api/images/posts/1.jpg",
    "instrucciones": "1. Pelar y cortar las patatas...",
    "fecha_creacion": "2025-06-26 12:33:44",
    "fecha_actualizacion": "2025-06-26 12:33:44",
    "nombre_usuario": "chef_juan",
    "foto_perfil": "http://localhost/api/images/profiles/1.jpg",
    "categoria_nombre": "Platos Principales",
    "ingredientes": [
      {
        "id": 1,
        "nombre": "Huevos",
        "unidad_medida": "unidades",
        "cantidad": 6.00,
        "notas": "huevos grandes"
      }
    ],
    "etiquetas": [
      {
        "id": 1,
        "nombre": "Sin Gluten",
        "color": "#ffc107"
      }
    ],
    "comentarios": [
      {
        "id": 1,
        "comentario": "¡Excelente receta!",
        "fecha_comentario": "2025-07-29 10:30:00",
        "nombre_usuario": "usuario1",
        "foto_perfil": "http://localhost/api/images/profiles/2.jpg"
      }
    ],
    "valoraciones": [
      {
        "id": 1,
        "puntuacion": 5,
        "fecha_valoracion": "2025-07-29 10:30:00",
        "nombre_usuario": "usuario1",
        "foto_perfil": "http://localhost/api/images/profiles/2.jpg"
      }
    ],
    "promedio_valoraciones": 4.5,
    "total_valoraciones": 2,
    "total_favoritos": 4
  }
}
```

## Código PHP que Calcula los Favoritos

```php
// En el endpoint personal_posts_preview
$totalFavoritos = DB::table('favoritos')
    ->where('receta_id', $receta->id)
    ->count();

$receta->total_favoritos = $totalFavoritos;

// En el endpoint personal_posts/{id}
$totalFavoritos = DB::table('favoritos')
    ->where('receta_id', $id)
    ->count();

$receta->total_favoritos = $totalFavoritos;
```

## Explicación para el Frontend

El campo `total_favoritos` indica cuántos usuarios han marcado esa receta como favorita. Este número se calcula en tiempo real cada vez que se hace una petición al endpoint.

- **En `personal_posts_preview`**: Se muestra el total de favoritos para cada receta en la lista de tarjetas
- **En `personal_posts/{id}`**: Se muestra el total de favoritos en la vista detallada de la receta

Este campo es útil para mostrar la popularidad de las recetas y puede usarse para ordenar o filtrar las recetas por popularidad. 