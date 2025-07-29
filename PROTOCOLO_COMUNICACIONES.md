# 🛰️ Protocolo de Comunicaciones CookShare

---

## 📚 Índice de Endpoints

- [Autenticación](#autenticación)
- [Feed de Recetas](#feed-de-recetas)
- [Receta Específica](#receta-específica)
- [Crear Receta](#crear-receta)
- [Editar Receta](#editar-receta)
- [Eliminar Receta](#eliminar-receta)
- [Favoritos](#favoritos)
- [Comentarios](#comentarios)
- [Ingredientes y Etiquetas de Receta](#ingredientes-y-etiquetas-de-receta)
- [Posts Personales](#posts-personales)
- [Códigos de Respuesta](#códigos-de-respuesta)

---

## 🔐 Autenticación

### Registro de Usuario
**POST** `/register`

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json<br>{<br>  "nombre_usuario": "string",<br>  "nombre_completo": "string",<br>  "email": "string",<br>  "password": "string (mín. 6 caracteres)"<br>}``` | ```json<br>{<br>  "token": "string",<br>  "data": {<br>    "id": 1,<br>    "nombre_usuario": "string",<br>    "email": "string"<br>  }<br>}``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 201    | Usuario registrado exitosamente |
| 400    | Datos de validación incorrectos |
| 422    | Error de validación (email duplicado, etc.) |
| 429    | Demasiados intentos (3 por minuto) |

### Login de Usuario
**POST** `/login`

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json<br>{<br>  "email": "string",<br>  "password": "string"<br>}``` | ```json<br>{<br>  "token": "string",<br>  "data": {<br>    "id": 1,<br>    "nombre_usuario": "string",<br>    "email": "string"<br>  }<br>}``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 200    | Login exitoso |
| 401    | Credenciales incorrectas |
| 422    | Error de validación |
| 429    | Demasiados intentos (5 por minuto) |

### Headers de Autorización
Para todos los endpoints protegidos, incluir:
```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 🥗 Feed de Recetas

**GET** `/feed`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| Query param: usuario_id | ```json<br>[<br>  { "titulo": "string",<br>    "descripcion": "string",<br>    "dificultad": "string",<br>    "foto_principal": "string",<br>    "nombre_usuario": "string",<br>    "foto_perfil": "string",<br>    "etiquetas": [ { "nombre": "string", "color": "string" } ] }<br>]``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 400    | ID de usuario no proporcionado |
| 560    | Receta no encontrada |
| 500    | Error interno del servidor |
| 565    | No existe el usuario con el ID proporcionado |

---

## 📄 Receta Específica

**GET** `/receta/{id}`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| URL param: id | ```json<br>{<br>  "id": 1,<br>  "titulo": "string",<br>  "descripcion": "string",<br>  "tiempo_preparacion": 10,<br>  "tiempo_coccion": 20,<br>  "porciones": 4,<br>  "dificultad": "string",<br>  "foto_principal": "string",<br>  "instrucciones": "string",<br>  "fecha_creacion": "string",<br>  "fecha_actualizacion": "string",<br>  "usuario": { "nombre_usuario": "string", "foto_perfil": "string" },<br>  "categoria": { "nombre": "string" },<br>  "ingredientes": [ { "nombre": "string", "cantidad": "string", "notas": "string" } ],<br>  "etiquetas": [ { "nombre": "string", "color": "string" } ]<br>}<br>``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 560    | No existe una receta con el ID proporcionado |
| 500    | Error interno del servidor |

---

## ➕ Crear Receta

**POST** `/post`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json<br>{<br>  "titulo": "string",<br>  "descripcion": "string",<br>  "tiempo_preparacion": 10,<br>  "tiempo_coccion": 20,<br>  "porciones": 4,<br>  "dificultad": "string",<br>  "foto_principal": "string (base64)",<br>  "instrucciones": "string",<br>  "usuario_id": 1,<br>  "categoria_id": 2,<br>  "ingredientes": [ { "ingrediente_id": 1, "cantidad": 2, "notas": "opcional" } ],<br>  "etiquetas": [1,2]<br>}``` | ```json<br>{<br>  "message": "Receta creada exitosamente",<br>  "data": { "id": 1, "titulo": "string", "ingredientes_count": 2, "etiquetas_count": 2 }<br>}``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 400    | Campo requerido no proporcionado |
| 565    | No existe el usuario con el ID proporcionado |
| 566    | No existe una categoría con el ID proporcionado |
| 567    | No existe un ingrediente con el ID proporcionado |
| 568    | No existe una etiqueta con el ID proporcionado |
| 500    | Error interno del servidor |

---

## ✏️ Editar Receta

**PUT** `/receta/{id}`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| URL param: id<br>```json<br>{<br>  "titulo": "string",<br>  "descripcion": "string",<br>  ...<br>  "ingredientes": [ ... ],<br>  "etiquetas": [ ... ]<br>}``` | ```json<br>{<br>  "message": "Receta actualizada exitosamente",<br>  "data": { "receta_id": 1, "campos_actualizados": [ ... ], "ingredientes_actualizados": true, "etiquetas_actualizadas": true }<br>}``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 400    | Campo requerido no proporcionado |
| 404    | Receta no encontrada |
| 567    | No existe un ingrediente con el ID proporcionado |
| 568    | No existe una etiqueta con el ID proporcionado |

---

## 🗑️ Eliminar Receta

**DELETE** `/receta/{id}`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| URL param: id | ```json<br>{<br>  "message": "Receta eliminada exitosamente",<br>  "data": { "receta_id": 1 }<br>}``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 404    | Receta no encontrada |

---

## ⭐ Favoritos

**POST** `/favorito`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json<br>{ "receta_id": 1, "usuario_id": 2 }``` | ```json<br>{ "message": "Receta añadida a favoritos exitosamente", "data": { "favorito_id": 1 } }``` |

**DELETE** `/favorito/{id}`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| URL param: id | ```json<br>{ "message": "Favorito eliminado exitosamente", "data": { "favorito_id": 1 } }``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 400    | Campo requerido no proporcionado |
| 560    | No existe una receta con el ID proporcionado |
| 565    | No existe el usuario con el ID proporcionado |
| 404    | Favorito no encontrado |

---

## 💬 Comentarios

**POST** `/comentario`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json<br>{ "receta_id": 1, "usuario_id": 2, "comentario": "Muy buena receta!" }``` | ```json<br>{ "message": "Comentario creado exitosamente", "data": { "comentario_id": 1 } }``` |

**DELETE** `/comentario/{id}`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| URL param: id | ```json<br>{ "message": "Comentario eliminado exitosamente", "data": { "comentario_id": 1 } }``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 400    | Campo requerido no proporcionado |
| 560    | No existe una receta con el ID proporcionado |
| 565    | No existe el usuario con el ID proporcionado |
| 404    | Comentario no encontrado |

---

## 🧂 Ingredientes y Etiquetas de Receta

**DELETE** `/receta/{receta_id}/ingrediente/{ingrediente_id}`  
**PUT** `/receta/{receta_id}/ingrediente/{ingrediente_id}`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| DELETE URL param | ```json<br>{ "message": "Ingrediente eliminado de la receta exitosamente", "data": { "receta_id": 1, "ingrediente_id": 2 } }``` |
| PUT + JSON `{ "cantidad": 5, "notas": "huevos medianos" }` | ```json<br>{ "message": "Ingrediente actualizado exitosamente", "data": { "receta_id": 1, "ingrediente_id": 2, "cantidad": 5, "notas": "huevos medianos" } }``` |

**DELETE** `/receta/{receta_id}/etiqueta/{etiqueta_id}`  
**PUT** `/receta/{receta_id}/etiqueta/{etiqueta_id}`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| DELETE URL param | ```json<br>{ "message": "Etiqueta eliminada de la receta exitosamente", "data": { "receta_id": 1, "etiqueta_id": 6 } }``` |
| PUT + JSON `{ "nueva_etiqueta_id": 4 }` | ```json<br>{ "message": "Etiqueta actualizada exitosamente", "data": { "receta_id": 1, "etiqueta_id_anterior": 3, "nueva_etiqueta_id": 4 } }``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 400    | Campo requerido / Cantidad inválida / ID de etiqueta no enviado |
| 401    | Token inválido, expirado o no proporcionado |
| 404    | No existe ese ingrediente/etiqueta en la receta |
| 422    | Error de validación de datos |
| 429    | Demasiados intentos (rate limiting) |
| 568    | No existe una etiqueta con el ID proporcionado |

---

## 👤 Posts Personales

### Vista Previa de Posts Personales
**GET** `/personal_posts_preview`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| Ninguna (usuario identificado por token) | ```json<br>[<br>  {<br>    "id": 1,<br>    "titulo": "string",<br>    "dificultad": "string",<br>    "foto_principal": "string (URL)",<br>    "nombre_usuario": "string",<br>    "total_favoritos": 12<br>  }<br>]``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 200    | Lista obtenida exitosamente |
| 401    | Token inválido o no proporcionado |

### Detalles Completos de Post Personal
**GET** `/personal_posts/{id}`

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

| ENTRADAS | SALIDAS |
|----------|---------|
| URL param: id (debe pertenecer al usuario autenticado) | ```json<br>{<br>  "id": 1,<br>  "titulo": "string",<br>  "descripcion": "string",<br>  "tiempo_preparacion": 15,<br>  "tiempo_coccion": 20,<br>  "porciones": 4,<br>  "dificultad": "string",<br>  "foto_principal": "string (URL)",<br>  "instrucciones": "string",<br>  "fecha_creacion": "string",<br>  "fecha_actualizacion": "string",<br>  "nombre_usuario": "string",<br>  "foto_perfil": "string (URL)",<br>  "categoria_nombre": "string",<br>  "ingredientes": [<br>    {<br>      "nombre": "string",<br>      "unidad_medida": "string",<br>      "cantidad": "string",<br>      "notas": "string"<br>    }<br>  ],<br>  "etiquetas": [<br>    {<br>      "nombre": "string",<br>      "color": "string (hex)"<br>    }<br>  ],<br>  "comentarios": [<br>    {<br>      "id": 1,<br>      "comentario": "string",<br>      "fecha_comentario": "string",<br>      "nombre_usuario": "string",<br>      "foto_perfil": "string (URL)"<br>    }<br>  ],<br>  "valoraciones": [<br>    {<br>      "id": 1,<br>      "puntuacion": 5,<br>      "fecha_valoracion": "string",<br>      "nombre_usuario": "string",<br>      "foto_perfil": "string (URL)"<br>    }<br>  ],<br>  "promedio_valoraciones": 4.5,<br>  "total_valoraciones": 2,<br>  "total_favoritos": 15<br>}``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 200    | Receta obtenida exitosamente |
| 401    | Token inválido o no proporcionado |
| 404    | Receta no encontrada, no activa o no pertenece al usuario autenticado |

---

## 🔐 Códigos de Error de Autenticación

| CÓDIGO | SIGNIFICADO | SOLUCIÓN |
|--------|-------------|----------|
| 401    | Token inválido, expirado o no proporcionado | Hacer login nuevamente para obtener un nuevo token |
| 422    | Error de validación (email duplicado, etc.) | Verificar datos de entrada |
| 429    | Demasiados intentos de login/registro | Esperar 1 minuto antes de intentar nuevamente |

---
 