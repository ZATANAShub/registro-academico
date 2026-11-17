# Registro de pruebas – Encuentro 16

**Proyecto:** Registro Académico (Laravel 13.43, PHP 8.3.6, MySQL 8.0.46)
**Fecha de ejecución:** 17 de noviembre de 2026
**Datos usados:** todos ficticios (correos `@correo.test`).

Hay dos tipos de pruebas:

1. **Manuales/HTTP contra MySQL real** (base `registro_academico`): se levantó `php artisan serve` y se hicieron las peticiones con `curl`, usando cookies de sesión y token CSRF como un navegador. Después de cada paso se consultó MySQL directamente.
2. **Automáticas** (`php artisan test`): 21 pruebas y 101 aserciones en `tests/Feature/SesionTest.php` y `tests/Feature/EstudianteTest.php`, ejecutadas en SQLite en memoria. **Resultado: 21 de 21 pasaron.**

## Tabla de pruebas

| # | Prueba | Datos utilizados | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| 1 | Iniciar sesión correctamente | Correo y contraseña del operador del `.env` | Acceso al sistema | `302 → /estudiantes`; luego `GET /estudiantes` = 200 | Correcto | También se probó una contraseña incorrecta: vuelve a `/login` con el mensaje "El correo o la contraseña no son correctos." |
| 2 | Acceder sin iniciar sesión | `GET /estudiantes` sin cookie | Redirección al login | `302 → /login` | Correcto | La prueba automática verifica también create, edit, store y destroy |
| 3 | Registrar estudiante válido | Ana Ficticia, ana.ficticia@correo.test, 20, Ingeniería Informática (y 2 más) | Registro guardado | `302`; MySQL muestra las 3 filas (ids 1, 2, 3) | Correcto | Aparece el mensaje "Estudiante registrado correctamente." solo una vez |
| 4 | Registrar correo duplicado | ana.ficticia@correo.test otra vez | Error de validación | "Ya existe un estudiante con ese correo." | Correcto | MySQL siguió con 3 filas |
| 5 | Registrar edad 15 | edad = 15 | Rechazo | "La edad debe estar entre 16 y 100 años." | Correcto | Automática: 16 y 100 se aceptan; 101 y 20.5 se rechazan |
| 6 | Registrar edad no numérica | edad = "veinte" | Rechazo | "La edad debe ser un número entero." | Correcto | — |
| 7 | Registrar programa inexistente | programa = "Medicina Espacial" | Rechazo | "El programa seleccionado no es válido." | Correcto | Total en MySQL después de los rechazos 4–7: 3 filas |
| 8 | Buscar estudiante existente | `?buscar=Bruno` | Mostrar coincidencias | Solo aparece bruno.demo@correo.test | Correcto | La búsqueda usa `LIKE` sobre `nombre` |
| 9 | Buscar estudiante inexistente | `?buscar=Zacarias` | Resultado vacío | "No se encontraron estudiantes con el nombre «Zacarias»." | Correcto | — |
| 10 | Editar estudiante | id 1 → "Ana Ficticia Editada", 21, Ingeniería de Software | Actualizar la misma fila | MySQL: id 1 cambió; ids 2 y 3 sin cambios; total 3 | Correcto | No se duplicó el registro |
| 11 | Editar conservando su propio correo | id 1 con ana.ficticia@correo.test | Permitir actualización | Actualizado sin error | Correcto | Usar el correo de **otro** estudiante sí se rechaza ("Ya existe…") |
| 12 | Editar identificador inexistente | id 99999 (GET edit y PUT) | No modificar otra fila | `404` en ambos; total 3 y 0 filas "Fantasma" | Correcto | Route model binding devuelve 404 |
| 13 | Abrir confirmación y cancelar | Botón "Eliminar" → "Cancelar" | Conservar registro | El HTML tiene el modal; "Cancelar" es `type="button"` con `data-bs-dismiss`, así que no envía nada. El registro sigue en la BD | **Pendiente** (verificación manual) | No se pudo hacer clic en un navegador real desde este entorno. **Hazlo tú en el navegador antes de la defensa.** |
| 14 | Confirmar eliminación | Eliminar id 2 (Bruno Demo) | Eliminar solo esa fila | MySQL: quedan ids 1 y 3 | Correcto | Se envía `DELETE` con `@csrf` y `@method('DELETE')` |
| 15 | Cerrar sesión | `POST /logout` | Finalizar acceso | `302 → /login`; luego `GET /estudiantes` → `302 /login` | Correcto | La sesión se invalida y se regenera el token |
| 16 | Volver a entrar | Se **detuvo y reinició** el servidor y se inició sesión de nuevo | Recuperar acceso y registros | `302 → /estudiantes`; aparecen ana.ficticia y carla.test | Correcto | Comprueba la persistencia en MySQL |

### Pruebas adicionales de seguridad

| Prueba | Resultado obtenido | Estado |
|---|---|---|
| POST a `/estudiantes` sin token CSRF | `419` (rechazado) | Correcto |
| Nombre con `<script>alert(1)</script>` | Se muestra escapado (`&lt;script&gt;`) | Correcto (automática) |
| Contraseña del operador en la BD | Empieza por `$2y$` (bcrypt), no es texto plano | Correcto |
| Base original `mi_proyecto` | Sigue con sus 26 filas; no se modificó | Correcto |
| Log de errores de Laravel tras las pruebas | Sin errores | Correcto |

## Correcciones realizadas a partir de las pruebas

### Versión PHP nativa (carpeta `semana3/`, antes de migrar)

Las pruebas se hicieron en una base de prueba aparte, `mi_proyecto_pruebas`, con la misma estructura y sin datos reales.

| Problema encontrado | Evidencia | Corrección |
|---|---|---|
| Editar siempre fallaba | `Unknown column 'fechaNacimiento' in 'field list'` en `actualizar.php` y avisos `Undefined array key "fechaNacimiento"` en `editar.php` | Se cambió a `fecha_nacimiento` y `telefono_acudiente`, igual que en la tabla y en `guardar.php` |
| Avisos de PHP 8.3 con columnas NULL | `htmlspecialchars(null)` | Se agregó `?? ''` en `editar.php` y `listar.php` |
| Se aceptaban correos duplicados e inválidos | Se insertó un correo repetido | **No se corrigió en PHP nativo**: se resolvió en la versión Laravel |
| Sin login, sin protección de páginas y sin CSRF | `listar.php` respondía 200 sin sesión; un POST externo borraba registros | Se resolvió en la versión Laravel |
| Faltaban los campos `edad` y `programa` | La tabla tenía 11 campos distintos | La versión Laravel usa los 4 campos oficiales en una base nueva |

### Versión Laravel

- La prueba de ejemplo que trae Laravel (`ExampleTest`) esperaba un 200 en `/`. Ahora `/` redirige al listado, así que se reemplazó por pruebas reales del proyecto.

## Datos que quedaron en `registro_academico`

Después de las pruebas quedaron 3 estudiantes ficticios (Ana Ficticia Editada, Carla Test, Diego Flash). Puedes dejarlos para la demostración o eliminarlos desde la aplicación.
