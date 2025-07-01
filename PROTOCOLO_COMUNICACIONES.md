# 🛰️ Protocolo de Comunicaciones CookShare

---

## 📚 Índice de Endpoints

- [Feed de Recetas](#feed-de-recetas)
- [Receta Específica](#receta-específica)
- [Crear Receta](#crear-receta)
- [Editar Receta](#editar-receta)
- [Eliminar Receta](#eliminar-receta)
- [Favoritos](#favoritos)
- [Comentarios](#comentarios)
- [Ingredientes y Etiquetas de Receta](#ingredientes-y-etiquetas-de-receta)
- [Códigos de Respuesta](#códigos-de-respuesta)

---

## 🥗 Feed de Recetas

**GET** `/feed`

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json  | ```json |
| {        | [       |
|   "usuario_id": 1 |   { "titulo": "string",<br>     "descripcion": "string",<br>     "dificultad": "string",<br>     "foto_principal": "string",<br>     "nombre_usuario": "string",<br>     "foto_perfil": "string",<br>     "etiquetas": [ { "nombre": "string", "color": "string" } ] }<br>] |
| }        |         |
|```       |```      |

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

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json | ```json |
| {<br>  "titulo": "string",<br>  "descripcion": "string",<br>  "tiempo_preparacion": 10,<br>  "tiempo_coccion": 20,<br>  "porciones": 4,<br>  "dificultad": "string",<br>  "foto_principal": "string (base64)",<br>  "instrucciones": "string",<br>  "usuario_id": 1,<br>  "categoria_id": 2,<br>  "ingredientes": [ { "ingrediente_id": 1, "cantidad": 2, "notas": "opcional" } ],<br>  "etiquetas": [1,2]<br>} | {<br>  "message": "Receta creada exitosamente",<br>  "data": { "id": 1, "titulo": "string", "ingredientes_count": 2, "etiquetas_count": 2 }<br>} |
|```      |```      |

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

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json<br>{ "receta_id": 1, "usuario_id": 2 }``` | ```json<br>{ "message": "Receta añadida a favoritos exitosamente", "data": { "favorito_id": 1 } }``` |

**DELETE** `/favorito/{id}`

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

| ENTRADAS | SALIDAS |
|----------|---------|
| ```json<br>{ "receta_id": 1, "usuario_id": 2, "comentario": "Muy buena receta!" }``` | ```json<br>{ "message": "Comentario creado exitosamente", "data": { "comentario_id": 1 } }``` |

**DELETE** `/comentario/{id}`

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

| ENTRADAS | SALIDAS |
|----------|---------|
| DELETE URL param | ```json<br>{ "message": "Ingrediente eliminado de la receta exitosamente", "data": { "receta_id": 1, "ingrediente_id": 2 } }``` |
| PUT + JSON `{ "cantidad": 5, "notas": "huevos medianos" }` | ```json<br>{ "message": "Ingrediente actualizado exitosamente", "data": { "receta_id": 1, "ingrediente_id": 2, "cantidad": 5, "notas": "huevos medianos" } }``` |

**DELETE** `/receta/{receta_id}/etiqueta/{etiqueta_id}`  
**PUT** `/receta/{receta_id}/etiqueta/{etiqueta_id}`

| ENTRADAS | SALIDAS |
|----------|---------|
| DELETE URL param | ```json<br>{ "message": "Etiqueta eliminada de la receta exitosamente", "data": { "receta_id": 1, "etiqueta_id": 6 } }``` |
| PUT + JSON `{ "nueva_etiqueta_id": 4 }` | ```json<br>{ "message": "Etiqueta actualizada exitosamente", "data": { "receta_id": 1, "etiqueta_id_anterior": 3, "nueva_etiqueta_id": 4 } }``` |

**CÓDIGOS DE RESPUESTA**

| CÓDIGO | SIGNIFICADO |
|--------|-------------|
| 400    | Campo requerido / Cantidad inválida / ID de etiqueta no enviado |
| 404    | No existe ese ingrediente/etiqueta en la receta |
| 568    | No existe una etiqueta con el ID proporcionado |

---
