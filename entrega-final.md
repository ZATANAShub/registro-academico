# Entrega final – Encuentro 16

| | |
|---|---|
| **Proyecto** | Registro Académico |
| **Asignatura** | Programación Web II |
| **Institución** | Corporación Universitaria Autónoma de Nariño (AUNAR) – Villavicencio |
| **Estudiante** | Juan Esteban Vásquez Gómez |
| **Enlace del repositorio** | _Pendiente: el repositorio todavía no se ha publicado_ |
| **Commit de entrega** | _Pendiente: escribir aquí el resultado de `git rev-parse --short HEAD` después del commit final_ |

## Funciones implementadas (comprobadas con pruebas)

- Inicio de sesión del operador con contraseña guardada con hash.
- Protección de todas las páginas de estudiantes con el middleware `auth`.
- Registro de estudiantes con nombre, correo, edad y programa.
- Validación en el servidor con mensajes en español: nombre de 2 a 80 caracteres, correo válido, único y de hasta 120 caracteres, edad entera de 16 a 100 y programa del catálogo.
- Listado con paginación de 10 registros por página.
- Búsqueda por nombre.
- Edición que conserva el mismo `id` y permite mantener el propio correo.
- Eliminación con una ventana de confirmación (modal de Bootstrap).
- Mensajes de éxito y de error.
- Cierre de sesión.
- Protección CSRF en todos los formularios.
- 21 pruebas automáticas y 16 pruebas manuales contra MySQL (ver `registro-pruebas.md`).

## Estado real del proyecto

- La versión Laravel está **funcional**. 15 de las 16 pruebas de la tabla se ejecutaron y pasaron.
- **Pendiente:** comprobar en un navegador que el botón "Cancelar" del modal no elimina el registro (prueba 13). El código está preparado para eso, pero no se hizo clic en un navegador real.
- **Pendiente:** el catálogo de programas (`config/programas.php`) es una **lista provisional** y hay que reemplazarlo por el oficial.
- **Pendiente:** crear el repositorio Git, hacer el commit de entrega y, si el profesor lo pide, publicarlo.
- La versión anterior en PHP nativo (carpeta `semana3/`) se conserva como antecedente. Se corrigió su error de edición, pero no tiene login ni CSRF y usa otros campos. **No es la versión que se entrega.**

## Dificultad encontrada y solución

**Dificultad:** en la versión PHP nativa, editar un estudiante siempre fallaba. El formulario y la consulta `UPDATE` usaban `fechaNacimiento` y `telefonoAcudiente`, pero en la tabla las columnas se llaman `fecha_nacimiento` y `telefono_acudiente`, y MySQL respondía `Unknown column 'fechaNacimiento'`. Además, esa versión no tenía los campos `edad` y `programa` que pide el Encuentro 16, ni inicio de sesión ni protección CSRF.

**Solución:** se corrigieron los nombres de las columnas en `editar.php` y `actualizar.php`. Después se migró el proyecto a Laravel con una sola definición de los campos: la migración define la tabla, el modelo declara los campos permitidos (`Fillable`) y el formulario se comparte entre registrar y editar (`_formulario.blade.php`). Así los nombres no pueden quedar distintos entre pantallas. Laravel también aportó `auth`, `@csrf` y la validación.

## Mejoras futuras

- Reemplazar la lista provisional por el catálogo oficial de programas, o guardarlo en una tabla propia.
- Agregar la traducción completa al español de los mensajes de Laravel (`lang/es`).
- Importar a la nueva base, si se necesitan, los 26 registros de la versión anterior. Hay que decidir cómo obtener `edad` (por ejemplo, calculándola de `fecha_nacimiento`) y `programa`.
- Permitir más de un operador, con roles.
