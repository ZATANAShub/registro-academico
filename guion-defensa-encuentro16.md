# Guion de defensa – Encuentro 16

> Esto es una guía, no un libreto para memorizar. Lee cada parte, abre el archivo que menciona y explícalo con tus palabras.

## 1. Presentación inicial

"Buenas, profe. Mi proyecto es un **sistema de registro académico**. Sirve para que un operador inicie sesión y pueda registrar estudiantes, verlos en una lista, buscarlos por nombre, editarlos y eliminarlos.

Empezó en PHP normal en los encuentros 12, 13 y 14. Para el 15 y el 16 lo pasé a **Laravel**, que organiza el código en modelo, vista y controlador. Los datos se guardan en **MySQL** y cada estudiante tiene cuatro campos: nombre, correo, edad y programa."

## 2. Demostración paso a paso

Antes de empezar, ten el servidor corriendo (`php artisan serve`) y el navegador en `http://127.0.0.1:8000`.

1. **Pantalla inicial.** Abro `http://127.0.0.1:8000`. "Como no he iniciado sesión, me manda directo al login. Eso lo hace el middleware `auth`."
2. **Inicio de sesión.** Primero escribo una contraseña mala para mostrar el mensaje de error, y luego entro con la cuenta del operador. "La contraseña está guardada con hash, no en texto plano."
3. **Registro de un estudiante.** Clic en "Registrar nuevo estudiante". Lleno, por ejemplo: *Laura Martínez, laura.martinez@correo.test, 19, Ingeniería Informática*. Guardo y sale el mensaje verde.
4. **Consulta del listado.** "Aquí está la tabla con ID, nombre, correo, edad y programa. Muestra 10 por página."
5. **Búsqueda.** Escribo "Laura" y aparece. Luego busco un nombre que no exista y sale "No se encontraron estudiantes…".
6. **Edición.** Clic en "Editar" (amarillo). Cambio la edad a 20 y guardo. "Fíjese que el ID es el mismo: se actualizó la misma fila, no se creó otra."
7. **Validación de un dato incorrecto.** Voy a registrar con edad **15** o con un correo que ya existe. "El servidor lo rechaza y me dice exactamente qué está mal."
8. **Confirmación y eliminación.** Clic en "Eliminar" (rojo). Se abre la ventana de confirmación. Primero doy **Cancelar** y muestro que el registro sigue ahí. Después vuelvo a abrirla y doy **Sí, eliminar**. Solo desaparece ese estudiante.
9. **Cierre de sesión.** Clic en "Cerrar sesión". Intento entrar a `/estudiantes` y me devuelve al login.
10. **Persistencia en MySQL.** Abro la terminal de MySQL:
    ```sql
    USE registro_academico;
    SELECT * FROM estudiantes;
    ```
    "Aquí están los mismos datos que se ven en la página. Si apago el servidor y lo vuelvo a encender, siguen ahí, porque están en la base de datos."

## 3. Explicación técnica sencilla

**Ruta** (`routes/web.php`): "Es la que dice qué pasa cuando visito una dirección. Por ejemplo, `Route::resource('estudiantes', EstudianteController::class)` crea de una vez las rutas para listar, crear, guardar, editar, actualizar y eliminar, y cada una llama a un método del controlador."

**Controlador** (`app/Http/Controllers/EstudianteController.php`): "Es el que recibe la petición y decide qué hacer. Por ejemplo, `store()` valida los datos, crea el estudiante y redirige al listado con un mensaje. `SesionController` hace lo mismo para el login y el logout."

**Modelo** (`app/Models/Estudiante.php`): "Representa la tabla `estudiantes`. Ahí digo qué campos se pueden llenar: nombre, correo, edad y programa."

**Vista Blade** (`resources/views/estudiantes/`): "Son los HTML con Bootstrap. Blade me deja usar cosas como `@foreach` para recorrer los estudiantes, y `{{ }}` para mostrar datos escapados, lo que evita que alguien meta código. El formulario está en `_formulario.blade.php` y lo reutilizo para registrar y para editar."

**Eloquent**: "Es la forma en que Laravel habla con la base de datos usando objetos en vez de escribir SQL. Por ejemplo, `Estudiante::create($datos)` hace el `INSERT` y `$estudiante->delete()` hace el `DELETE`. Por debajo usa consultas preparadas."

**Conexión con MySQL**: "Ya no hay un `conexion.php` con la contraseña escrita en el código. Los datos de conexión están en el archivo `.env` (`DB_DATABASE=registro_academico`, usuario y contraseña), y Laravel los lee desde `config/database.php`. El `.env` no se sube a Git."

**Migraciones** (`database/migrations/..._create_estudiantes_table.php`): "Son como el plano de la tabla en código. Ahí dice que `correo` tiene máximo 120 caracteres y es único, y que `edad` es un número. Con `php artisan migrate` se crea la tabla, así cualquiera puede armar la misma base."

**@csrf**: "Pone un token oculto en cada formulario. Si alguien intenta mandar un formulario desde otra página sin ese token, Laravel responde con el error 419. Yo lo probé enviando un POST sin token y salió 419."

**Middleware auth**: "Es un filtro que se ejecuta antes del controlador. Si no hay sesión iniciada, te manda al login. En `web.php` metí todas las rutas de estudiantes dentro de `Route::middleware('auth')`."

**Validación**: "En el controlador, el método `reglas()` tiene las reglas: nombre `min:2|max:80`, correo `email|max:120|unique`, edad `integer|between:16,100` y programa `in:` la lista de `config/programas.php`. Si algo falla, Laravel devuelve al formulario con los errores y lo que ya había escrito."

**CRUD**: "Son las cuatro operaciones básicas: **C**rear (`store`), **R**ead o leer (`index`), **U**pdate o actualizar (`update`) y **D**elete o eliminar (`destroy`). Cada una tiene su ruta y su método en el controlador."

**Git**: "Es el control de versiones. Guarda la historia de los cambios con commits, y así puedo volver atrás o mostrar qué cambió. El `.gitignore` evita subir el `.env` con contraseñas y la carpeta `vendor`, que se reinstala con `composer install`."

## 4. Posibles preguntas del profesor

1. **¿Por qué pasaste el proyecto a Laravel?**
   Porque lo pedía el Encuentro 15, y porque Laravel ya trae login, protección CSRF, validación y una organización MVC que en PHP normal tocaba hacer a mano.

2. **¿Qué pasa si escribo una edad de 15?**
   El servidor la rechaza con el mensaje "La edad debe estar entre 16 y 100 años", por la regla `between:16,100`.

3. **¿Y si escribo letras en la edad?**
   La regla `integer` la rechaza con "La edad debe ser un número entero".

4. **¿Cómo evitas correos repetidos?**
   Con dos cosas: la regla `unique:estudiantes,correo` en la validación y un índice `unique` en la migración, así que la base de datos tampoco lo permite.

5. **Si edito un estudiante y no le cambio el correo, ¿no sale error de "ya existe"?**
   No, porque al editar uso `Rule::unique(...)->ignore($estudiante->id)`, que ignora el correo del propio estudiante.

6. **¿Qué pasa si alguien modifica el HTML y manda un programa que no existe?**
   Igual se rechaza, porque la validación está en el servidor con `Rule::in(config('programas'))`. Lo del HTML es solo ayuda visual.

7. **¿Dónde está la contraseña del operador?**
   En la tabla `users`, pero guardada con hash bcrypt (empieza por `$2y$`). La original solo está en mi `.env`, que no se sube.

8. **¿Cómo se creó el operador?**
   Con el seeder `OperadorSeeder`, que lee el correo y la contraseña del `.env`. Se ejecuta con `php artisan db:seed`.

9. **¿Qué hace `@method('PUT')`?**
   Los formularios HTML solo mandan GET o POST. `@method('PUT')` agrega un campo oculto para que Laravel trate la petición como PUT y la mande al método `update`.

10. **¿Y `@method('DELETE')`?**
    Lo mismo, pero para eliminar. Así la ruta llega a `destroy`.

11. **¿Qué pasa si intento editar un ID que no existe, como el 9999?**
    Laravel responde 404. Como el controlador recibe un `Estudiante $estudiante`, si no lo encuentra no sigue, así que no modifica ninguna otra fila.

12. **¿Cómo sabes que se edita la misma fila y no se crea otra?**
    Porque uso `$estudiante->update($datos)` sobre el estudiante que ya existe. Lo comprobé en MySQL: el ID quedó igual y el total de filas no cambió.

13. **¿Qué pasa si le doy "Cancelar" en la confirmación?**
    Ese botón es `type="button"` con `data-bs-dismiss="modal"`, entonces solo cierra la ventana y no envía nada al servidor.

14. **¿Cómo funciona la búsqueda?**
    El formulario manda `?buscar=texto` por GET y el controlador hace `where('nombre', 'like', "%texto%")`. Eloquent lo pasa como parámetro, no como texto pegado al SQL.

15. **¿Tu sistema es vulnerable a inyección SQL?**
    No en lo que probé, porque no armo SQL a mano. Eloquent usa consultas preparadas.

16. **¿Y a que alguien meta un `<script>` en el nombre?**
    Blade escapa todo lo que va en `{{ }}`. Lo probé: el script se ve como texto y no se ejecuta.

17. **¿Qué es el error 419?**
    Es la respuesta de Laravel cuando falta el token CSRF o ya expiró. Lo probé mandando un POST sin token.

18. **¿Qué pasa si alguien entra directo a `/estudiantes/create` sin iniciar sesión?**
    El middleware `auth` lo redirige a `/login`.

19. **¿Qué hace el logout además de sacarte?**
    Invalida la sesión y regenera el token CSRF, así esa sesión ya no sirve.

20. **¿Por qué hay un límite de intentos en el login?**
    Puse `throttle:5,1`: máximo 5 intentos por minuto, para dificultar que adivinen la contraseña probando muchas.

21. **¿Cómo pruebas que todo funciona?**
    Tengo 21 pruebas automáticas que corro con `php artisan test` (usan SQLite en memoria para no tocar MySQL), y además hice las pruebas manuales contra MySQL, que están en `registro-pruebas.md`.

22. **¿Qué error tuviste y cómo lo solucionaste?**
    En la versión de PHP normal, editar fallaba porque usaba `fechaNacimiento` y la columna se llamaba `fecha_nacimiento`. Lo corregí, y en Laravel lo evito porque el formulario es el mismo para crear y editar.

23. **¿Qué no subes a Git y por qué?**
    El `.env`, porque tiene contraseñas, y `vendor/`, porque se instala con `composer install`. Para que otro configure el proyecto dejo `.env.example` sin claves.

24. **¿Qué pasó con los datos de la versión anterior?**
    No los borré. La base `mi_proyecto` sigue con sus 26 registros. La versión Laravel usa una base nueva, `registro_academico`, porque los campos son distintos.

## 5. Reflexión final

"Lo que más aprendí es que la organización del código importa mucho. En la versión de PHP normal tenía la misma información repetida en varios archivos, y un nombre de columna escrito distinto (`fechaNacimiento` en vez de `fecha_nacimiento`) hacía que editar no funcionara. En Laravel cada cosa tiene su lugar: la ruta, el controlador, el modelo y la vista. Además, el formulario es uno solo para crear y editar, entonces ese tipo de error es más difícil de cometer.

También entendí que la validación tiene que estar en el servidor. En la primera versión se podían guardar correos repetidos o mal escritos, y ahora el sistema los rechaza aunque alguien modifique el formulario.

Una mejora que haría después es poner el catálogo oficial de programas en su propia tabla y traer a la nueva base los registros de la versión anterior."
