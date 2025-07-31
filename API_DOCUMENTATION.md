# Documentación de la API CookShare

## Resumen de Cambios

La API ha sido rediseñada para soportar la nueva estructura de base de datos que incluye:
- **Ingredientes**: Sistema de ingredientes con buscador y creación dinámica
- **Etiquetas**: Sistema de etiquetas para categorización y filtrado
- **Categorías**: Sistema de categorías para organizar recetas
- **Relaciones mejoradas**: Tablas intermedias para ingredientes y etiquetas
- **Autenticación**: Sistema completo de autenticación con Laravel Sanctum

## 🔐 Sistema de Autenticación

La API utiliza **Laravel Sanctum** para la autenticación basada en tokens. Todos los endpoints protegidos requieren un token válido en el header de autorización.

### Endpoints de Autenticación

#### 1. Registro de Usuario (POST /register)
**Propósito:** Crear una nueva cuenta de usuario

**Parámetros requeridos:**
```json
{
  "nombre_usuario": "string (único)",
  "nombre_completo": "string",
  "email": "string (único)",
  "password": "string (mínimo 6 caracteres)"
}
```

**Respuesta exitosa (201):**
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

**Validaciones:**
- `nombre_usuario`: Requerido, único en la tabla usuarios
- `nombre_completo`: Requerido
- `email`: Requerido, formato válido, único en la tabla usuarios
- `password`: Requerido, mínimo 6 caracteres

**Rate Limiting:** 3 intentos por minuto

#### 2. Login de Usuario (POST /login)
**Propósito:** Autenticar un usuario existente

**Parámetros requeridos:**
```json
{
  "email": "string",
  "password": "string"
}
```

**Respuesta exitosa (200):**
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

**Respuesta de error (401):**
```json
{
  "message": "Credenciales incorrectas"
}
```

**Rate Limiting:** 5 intentos por minuto

### Uso de Tokens

#### Headers Requeridos para Endpoints Protegidos
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Ejemplo de Uso
```bash
curl -X GET "http://localhost:8000/api/feed?usuario_id=1" \
  -H "Authorization: Bearer 2|xyz789abc123..." \
  -H "Content-Type: application/json"
```

### Características del Sistema de Autenticación

1. **Tokens de Acceso:** Generados automáticamente al registrar o hacer login
2. **Expiración:** Los tokens expiran en 30 días
3. **Middleware:** Todos los endpoints protegidos usan `auth:sanctum` y `check.token.expiration`
4. **Seguridad:** Rate limiting para prevenir ataques de fuerza bruta
5. **Validación:** Verificación automática de tokens en cada request

### Códigos de Error de Autenticación

- `401 Unauthorized`: Token inválido, expirado o no proporcionado
- `422 Validation Error`: Datos de entrada inválidos
- `429 Too Many Requests`: Demasiados intentos de login/registro

## Estructura de Base de Datos

### Tablas Principales
- `recetas` (id, titulo, descripcion, tiempo_preparacion, tiempo_coccion, porciones, dificultad, foto_principal, instrucciones, usuario_id, categoria_id, fecha_creacion, fecha_actualizacion, activa)
- `ingredientes` (id, nombre, unidad_medida)
- `etiquetas` (id, nombre, color)
- `categorias` (id, nombre)

### Tablas de Relación
- `receta_ingredientes` (id, receta_id, ingrediente_id, cantidad, notas)
- `receta_etiquetas` (id, receta_id, etiqueta_id)

## Endpoints Actualizados

### 1. Crear Receta (POST /post)
**Cambios principales:**
- Ahora requiere `ingredientes` y `etiquetas`
- Validación mejorada de ingredientes y etiquetas
- Procesamiento de relaciones en tablas intermedias

**Parámetros requeridos:**
```json
{
  "titulo": "string",
  "descripcion": "string",
  "tiempo_preparacion": "number",
  "tiempo_coccion": "number",
  "porciones": "number",
  "dificultad": "string (Fácil|Intermedio|Difícil)",
  "foto_principal": "string (base64)",
  "instrucciones": "string",
  "usuario_id": "number",
  "categoria_id": "number",
  "ingredientes": [
    {
      "ingrediente_id": "number",
      "cantidad": "number",
      "notas": "string (opcional)"
    }
  ],
  "etiquetas": ["number"] // Array de IDs de etiquetas
}
```

### 2. Obtener Receta Completa (GET /receta/{id})
**Cambios principales:**
- Ahora incluye ingredientes y etiquetas
- Información completa de la receta
- URLs de imágenes construidas automáticamente

**Respuesta:**
```json
{
  "data": {
    "id": "number",
    "titulo": "string",
    "descripcion": "string",
    "tiempo_preparacion": "number",
    "tiempo_coccion": "number",
    "porciones": "number",
    "dificultad": "string",
    "foto_principal": "string (URL)",
    "instrucciones": "string",
    "fecha_creacion": "string",
    "fecha_actualizacion": "string",
    "usuario": {
      "nombre_usuario": "string",
      "foto_perfil": "string (URL)"
    },
    "categoria": {
      "nombre": "string"
    },
    "ingredientes": [
      {
        "nombre": "string",
        "cantidad": "string (con unidad)",
        "notas": "string"
      }
    ],
    "etiquetas": [
      {
        "nombre": "string",
        "color": "string (hex)"
      }
    ]
  }
}
```

### 3. Editar Receta (PUT /receta/{id})
**Cambios principales:**
- Soporte para actualizar ingredientes y etiquetas
- Validación mejorada
- Actualización de relaciones

**Parámetros opcionales:**
```json
{
  "titulo": "string",
  "descripcion": "string",
  "tiempo_preparacion": "number",
  "tiempo_coccion": "number",
  "porciones": "number",
  "dificultad": "string",
  "instrucciones": "string",
  "foto_principal": "string (base64)",
  "categoria_id": "number",
  "ingredientes": [
    {
      "ingrediente_id": "number",
      "cantidad": "number",
      "notas": "string (opcional)"
    }
  ],
  "etiquetas": ["number"]
}
```

### 4. Feed de Recetas (GET /feed)
**Propósito:** Obtener feed de recetas de otros usuarios con información básica y métricas de popularidad

**Parámetros requeridos:**
- `usuario_id`: ID del usuario autenticado (para excluir sus propias recetas)

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Respuesta:**
```json
{
  "data": [
    {
      "id": "number",
      "titulo": "string",
      "descripcion": "string",
      "dificultad": "string",
      "foto_principal": "string (URL)",
      "fecha_creacion": "string",
      "nombre_usuario": "string",
      "foto_perfil": "string (URL)",
      "total_favoritos": "number",
      "etiquetas": [
        {
          "nombre": "string",
          "color": "string (hex)"
        }
      ]
    }
  ]
}
```

**Características:**
- ✅ **Recuento de favoritos:** Incluye `total_favoritos` para mostrar popularidad
- ✅ **Etiquetas básicas:** Incluye etiquetas con nombre y color
- ✅ **Información de fecha:** Ordenado por fecha de creación (más recientes primero)
- ✅ **Filtrado inteligente:** Excluye recetas del usuario autenticado
- ✅ **Solo recetas activas:** Filtrado por `activa = true`
- ✅ **URLs construidas:** Todas las imágenes tienen URLs completas

**Códigos de respuesta:**
- `200`: Feed obtenido exitosamente
- `400`: ID de usuario no proporcionado
- `565`: No existe el usuario con el ID proporcionado
- `500`: Error interno del servidor

**Ejemplo de uso:**
```bash
curl -X GET "http://localhost/api/feed?usuario_id=1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### 5. Favoritos (GET /favoritos)
**Cambios principales:**
- Incluye etiquetas de las recetas
- Filtrado por recetas activas
- Información más completa

## Nuevos Endpoints

### 1. Lista de Ingredientes (GET /ingredientes/lista)
**Propósito:** Obtener lista de ingredientes disponibles para el buscador

**Parámetros:**
- `busqueda` (opcional): Término para filtrar ingredientes

**Respuesta:**
```json
{
  "data": [
    {
      "id": "number",
      "nombre": "string",
      "unidad_medida": "string"
    }
  ]
}
```

### 2. Crear Ingrediente (POST /ingredientes/crear)
**Propósito:** Crear un nuevo ingrediente si no existe en la lista

**Parámetros:**
```json
{
  "nombre": "string",
  "unidad_medida": "string"
}
```

### 3. Lista de Etiquetas (GET /etiquetas/lista)
**Propósito:** Obtener lista de etiquetas disponibles

**Parámetros:**
- `busqueda` (opcional): Término para filtrar etiquetas

**Respuesta:**
```json
{
  "data": [
    {
      "id": "number",
      "nombre": "string",
      "color": "string (hex)"
    }
  ]
}
```

### 4. Lista de Categorías (GET /categorias/lista)
**Propósito:** Obtener lista de categorías disponibles

**Respuesta:**
```json
{
  "data": [
    {
      "id": "number",
      "nombre": "string"
    }
  ]
}
```

### 5. Buscar Recetas por Etiquetas (GET /recetas/buscar)
**Propósito:** Buscar recetas que contengan ciertas etiquetas

**Parámetros:**
- `etiquetas_ids`: Array de IDs de etiquetas
- `usuario_id` (opcional): Excluir recetas del usuario

**Respuesta:**
```json
{
  "data": [
    {
      "id": "number",
      "titulo": "string",
      "descripcion": "string",
      "dificultad": "string",
      "foto_principal": "string (URL)",
      "fecha_creacion": "string",
      "usuario": {
        "nombre_usuario": "string",
        "foto_perfil": "string (URL)"
      },
      "etiquetas": [
        {
          "nombre": "string",
          "color": "string (hex)"
        }
      ]
    }
  ]
}
```

### 6. Eliminar Receta (DELETE /receta/{id})
**Propósito:** Marcar una receta como inactiva (no se elimina físicamente)

**Respuesta:**
```json
{
  "message": "Receta eliminada exitosamente",
  "data": {
    "receta_id": "number"
  }
}
```

### DELETE /favorito/{id}
Elimina un favorito por su ID.

**Respuesta:**
```json
{
  "message": "Favorito eliminado exitosamente",
  "data": { "favorito_id": 1 }
}
```

### DELETE /comentario/{id}
Elimina (marca como inactivo) un comentario por su ID.

**Respuesta:**
```json
{
  "message": "Comentario eliminado exitosamente",
  "data": { "comentario_id": 1 }
}
```

### DELETE /receta/{receta_id}/ingrediente/{ingrediente_id}
Elimina un ingrediente específico de una receta.

**Respuesta:**
```json
{
  "message": "Ingrediente eliminado de la receta exitosamente",
  "data": { "receta_id": 1, "ingrediente_id": 2 }
}
```

### DELETE /receta/{receta_id}/etiqueta/{etiqueta_id}
Elimina una etiqueta específica de una receta.

**Respuesta:**
```json
{
  "message": "Etiqueta eliminada de la receta exitosamente",
  "data": { "receta_id": 1, "etiqueta_id": 3 }
}
```

### PUT /receta/{receta_id}/ingrediente/{ingrediente_id}
Edita la cantidad o notas de un ingrediente de una receta.

**Body:**
```json
{
  "cantidad": 5,
  "notas": "huevos medianos"
}
```
**Respuesta:**
```json
{
  "message": "Ingrediente de la receta actualizado exitosamente",
  "data": { "receta_id": 1, "ingrediente_id": 2, "cantidad": 5, "notas": "huevos medianos" }
}
```

### PUT /receta/{receta_id}/etiqueta/{etiqueta_id}
Cambia una etiqueta de una receta por otra.

**Body:**
```json
{
  "nueva_etiqueta_id": 4
}
```
**Respuesta:**
```json
{
  "message": "Etiqueta de la receta actualizada exitosamente",
  "data": { "receta_id": 1, "etiqueta_id_anterior": 3, "nueva_etiqueta_id": 4 }
}
```

### 7. Vista Previa de Posts Personales (GET /personal_posts_preview)
**Propósito:** Obtener información básica de las recetas del usuario autenticado para mostrar en tarjetas

**Parámetros:**
- Ninguno (el usuario se identifica automáticamente por el token)

**Respuesta:**
```json
{
  "data": [
    {
      "id": "number",
      "titulo": "string",
      "dificultad": "string",
      "foto_principal": "string (URL)",
      "nombre_usuario": "string",
      "total_favoritos": "number"
    }
  ]
}
```

**Códigos de respuesta:**
- `200`: Lista obtenida exitosamente
- `401`: Token inválido o no proporcionado

### 8. Detalles Completos de Post Personal (GET /personal_posts/{id})
**Propósito:** Obtener información completa de una receta específica del usuario autenticado

**Parámetros:**
- `id`: ID de la receta a obtener (debe pertenecer al usuario autenticado)

**Respuesta:**
```json
{
  "data": {
    "id": "number",
    "titulo": "string",
    "descripcion": "string",
    "tiempo_preparacion": "number",
    "tiempo_coccion": "number",
    "porciones": "number",
    "dificultad": "string",
    "foto_principal": "string (URL)",
    "instrucciones": "string",
    "fecha_creacion": "string",
    "fecha_actualizacion": "string",
    "nombre_usuario": "string",
    "foto_perfil": "string (URL)",
    "categoria_nombre": "string",
    "ingredientes": [
      {
        "nombre": "string",
        "unidad_medida": "string",
        "cantidad": "string",
        "notas": "string"
      }
    ],
    "etiquetas": [
      {
        "nombre": "string",
        "color": "string (hex)"
      }
    ],
    "comentarios": [
      {
        "id": "number",
        "comentario": "string",
        "fecha_comentario": "string",
        "nombre_usuario": "string",
        "foto_perfil": "string (URL)"
      }
    ],
    "valoraciones": [
      {
        "id": "number",
        "puntuacion": "number (1-5)",
        "fecha_valoracion": "string",
        "nombre_usuario": "string",
        "foto_perfil": "string (URL)"
      }
    ],
    "promedio_valoraciones": "number (1-5)",
    "total_valoraciones": "number",
    "total_favoritos": "number"
  }
}
```

**Códigos de respuesta:**
- `200`: Receta obtenida exitosamente
- `401`: Token inválido o no proporcionado
- `404`: Receta no encontrada, no activa o no pertenece al usuario autenticado

## Códigos de Error Actualizados

- `560`: No existe una receta con el ID proporcionado
- `561`: La receta no tiene ingredientes registrados
- `562`: El usuario no tiene recetas favoritas
- `563`: La receta no tiene comentarios
- `564`: La receta no tiene valoraciones
- `565`: No existe un usuario con el ID proporcionado
- `566`: No existe una categoría con el ID proporcionado
- `567`: No existe un ingrediente con el ID proporcionado
- `568`: No existe una etiqueta con el ID proporcionado
- `569`: No se encontraron recetas con las etiquetas especificadas

## Funciones Helper Nuevas

Se han agregado nuevas funciones helper en `functions/api.php`:

- `validarIngredienteExiste($ingredienteId)`: Valida que un ingrediente existe
- `validarEtiquetaExiste($etiquetaId)`: Valida que una etiqueta existe
- `validarIngredientes($ingredientes)`: Valida un array de ingredientes
- `validarEtiquetas($etiquetas)`: Valida un array de etiquetas

## Flujo de Creación de Receta

1. **Obtener datos básicos:**
   - GET `/categorias/lista` - Para obtener categorías disponibles
   - GET `/ingredientes/lista` - Para obtener ingredientes disponibles
   - GET `/etiquetas/lista` - Para obtener etiquetas disponibles

2. **Crear ingrediente si no existe:**
   - POST `/ingredientes/crear` - Si el ingrediente no está en la lista

3. **Crear receta:**
   - POST `/post` - Con todos los datos incluyendo ingredientes y etiquetas

4. **Ver receta completa:**
   - GET `/receta/{id}` - Para ver todos los detalles de la receta

## Consideraciones Importantes

1. **Ingredientes:** Los usuarios pueden buscar ingredientes existentes o crear nuevos
2. **Etiquetas:** Sistema de etiquetas para mejor categorización y búsqueda
3. **Categorías:** Sistema de categorías para organizar recetas
4. **Eliminación:** Las recetas se marcan como inactivas, no se eliminan físicamente
5. **Validaciones:** Validaciones mejoradas para todos los campos
6. **Relaciones:** Manejo automático de relaciones en tablas intermedias

---

## 🎨 Guía para el Frontend

### **Configuración Base**

**URL Base:** `http://localhost` (puerto 80)

**Headers estándar para todas las peticiones:**
```javascript
headers: {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

**Headers para endpoints protegidos:**
```javascript
headers: {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': 'Bearer ' + token
}
```

### **Flujo de Autenticación**

1. **Registro o Login** para obtener token
2. **Guardar token** en localStorage o estado de la aplicación
3. **Incluir token** en todas las peticiones protegidas

### **Ejemplos de Uso para el Feed**

#### **Obtener Feed de Recetas:**
```javascript
const obtenerFeed = async (usuarioId, token) => {
  try {
    const response = await fetch(`/api/feed?usuario_id=${usuarioId}`, {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    return data.data; // Array de recetas
  } catch (error) {
    console.error('Error al obtener feed:', error);
  }
};
```

#### **Mostrar Recetas en el Feed:**
```javascript
const mostrarRecetas = (recetas) => {
  recetas.forEach(receta => {
    const recetaElement = document.createElement('div');
    recetaElement.className = 'receta-card';
    
    recetaElement.innerHTML = `
      <img src="${receta.foto_principal}" alt="${receta.titulo}" />
      <h3>${receta.titulo}</h3>
      <p>${receta.descripcion}</p>
      <div class="receta-meta">
        <span class="usuario">
          <img src="${receta.foto_perfil}" alt="${receta.nombre_usuario}" />
          ${receta.nombre_usuario}
        </span>
        <span class="dificultad">${receta.dificultad}</span>
        <span class="favoritos">❤️ ${receta.total_favoritos}</span>
      </div>
      <div class="etiquetas">
        ${receta.etiquetas.map(etiqueta => 
          `<span class="etiqueta" style="background-color: ${etiqueta.color}">
            ${etiqueta.nombre}
          </span>`
        ).join('')}
      </div>
    `;
    
    document.getElementById('feed-container').appendChild(recetaElement);
  });
};
```

#### **Ordenar por Popularidad:**
```javascript
const ordenarPorFavoritos = (recetas) => {
  return recetas.sort((a, b) => b.total_favoritos - a.total_favoritos);
};

// Usar
const recetasPopulares = ordenarPorFavoritos(recetas);
```

#### **Filtrar Recetas Populares:**
```javascript
const filtrarRecetasPopulares = (recetas, minFavoritos = 10) => {
  return recetas.filter(receta => receta.total_favoritos >= minFavoritos);
};

// Usar
const recetasConMuchosFavoritos = filtrarRecetasPopulares(recetas, 15);
```

### **Manejo de Estados**

#### **Estados de Carga:**
```javascript
const [feed, setFeed] = useState([]);
const [loading, setLoading] = useState(false);
const [error, setError] = useState(null);

const cargarFeed = async () => {
  setLoading(true);
  setError(null);
  
  try {
    const recetas = await obtenerFeed(usuarioId, token);
    setFeed(recetas);
  } catch (err) {
    setError('Error al cargar el feed');
  } finally {
    setLoading(false);
  }
};
```

#### **Manejo de Errores:**
```javascript
const manejarError = (error) => {
  switch (error.status) {
    case 400:
      return 'ID de usuario no proporcionado';
    case 401:
      return 'Token inválido o expirado';
    case 565:
      return 'Usuario no encontrado';
    default:
      return 'Error interno del servidor';
  }
};
```

### **Optimizaciones Recomendadas**

1. **Caché de datos:** Guardar feed en localStorage para carga rápida
2. **Paginación:** Implementar paginación para feeds grandes
3. **Actualización automática:** Refrescar feed cada cierto tiempo
4. **Lazy loading:** Cargar imágenes solo cuando sean visibles
5. **Debounce:** Evitar múltiples peticiones simultáneas

### **Ejemplo Completo de Componente React**

```jsx
import React, { useState, useEffect } from 'react';

const FeedComponent = ({ usuarioId, token }) => {
  const [recetas, setRecetas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [orden, setOrden] = useState('fecha'); // 'fecha' o 'favoritos'

  useEffect(() => {
    cargarFeed();
  }, [usuarioId]);

  const cargarFeed = async () => {
    setLoading(true);
    try {
      const response = await fetch(`/api/feed?usuario_id=${usuarioId}`, {
        headers: {
          'Authorization': 'Bearer ' + token,
          'Content-Type': 'application/json'
        }
      });
      
      const data = await response.json();
      
      // Ordenar según el estado
      const recetasOrdenadas = orden === 'favoritos' 
        ? data.data.sort((a, b) => b.total_favoritos - a.total_favoritos)
        : data.data;
        
      setRecetas(recetasOrdenadas);
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Cargando...</div>;

  return (
    <div className="feed-container">
      <div className="filtros">
        <button onClick={() => setOrden('fecha')}>Por fecha</button>
        <button onClick={() => setOrden('favoritos')}>Por popularidad</button>
      </div>
      
      <div className="recetas-grid">
        {recetas.map(receta => (
          <div key={receta.id} className="receta-card">
            <img src={receta.foto_principal} alt={receta.titulo} />
            <h3>{receta.titulo}</h3>
            <p>{receta.descripcion}</p>
            <div className="receta-stats">
              <span>❤️ {receta.total_favoritos}</span>
              <span>{receta.dificultad}</span>
            </div>
            <div className="etiquetas">
              {receta.etiquetas.map(etiqueta => (
                <span 
                  key={etiqueta.nombre}
                  className="etiqueta"
                  style={{ backgroundColor: etiqueta.color }}
                >
                  {etiqueta.nombre}
                </span>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default FeedComponent;
```

### **Notas Importantes para el Frontend**

1. **URLs de imágenes:** Todas las imágenes tienen URLs completas construidas automáticamente
2. **Recuento de favoritos:** Se actualiza en tiempo real en cada consulta
3. **Ordenamiento:** Las recetas vienen ordenadas por fecha de creación (más recientes primero)
4. **Filtrado:** El feed excluye automáticamente las recetas del usuario autenticado
5. **Autenticación:** Todos los endpoints requieren token válido
6. **Rate limiting:** Respeta los límites de peticiones por minuto

---

## 16. OBTENER PERFIL PÚBLICO DE OTRO USUARIO

**Endpoint:** `GET /usuario/{nombre_usuario}`

**Descripción:** Obtiene el perfil público de otro usuario con estadísticas usando su nombre de usuario.

**Autenticación:** Requerida (Bearer Token)

**Parámetros de URL:**
- `nombre_usuario` (string, requerido): Nombre de usuario a consultar

**Respuesta Exitosa (200):**
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

**Códigos de Error:**
- `401`: Usuario no autenticado
- `404`: Usuario no encontrado

**Ejemplo de uso:**
```bash
curl -X GET "http://localhost/api/usuario/chef_maria" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

**Notas importantes:**
- Los nombres de usuario son únicos en la base de datos
- Este endpoint es más práctico para el frontend ya que no requiere conocer el ID
- Incluye estadísticas: total de recetas activas y total de favoritos recibidos
- La foto de perfil se devuelve con URL completa
- Solo muestra recetas activas en el conteo

## 17. OBTENER RECETAS DE UN USUARIO

**Endpoint:** `GET /usuario/{nombre_usuario}/recetas`

**Descripción:** Obtiene todas las recetas de un usuario con información completa.

**Autenticación:** Requerida (Bearer Token)

**Parámetros de URL:**
- `nombre_usuario` (string, requerido): Nombre de usuario

**Respuesta Exitosa (200):**
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
      ]
    }
  ]
}
```

**Códigos de Error:**
- `401`: Usuario no autenticado
- `404`: Usuario no encontrado

**Ejemplo de uso:**
```bash
curl -X GET "http://localhost/api/usuario/chef_maria/recetas" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

**Notas importantes:**
- Devuelve solo recetas activas del usuario
- Incluye información completa: ingredientes, etiquetas, categoría
- Ordenadas por fecha de creación (más recientes primero)
- Incluye conteo de favoritos por receta
- URLs de imágenes procesadas automáticamente

