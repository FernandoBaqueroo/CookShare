# Ejemplos de Endpoints para Postman - CookShare API

## Configuración Base
- **Base URL**: `http://localhost:8000/api`
- **Content-Type**: `application/json`

---

## 🔐 AUTENTICACIÓN

### Configuración de Variables de Entorno
Antes de probar los endpoints, configura estas variables en Postman:

1. **Variables de Entorno:**
   - `base_url`: `http://localhost:8000/api`
   - `token`: (se llenará automáticamente después del login)

2. **Configurar Auto-guardado del Token:**
   En el request de login, agrega este script en la pestaña "Tests":
   ```javascript
   if (pm.response.code === 200) {
       const response = pm.response.json();
       pm.environment.set("token", response.token);
   }
   ```

---

## 1. REGISTRO DE USUARIO
**POST** `/register`

### Headers:
```
Content-Type: application/json
```

### Body:
```json
{
  "nombre_usuario": "chef_juan",
  "nombre_completo": "Juan Pérez",
  "email": "juan@ejemplo.com",
  "password": "123456"
}
```

### Response esperado:
```json
{
  "token": "1|abc123def456...",
  "data": {
    "id": 1,
    "nombre_usuario": "chef_juan",
    "email": "juan@ejemplo.com"
  }
}
```

---

## 2. LOGIN DE USUARIO
**POST** `/login`

### Headers:
```
Content-Type: application/json
```

### Body:
```json
{
  "email": "juan@ejemplo.com",
  "password": "123456"
}
```

### Response esperado:
```json
{
  "token": "2|xyz789abc123...",
  "data": {
    "id": 1,
    "nombre_usuario": "chef_juan",
    "email": "juan@ejemplo.com"
  }
}
```

---

## 3. OBTENER LISTA DE CATEGORÍAS
**GET** `/categorias/lista`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "id": 1,
      "nombre": "Desayunos"
    },
    {
      "id": 2,
      "nombre": "Platos Principales"
    },
    {
      "id": 3,
      "nombre": "Postres"
    },
    {
      "id": 4,
      "nombre": "Bebidas"
    }
  ]
}
```

---

## 4. OBTENER LISTA DE INGREDIENTES
**GET** `/ingredientes/lista`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Query Parameters (opcional):
```
busqueda=huevo
```

### Response esperado:
```json
{
  "data": [
    {
      "id": 1,
      "nombre": "Huevos",
      "unidad_medida": "unidades"
    },
    {
      "id": 2,
      "nombre": "Leche",
      "unidad_medida": "ml"
    },
    {
      "id": 3,
      "nombre": "Harina",
      "unidad_medida": "gramos"
    },
    {
      "id": 4,
      "nombre": "Azúcar",
      "unidad_medida": "gramos"
    },
    {
      "id": 5,
      "nombre": "Mantequilla",
      "unidad_medida": "gramos"
    }
  ]
}
```

---

## 5. CREAR NUEVO INGREDIENTE
**POST** `/ingredientes/crear`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "nombre": "Aceite de Oliva",
  "unidad_medida": "ml"
}
```

### Response esperado:
```json
{
  "message": "Ingrediente creado exitosamente",
  "data": {
    "id": 6,
    "nombre": "Aceite de Oliva",
    "unidad_medida": "ml"
  }
}
```

---

## 6. OBTENER LISTA DE ETIQUETAS
**GET** `/etiquetas/lista`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Query Parameters (opcional):
```
busqueda=sin
```

### Response esperado:
```json
{
  "data": [
    {
      "id": 1,
      "nombre": "Sin Gluten",
      "color": "#ffc107"
    },
    {
      "id": 2,
      "nombre": "Vegetariano",
      "color": "#28a745"
    },
    {
      "id": 3,
      "nombre": "Vegano",
      "color": "#20c997"
    },
    {
      "id": 4,
      "nombre": "Sin Lactosa",
      "color": "#17a2b8"
    },
    {
      "id": 5,
      "nombre": "Rápido",
      "color": "#fd7e14"
    }
  ]
}
```

---

## 7. CREAR RECETA
**POST** `/post`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "titulo": "Tortilla Española Clásica",
  "descripcion": "La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera",
  "tiempo_preparacion": 15,
  "tiempo_coccion": 20,
  "porciones": 4,
  "dificultad": "Intermedio",
  "foto_principal": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=",
  "instrucciones": "1. Pelar y cortar las patatas en láminas finas. 2. Freír las patatas en aceite abundante. 3. Batir los huevos y mezclar con las patatas. 4. Cuajar en la sartén por ambos lados.",
  "usuario_id": 1,
  "categoria_id": 2,
  "ingredientes": [
    {
      "ingrediente_id": 1,
      "cantidad": 6,
      "notas": "huevos grandes"
    },
    {
      "ingrediente_id": 6,
      "cantidad": 100,
      "notas": "aceite de oliva virgen extra"
    },
    {
      "ingrediente_id": 7,
      "cantidad": 2,
      "notas": "patatas medianas"
    }
  ],
  "etiquetas": [1, 2, 5]
}
```

### Response esperado:
```json
{
  "message": "Receta creada exitosamente",
  "data": {
    "id": 1,
    "titulo": "Tortilla Española Clásica",
    "ingredientes_count": 3,
    "etiquetas_count": 3
  }
}
```

---

## 8. OBTENER RECETA COMPLETA
**GET** `/receta/1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": {
    "id": 1,
    "titulo": "Tortilla Española Clásica",
    "descripcion": "La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera",
    "tiempo_preparacion": 15,
    "tiempo_coccion": 20,
    "porciones": 4,
    "dificultad": "Intermedio",
    "foto_principal": "http://localhost:8000/api/images/posts/1.jpg",
    "instrucciones": "1. Pelar y cortar las patatas en láminas finas. 2. Freír las patatas en aceite abundante. 3. Batir los huevos y mezclar con las patatas. 4. Cuajar en la sartén por ambos lados.",
    "fecha_creacion": "2025-01-20 10:30:00",
    "fecha_actualizacion": "2025-01-20 10:30:00",
    "usuario": {
      "nombre_usuario": "chef_juan",
      "foto_perfil": "http://localhost:8000/api/images/profiles/1.jpg"
    },
    "categoria": {
      "nombre": "Platos Principales"
    },
    "ingredientes": [
      {
        "nombre": "Huevos",
        "cantidad": "6 unidades",
        "notas": "huevos grandes"
      },
      {
        "nombre": "Aceite de Oliva",
        "cantidad": "100 ml",
        "notas": "aceite de oliva virgen extra"
      },
      {
        "nombre": "Patatas",
        "cantidad": "2 unidades",
        "notas": "patatas medianas"
      }
    ],
    "etiquetas": [
      {
        "nombre": "Sin Gluten",
        "color": "#ffc107"
      },
      {
        "nombre": "Vegetariano",
        "color": "#28a745"
      },
      {
        "nombre": "Rápido",
        "color": "#fd7e14"
      }
    ]
  }
}
```

---

## 9. OBTENER FEED DE RECETAS
**GET** `/feed?usuario_id=1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Tortilla Española Clásica",
      "descripcion": "La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera",
      "dificultad": "Intermedio",
      "foto_principal": "http://localhost:8000/api/images/posts/1.jpg",
      "fecha_creacion": "2025-01-20 10:30:00",
      "nombre_usuario": "chef_juan",
      "foto_perfil": "http://localhost:8000/api/images/profiles/1.jpg",
      "total_favoritos": 15,
      "etiquetas": [
        {
          "nombre": "Sin Gluten",
          "color": "#ffc107"
        },
        {
          "nombre": "Vegetariano",
          "color": "#28a745"
        },
        {
          "nombre": "Rápido",
          "color": "#fd7e14"
        }
      ]
    }
  ]
}
```

---

## 10. BUSCAR RECETAS POR ETIQUETAS
**GET** `/recetas/buscar?etiquetas_ids[]=1&etiquetas_ids[]=2&usuario_id=1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Tortilla Española Clásica",
      "descripcion": "La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera",
      "dificultad": "Intermedio",
      "foto_principal": "http://localhost:8000/api/images/posts/1.jpg",
      "fecha_creacion": "2025-01-20 10:30:00",
      "usuario": {
        "nombre_usuario": "chef_juan",
        "foto_perfil": "http://localhost:8000/api/images/profiles/1.jpg"
      },
      "etiquetas": [
        {
          "nombre": "Sin Gluten",
          "color": "#ffc107"
        },
        {
          "nombre": "Vegetariano",
          "color": "#28a745"
        },
        {
          "nombre": "Rápido",
          "color": "#fd7e14"
        }
      ]
    }
  ]
}
```

---

## 11. EDITAR RECETA
**PUT** `/receta/1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "titulo": "Tortilla Española Clásica Mejorada",
  "descripcion": "La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera. Versión mejorada con cebolla caramelizada.",
  "tiempo_preparacion": 20,
  "ingredientes": [
    {
      "ingrediente_id": 1,
      "cantidad": 6,
      "notas": "huevos grandes"
    },
    {
      "ingrediente_id": 6,
      "cantidad": 100,
      "notas": "aceite de oliva virgen extra"
    },
    {
      "ingrediente_id": 7,
      "cantidad": 2,
      "notas": "patatas medianas"
    },
    {
      "ingrediente_id": 8,
      "cantidad": 1,
      "notas": "cebolla grande"
    }
  ],
  "etiquetas": [1, 2, 5, 6]
}
```

### Response esperado:
```json
{
  "message": "Receta actualizada exitosamente",
  "data": {
    "receta_id": 1,
    "campos_actualizados": ["titulo", "descripcion", "tiempo_preparacion", "fecha_actualizacion"],
    "ingredientes_actualizados": true,
    "etiquetas_actualizadas": true
  }
}
```

---

## 12. AGREGAR A FAVORITOS
**POST** `/favorito`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "receta_id": 1,
  "usuario_id": 2
}
```

### Response esperado:
```json
{
  "message": "Receta añadida a favoritos exitosamente",
  "data": {
    "favorito_id": 1
  }
}
```

---

## 13. OBTENER FAVORITOS
**GET** `/favoritos?usuario_id=2`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "id": 1,
      "fecha_favorito": "2025-01-20 11:00:00",
      "receta": {
        "id": 1,
        "titulo": "Tortilla Española Clásica Mejorada",
        "descripcion": "La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera. Versión mejorada con cebolla caramelizada.",
        "dificultad": "Intermedio",
        "foto_principal": "http://localhost:8000/api/images/posts/1.jpg",
        "nombre_usuario": "chef_juan",
        "foto_perfil": "http://localhost:8000/api/images/profiles/1.jpg",
        "etiquetas": [
          {
            "nombre": "Sin Gluten",
            "color": "#ffc107"
          },
          {
            "nombre": "Vegetariano",
            "color": "#28a745"
          },
          {
            "nombre": "Rápido",
            "color": "#fd7e14"
          }
        ]
      }
    }
  ]
}
```

---

## 14. CREAR COMENTARIO
**POST** `/comentario`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "receta_id": 1,
  "usuario_id": 2,
  "comentario": "¡Excelente receta! La hice para el desayuno y quedó perfecta. Muy fácil de seguir las instrucciones."
}
```

### Response esperado:
```json
{
  "message": "Comentario creado exitosamente",
  "data": {
    "comentario_id": 1
  }
}
```

---

## 15. OBTENER COMENTARIOS
**GET** `/comentarios?receta_id=1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "comentario": "¡Excelente receta! La hice para el desayuno y quedó perfecta. Muy fácil de seguir las instrucciones.",
      "fecha_comentario": "2025-01-20 12:00:00",
      "usuario": {
        "nombre_usuario": "maria_cocina",
        "foto_perfil": "http://localhost:8000/api/images/profiles/2.jpg"
      }
    }
  ]
}
```

---

## 16. CREAR VALORACIÓN
**POST** `/valoracion`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "receta_id": 8,
  "usuario_id": 2,
  "puntuacion": 5
}
```

### Response esperado:
```json
{
  "message": "Valoración creada exitosamente",
  "data": {
    "valoracion_id": 1
  }
}
```

---

## 17. OBTENER VALORACIONES
**GET** `/valoraciones?receta_id=1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "titulo": "Tortilla Española Clásica Mejorada",
      "usuario": {
        "nombre_usuario": "maria_cocina",
        "foto_perfil": "http://localhost:8000/api/images/profiles/2.jpg"
      },
      "valoracion": {
        "puntuacion": 5,
        "fecha_valoracion": "2025-01-20 12:30:00"
      }
    }
  ]
}
```

---

## 18. ACTUALIZAR IMAGEN DE PERFIL
**POST** `/profile-image`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "usuario_id": 1,
  "foto_perfil": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k="
}
```

### Response esperado:
```json
{
  "message": "Imagen de perfil actualizada exitosamente",
  "data": {
    "usuario_id": 1,
    "foto_url": "http://localhost:8000/api/images/profiles/1.jpg"
  }
}
```

---

## 19. EDITAR PERFIL DE USUARIO
**PUT** `/usuario/1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "nombre_usuario": "chef_juan_mejorado",
  "email": "juan.mejorado@email.com",
  "bio": "Chef apasionado por la cocina tradicional española. Especializado en tapas y paellas."
}
```

### Response esperado:
```json
{
  "message": "Perfil de usuario actualizado exitosamente",
  "data": {
    "usuario_id": 1,
    "campos_actualizados": ["nombre_usuario", "email", "bio"]
  }
}
```

---

## 20. ELIMINAR RECETA
**DELETE** `/receta/1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "message": "Receta eliminada exitosamente",
  "data": {
    "receta_id": 1
  }
}
```

---

## 21. OBTENER PERFIL PÚBLICO DE OTRO USUARIO
**GET** `/usuario/chef_maria`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": {
    "id": 2,
    "nombre_usuario": "chef_maria",
    "nombre_completo": "María López",
    "bio": "Chef especializada en pastelería francesa",
    "foto_perfil": "http://localhost/api/images/profiles/2.jpg",
    "fecha_registro": "2025-01-05 14:30:00",
    "total_recetas": 8,
    "total_favoritos_recibidos": 45
  }
}
```

### Response error (404):
```json
{
  "message": "Usuario no encontrado"
}
```

---

## 22. OBTENER RECETAS DE UN USUARIO
**GET** `/usuario/chef_maria/recetas`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Tortilla Española Clásica",
      "descripcion": "La auténtica tortilla de patatas española",
      "tiempo_preparacion": "15",
      "tiempo_coccion": "20",
      "porciones": "4",
      "dificultad": "Intermedio",
      "foto_principal": "http://localhost/api/images/posts/1.jpg",
      "instrucciones": "1. Pelar y cortar las patatas...",
      "fecha_creacion": "2025-01-15 10:30:00",
      "fecha_actualizacion": "2025-01-15 10:30:00",
      "categoria_nombre": "Platos Principales",
      "total_favoritos": 15,
      "ingredientes": [
        {
          "nombre": "Huevos",
          "unidad_medida": "unidades",
          "cantidad": "6.00",
          "notas": "huevos grandes"
        }
      ],
      "etiquetas": [
        {
          "nombre": "Sin Gluten",
          "color": "#ffc107"
        }
      ]
    }
  ]
}
```

### Response error (404):
```json
{
  "message": "Usuario no encontrado"
}
```

---

## 22. EDITAR VALORACIÓN
**PUT** `/valoracion/1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "puntuacion": 4
}
```

### Response esperado:
```json
{
  "message": "Valoración actualizada exitosamente",
  "data": {
    "valoracion_id": 1,
    "puntuacion": 4
  }
}
```

---

## 23. OBTENER INGREDIENTES DE UNA RECETA
**GET** `/ingredientes?receta_id=1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "nombre": "Huevos",
      "cantidad": "6 unidades",
      "notas": "huevos grandes"
    },
    {
      "nombre": "Aceite de Oliva",
      "cantidad": "100 ml",
      "notas": "aceite de oliva virgen extra"
    },
    {
      "nombre": "Patatas",
      "cantidad": "2 unidades",
      "notas": "patatas medianas"
    },
    {
      "nombre": "Cebolla",
      "cantidad": "1 unidades",
      "notas": "cebolla grande"
    }
  ]
}
```

---

## 24. ELIMINAR FAVORITO
**DELETE** `/favorito/1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "message": "Favorito eliminado exitosamente",
  "data": { "favorito_id": 1 }
}
```

---

## 25. ELIMINAR COMENTARIO
**DELETE** `/comentario/1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "message": "Comentario eliminado exitosamente",
  "data": { "comentario_id": 1 }
}
```

---

## 26. ELIMINAR INGREDIENTE DE UNA RECETA
**DELETE** `/receta/1/ingrediente/2`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "message": "Ingrediente eliminado de la receta exitosamente",
  "data": { "receta_id": 1, "ingrediente_id": 2 }
}
```

---

## 27. ELIMINAR ETIQUETA DE UNA RECETA
**DELETE** `/receta/1/etiqueta/6`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "message": "Etiqueta eliminada de la receta exitosamente",
  "data": { "receta_id": 1, "etiqueta_id": 6 }
}
```

---

## 28. EDITAR INGREDIENTE DE UNA RECETA
**PUT** `/receta/1/ingrediente/2`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "cantidad": 5,
  "notas": "huevos medianos"
}
```

### Response esperado:
```json
{
  "message": "Ingrediente de la receta actualizado exitosamente",
  "data": { "receta_id": 1, "ingrediente_id": 2, "cantidad": 5, "notas": "huevos medianos" }
}
```

---

## 29. EDITAR ETIQUETA DE UNA RECETA
**PUT** `/receta/1/etiqueta/3`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Body:
```json
{
  "nueva_etiqueta_id": 4
}
```

### Response esperado:
```json
{
  "message": "Etiqueta de la receta actualizada exitosamente",
  "data": { "receta_id": 1, "etiqueta_id_anterior": 3, "nueva_etiqueta_id": 4 }
}
```

---

## 30. VISTA PREVIA DE POSTS PERSONALES
**GET** `/personal_posts_preview`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Tortilla Española Clásica",
      "dificultad": "Intermedio",
      "foto_principal": "http://localhost:8000/api/images/posts/tortilla.jpg",
      "nombre_usuario": "chef_juan",
      "total_favoritos": 12
    },
    {
      "id": 2,
      "titulo": "Paella Valenciana",
      "dificultad": "Difícil",
      "foto_principal": "http://localhost:8000/api/images/posts/paella.jpg",
      "nombre_usuario": "chef_juan",
      "total_favoritos": 8
    }
  ]
}
```

**Nota:** Este endpoint automáticamente obtiene las recetas del usuario autenticado por el token. No requiere parámetros adicionales.

---

## 31. DETALLES COMPLETOS DE POST PERSONAL
**GET** `/personal_posts/1`

### Headers:
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

### Response esperado:
```json
{
  "data": {
    "id": 1,
    "titulo": "Tortilla Española Clásica",
    "descripcion": "La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera",
    "tiempo_preparacion": 15,
    "tiempo_coccion": 20,
    "porciones": 4,
    "dificultad": "Intermedio",
    "foto_principal": "http://localhost:8000/api/images/posts/tortilla.jpg",
    "instrucciones": "1. Pelar y cortar las patatas en láminas finas. 2. Freír las patatas en aceite abundante. 3. Batir los huevos y mezclar con las patatas. 4. Cuajar en la sartén por ambos lados.",
    "fecha_creacion": "2025-06-26 12:33:44",
    "fecha_actualizacion": "2025-06-26 12:33:44",
    "nombre_usuario": "chef_juan",
    "foto_perfil": "http://localhost:8000/api/images/profiles/chef_juan.jpg",
    "categoria_nombre": "Platos Principales",
    "ingredientes": [
      {
        "nombre": "Huevos",
        "unidad_medida": "unidades",
        "cantidad": "6.00",
        "notas": "huevos grandes"
      },
      {
        "nombre": "Patatas",
        "unidad_medida": "gramos",
        "cantidad": "500.00",
        "notas": "patatas medianas"
      }
    ],
    "etiquetas": [
      {
        "nombre": "Sin Gluten",
        "color": "#ffc107"
      },
      {
        "nombre": "Vegetariano",
        "color": "#28a745"
      }
    ],
    "comentarios": [
      {
        "id": 1,
        "comentario": "¡Excelente receta! Muy fácil de seguir.",
        "fecha_comentario": "2025-07-01 10:30:00",
        "nombre_usuario": "maria_cocinera",
        "foto_perfil": "http://localhost:8000/api/images/profiles/maria.jpg"
      },
      {
        "id": 2,
        "comentario": "La hice para mi familia y les encantó.",
        "fecha_comentario": "2025-07-01 09:15:00",
        "nombre_usuario": "carlos_chef",
        "foto_perfil": "http://localhost:8000/api/images/profiles/carlos.jpg"
      }
    ],
    "valoraciones": [
      {
        "id": 1,
        "puntuacion": 5,
        "fecha_valoracion": "2025-07-01 10:30:00",
        "nombre_usuario": "maria_cocinera",
        "foto_perfil": "http://localhost:8000/api/images/profiles/maria.jpg"
      },
      {
        "id": 2,
        "puntuacion": 4,
        "fecha_valoracion": "2025-07-01 09:15:00",
        "nombre_usuario": "carlos_chef",
        "foto_perfil": "http://localhost:8000/api/images/profiles/carlos.jpg"
      }
    ],
    "promedio_valoraciones": 4.5,
    "total_valoraciones": 2,
    "total_favoritos": 15
  }
}
```

---

## Notas Importantes para Postman:

1. **Autenticación**: 
   - Primero ejecuta el registro o login para obtener un token
   - Todos los endpoints protegidos requieren `Authorization: Bearer {{token}}`
   - Los tokens expiran en 30 días

2. **Base URL**: Asegúrate de configurar la variable de entorno `base_url` como `http://localhost:8000/api`

3. **Headers**: Siempre incluye `Content-Type: application/json` y `Authorization: Bearer {{token}}` para endpoints protegidos

4. **Rate Limiting**: 
   - Registro: 3 intentos por minuto
   - Login: 5 intentos por minuto

5. **Imágenes Base64**: Para las imágenes, usa strings base64 válidos. Los ejemplos son simplificados.

6. **IDs**: Los IDs en los ejemplos son referenciales. Ajusta según los datos reales de tu base de datos.

7. **Query Parameters**: Para arrays en query parameters, usa la notación `param[]=valor1&param[]=valor2`

8. **Validaciones**: Todos los endpoints incluyen validaciones robustas, así que asegúrate de enviar todos los campos requeridos.

9. **Códigos de Error**: La API devuelve códigos de error específicos (400, 401, 404, 422, 429, 560-569) con mensajes descriptivos.

10. **Tokens**: Guarda el token automáticamente usando el script en la pestaña "Tests" del request de login.

---

## Orden Recomendado de Pruebas:

1. **Registro o Login** para obtener token
2. Obtener listas básicas (categorías, ingredientes, etiquetas)
3. Crear ingrediente si es necesario
4. Crear receta
5. Obtener receta completa
6. Obtener feed
7. Buscar por etiquetas
8. Editar receta
9. Agregar a favoritos
10. Crear comentario y valoración
11. Probar otros endpoints

¡Listo para probar en Postman! 🚀 