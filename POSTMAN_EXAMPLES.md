# Ejemplos de Endpoints para Postman - CookShare API

## Configuración Base
- **Base URL**: `http://localhost:8000/api`
- **Content-Type**: `application/json`

---

## 1. OBTENER LISTA DE CATEGORÍAS
**GET** `/categorias/lista`

### Headers:
```
Content-Type: application/json
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

## 2. OBTENER LISTA DE INGREDIENTES
**GET** `/ingredientes/lista`

### Headers:
```
Content-Type: application/json
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

## 3. CREAR NUEVO INGREDIENTE
**POST** `/ingredientes/crear`

### Headers:
```
Content-Type: application/json
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

## 4. OBTENER LISTA DE ETIQUETAS
**GET** `/etiquetas/lista`

### Headers:
```
Content-Type: application/json
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

## 5. CREAR RECETA
**POST** `/post`

### Headers:
```
Content-Type: application/json
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

## 6. OBTENER RECETA COMPLETA
**GET** `/receta/1`

### Headers:
```
Content-Type: application/json
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

## 7. OBTENER FEED DE RECETAS
**GET** `/feed?usuario_id=1`

### Headers:
```
Content-Type: application/json
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

## 8. BUSCAR RECETAS POR ETIQUETAS
**GET** `/recetas/buscar?etiquetas_ids[]=1&etiquetas_ids[]=2&usuario_id=1`

### Headers:
```
Content-Type: application/json
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

## 9. EDITAR RECETA
**PUT** `/receta/1`

### Headers:
```
Content-Type: application/json
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

## 10. AGREGAR A FAVORITOS
**POST** `/favorito`

### Headers:
```
Content-Type: application/json
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

## 11. OBTENER FAVORITOS
**GET** `/favoritos?usuario_id=2`

### Headers:
```
Content-Type: application/json
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

## 12. CREAR COMENTARIO
**POST** `/comentario`

### Headers:
```
Content-Type: application/json
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

## 13. OBTENER COMENTARIOS
**GET** `/comentarios?receta_id=1`

### Headers:
```
Content-Type: application/json
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

## 14. CREAR VALORACIÓN
**POST** `/valoracion`

### Headers:
```
Content-Type: application/json
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

## 15. OBTENER VALORACIONES
**GET** `/valoraciones?receta_id=1`

### Headers:
```
Content-Type: application/json
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

## 16. ACTUALIZAR IMAGEN DE PERFIL
**POST** `/profile-image`

### Headers:
```
Content-Type: application/json
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

## 17. EDITAR PERFIL DE USUARIO
**PUT** `/usuario/1`

### Headers:
```
Content-Type: application/json
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

## 18. ELIMINAR RECETA
**DELETE** `/receta/1`

### Headers:
```
Content-Type: application/json
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

## 19. EDITAR VALORACIÓN
**PUT** `/valoracion/1`

### Headers:
```
Content-Type: application/json
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

## 20. OBTENER INGREDIENTES DE UNA RECETA
**GET** `/ingredientes?receta_id=1`

### Headers:
```
Content-Type: application/json
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

## 21. ELIMINAR FAVORITO
**DELETE** `/favorito/1`

### Headers:
```
Content-Type: application/json
```

### Response esperado:
```json
{
  "message": "Favorito eliminado exitosamente",
  "data": { "favorito_id": 1 }
}
```

---

## 22. ELIMINAR COMENTARIO
**DELETE** `/comentario/1`

### Headers:
```
Content-Type: application/json
```

### Response esperado:
```json
{
  "message": "Comentario eliminado exitosamente",
  "data": { "comentario_id": 1 }
}
```

---

## 23. ELIMINAR INGREDIENTE DE UNA RECETA
**DELETE** `/receta/1/ingrediente/2`

### Headers:
```
Content-Type: application/json
```

### Response esperado:
```json
{
  "message": "Ingrediente eliminado de la receta exitosamente",
  "data": { "receta_id": 1, "ingrediente_id": 2 }
}
```

---

## 24. ELIMINAR ETIQUETA DE UNA RECETA
**DELETE** `/receta/1/etiqueta/6`

### Headers:
```
Content-Type: application/json
```

### Response esperado:
```json
{
  "message": "Etiqueta eliminada de la receta exitosamente",
  "data": { "receta_id": 1, "etiqueta_id": 6 }
}
```

---

## 25. EDITAR INGREDIENTE DE UNA RECETA
**PUT** `/receta/1/ingrediente/2`

### Headers:
```
Content-Type: application/json
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

## 26. EDITAR ETIQUETA DE UNA RECETA
**PUT** `/receta/1/etiqueta/3`

### Headers:
```
Content-Type: application/json
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

## Notas Importantes para Postman:

1. **Base URL**: Asegúrate de configurar la variable de entorno `base_url` como `http://localhost:8000/api`

2. **Headers**: Siempre incluye `Content-Type: application/json`

3. **Imágenes Base64**: Para las imágenes, usa strings base64 válidos. Los ejemplos son simplificados.

4. **IDs**: Los IDs en los ejemplos son referenciales. Ajusta según los datos reales de tu base de datos.

5. **Query Parameters**: Para arrays en query parameters, usa la notación `param[]=valor1&param[]=valor2`

6. **Validaciones**: Todos los endpoints incluyen validaciones robustas, así que asegúrate de enviar todos los campos requeridos.

7. **Códigos de Error**: La API devuelve códigos de error específicos (400, 404, 560-569) con mensajes descriptivos.

---

## Orden Recomendado de Pruebas:

1. Obtener listas básicas (categorías, ingredientes, etiquetas)
2. Crear ingrediente si es necesario
3. Crear receta
4. Obtener receta completa
5. Obtener feed
6. Buscar por etiquetas
7. Editar receta
8. Agregar a favoritos
9. Crear comentario y valoración
10. Probar otros endpoints

¡Listo para probar en Postman! 🚀 