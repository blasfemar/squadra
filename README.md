# Analizador SEO - Guía de Instalación

Siga estos pasos para configurar la aplicación en su servidor.

## Requisitos
* Un servidor web con PHP (>= 7.4 recomendado para `password_hash`) y acceso a una base de datos MySQL o MariaDB.
* PDO extension for PHP enabled (for database connectivity).
* `shell_exec` enabled in PHP for version display and application updates via Git (optional but recommended for full functionality).
* Git instalado en el servidor (para la función de actualización y versión).
* Acceso SSH al servidor para la configuración inicial y para asegurar permisos si `shell_exec` o `git` tienen problemas.

## Pasos de Instalación

1.  **Clonar el Repositorio**: Conéctese a su servidor vía SSH y clone este repositorio en el directorio raíz de su dominio o subdominio (ej. `/var/www/html` o `/public_html/seo.squadraagencia.com`).
    ```bash
    # Navegue al directorio deseado, por ejemplo:
    # cd /var/www/html
    # git clone <URL_DEL_REPOSITORIO> .
    # (Reemplace <URL_DEL_REPOSITORIO> con la URL real de su repositorio Git)
    # Ejemplo si el repo es 'squadra':
    # git clone git@github.com:blasfemar/squadra.git .
    ```
    *Nota: El `.` al final clona el contenido del repositorio directamente en el directorio actual.*

2.  **Permisos (Opcional pero Recomendado)**: Dependiendo de la configuración de su servidor, es posible que necesite ajustar los permisos para que el servidor web pueda escribir el archivo `api/config.php` durante la instalación.
    ```bash
    # Ejemplo (ejecutar con precaución y ajustar a su entorno):
    # chown www-data:www-data /ruta/a/su/app/api  # Si www-data es su usuario de servidor web
    # chmod 775 /ruta/a/su/app/api
    ```

3.  **Ejecutar el Asistente de Instalación**: Abra su navegador y vaya a la URL de su archivo de instalación:
    **`http://su-dominio.com/install.php`** o **`https://su-dominio.com/install.php`**
    (Reemplace `su-dominio.com` con su nombre de dominio real).

4.  **Siga los Pasos del Asistente**:
    *   **Paso 1**: Se le pedirá que ingrese los detalles de conexión de su base de datos (host, nombre de la base de datos, usuario, contraseña). Esta información se guardará en `api/config.php`.
    *   **Paso 2**: Haga clic para instalar las tablas necesarias (`settings`, `users`) en su base de datos.
    *   **Paso 3**: Cree su cuenta de administrador personal. Este será el único usuario con acceso al panel de administración.

5.  **BORRAR EL INSTALADOR (CRÍTICO)**: Una vez que el asistente muestre el mensaje de "Instalación Completada", **es crucial que elimine el archivo `install.php` de su servidor por razones de seguridad.**
    ```bash
    # rm /ruta/a/su/app/install.php
    ```

¡La aplicación está lista para usarse! Puede acceder al panel de administración a través de `http://su-dominio.com/admin/login.php`.

## Actualización de la Aplicación
Dentro del panel de administración, encontrará una opción para actualizar la aplicación. Esto ejecutará un `git pull` para obtener los últimos cambios del repositorio. Asegúrese de que su servidor tiene permisos para ejecutar `git` y escribir en los archivos del proyecto.

## Estructura de Archivos Relevante
```
/
|-- api/
|   |-- config.php.example  # Archivo de ejemplo para la configuración (config.php es generado)
|   |-- analyze.php         # Endpoint para el análisis SEO (trabajo en progreso)
|-- admin/
|   |-- index.php           # Panel principal (protegido)
|   |-- update_app.php      # Script de actualización (protegido)
|   |-- login.php           # Formulario de inicio de sesión
|   |-- logout.php          # Script para cerrar sesión
|   |-- auth.php            # Middleware de autenticación
|-- assets/
|   |-- css/
|   |   |-- style.css       # Estilos principales
|   |-- js/
|   |   |-- script.js       # Scripts principales
|-- migrations/
|   |-- 001_initial_schema.sql # Esquema inicial de la BD
|-- .gitignore              # Archivos a ignorar por Git
|-- index.html              # Página principal (trabajo en progreso)
|-- install.php             # Asistente de instalación (BORRAR DESPUÉS DE USAR)
|-- README.md               # Este archivo
```

## Contribuciones
Las contribuciones son bienvenidas. Por favor, haga un fork del repositorio, cree una nueva rama para sus características o correcciones, y envíe un Pull Request.
