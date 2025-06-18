-- Seed data for the 'translations' table

INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES
('app_title', 'Squadra SEO Analyzer', 'Squadra SEO Analyzer'),
('admin_login_button', 'Acceso Admin', 'Admin Login'),
('frontend_analyze_form_title', 'Analizar Rendimiento SEO', 'Analyze SEO Performance'),
('frontend_url_placeholder', 'Introduce la URL del sitio web (ej., https://www.example.com)', 'Enter website URL (e.g., https://www.example.com)'),
('analyze_button', 'Analizar', 'Analyze'),
('frontend_form_subtitle', 'Introduce una URL para empezar con tu análisis SEO.', 'Enter a URL to get started with your SEO analysis.'),
('footer_copyright', '&copy; {year} {app_name}. Todos los derechos reservados.', '&copy; {year} {app_name}. All rights reserved.'),

('admin_panel_title', 'Panel de Administración', 'Admin Panel'),
('layout_page_content_not_loaded', 'Error: El contenido de la página no fue proporcionado al layout.', 'Error: Page content was not provided to the layout.'),
('admin_update_confirm_message', '¿Estás seguro de que quieres actualizar la aplicación? Esto ejecutará `git pull`. Asegúrate de tener una copia de seguridad si es necesario.', 'Are you sure you want to attempt to update the application? This will run `git pull`. Make sure you have a backup if needed.'),

('admin_login_page_title', 'Acceso Admin', 'Admin Login'),
('admin_login_form_title', 'Acceso al Panel de Admin', 'Admin Panel Login'),
('login_app_not_installed_message', 'Aplicación no instalada completamente o configuración ausente.', 'Application not fully installed or configuration is missing.'),
('login_run_installer_link_text', 'Por favor, ejecuta el script de instalación.', 'Please run the installation script.'),
('login_error_credentials_required', 'Usuario y contraseña son requeridos.', 'Username and password are required.'),
('login_error_invalid_credentials', 'Usuario o contraseña inválidos.', 'Invalid username or password.'),
('login_error_db_connection', 'Error de conexión a la base de datos.', 'Database connection error.'),
('login_error_db_query', 'Error en la consulta a la base de datos.', 'Database query error.'),
('login_error_config_missing', 'Archivo de configuración ausente. Ejecute el instalador.', 'Configuration file missing. Please run the installer.'),
('login_error_config_constants_missing', 'Constantes de configuración de BD no definidas en api/config.php.', 'Database configuration constants are not defined in api/config.php.'),
('username_label', 'Usuario', 'Username'),
('password_label', 'Contraseña', 'Password'),
('login_button', 'Acceder', 'Login'),
('login_footer_installer_notice', 'Si no has instalado la aplicación aún, por favor ejecuta el instalador.', 'If you haven''t installed the application yet, please run the installer.'),

('admin_menu_dashboard', 'Dashboard', 'Dashboard'),
('admin_menu_user_management', 'Gestión de Usuarios', 'User Management'),
('admin_menu_interface_settings', 'Ajustes de Interfaz', 'Interface Settings'),
('admin_menu_translations_link', 'Gestión de Textos', 'Text Management'),
('admin_menu_update_application', 'Actualizar Aplicación', 'Update Application'),
('admin_menu_changelog', 'Historial de Cambios', 'Changelog'),
('admin_menu_logout', 'Cerrar Sesión', 'Logout'),

('admin_dashboard_title', 'Dashboard', 'Dashboard'),
('admin_dashboard_welcome', '¡Bienvenido de nuevo, {username}!', 'Welcome back, {username}!'),
('admin_dashboard_intro_text', 'Este es tu panel de Squadra SEO Analyzer. Desde aquí puedes gestionar usuarios, personalizar la interfaz y actualizar la aplicación.', 'This is your Squadra SEO Analyzer dashboard. From here, you can manage users, customize interface settings, and update the application.'),
('admin_dashboard_stats_title', 'Estadísticas Rápidas (Placeholder)', 'Quick Stats (Placeholder)'),
('admin_dashboard_total_users_label', 'Usuarios Totales:', 'Total Users:'),
('admin_dashboard_analyses_label', 'Análisis Realizados:', 'Analyses Performed:'),
('admin_dashboard_site_health_label', 'Salud del Sitio:', 'Site Health:'),
('site_health_good', 'Buena', 'Good'),
('status_not_available', 'N/D', 'N/A'),
('admin_dashboard_quick_links_title', 'Enlaces Rápidos', 'Quick Links'),
('admin_dashboard_manage_users_link', 'Gestionar Usuarios', 'Manage Users'),
('admin_dashboard_customize_interface_link', 'Personalizar Interfaz', 'Customize Interface'),
('admin_dashboard_check_updates_link', 'Buscar Actualizaciones (vía Sidebar)', 'Check for Updates (via Sidebar)'),
('admin_dashboard_sysinfo_title', 'Información del Sistema (Placeholder)', 'System Information (Placeholder)'),
('admin_dashboard_php_version_label', 'Versión PHP:', 'PHP Version:'),
('admin_dashboard_app_version_label', 'Versión App:', 'App Version:'),

('admin_users_title', 'Gestión de Usuarios', 'User Management'),
('admin_users_add_admin_title', 'Añadir Nuevo Administrador', 'Add New Administrator'),
('admin_users_add_success', '¡Usuario ''{username}'' añadido con éxito!', 'User ''{username}'' added successfully!'),
('admin_users_error_empty_fields', 'Nuevo usuario y contraseña no pueden estar vacíos.', 'New username and password cannot be empty.'),
('admin_users_error_password_short', 'Nueva contraseña debe tener al menos 8 caracteres.', 'New password must be at least 8 characters long.'),
('admin_users_error_username_exists', 'El usuario ''{username}'' ya existe. Por favor, elige otro.', 'Username ''{username}'' already exists. Please choose a different one.'),
('admin_users_error_hash_failed', 'Falló el hash de la contraseña.', 'Failed to hash password.'),
('admin_users_error_add_failed', 'Error añadiendo usuario: {error}', 'Error adding user: {error}'),
('admin_users_error_fetch_failed', 'Error obteniendo usuarios: {error}', 'Error fetching users: {error}'),
('password_label_min_chars', 'Contraseña (mín 8 caracteres)', 'Password (min 8 chars)'),
('add_user_button', 'Añadir Usuario', 'Add User'),
('admin_users_existing_admins_title', 'Administradores Existentes', 'Existing Administrators'),
('admin_users_none_found', 'No se encontraron administradores. Añade uno usando el formulario.', 'No administrator users found. Add one using the form.'),
('table_header_id', 'ID', 'ID'),
('table_header_username', 'Usuario', 'Username'),
('table_header_created_at', 'Creado En', 'Created At'),

('admin_ui_title', 'Ajustes de Interfaz', 'Interface Settings'),
('admin_ui_manage_title', 'Gestionar Ajustes de Interfaz', 'Manage Interface Settings'),
('admin_ui_save_success', '¡Ajustes de interfaz guardados con éxito!', 'Interface settings saved successfully!'),
('admin_ui_error_site_title_empty', 'El Título del Sitio no puede estar vacío.', 'Site Title cannot be empty.'),
('admin_ui_error_main_color_invalid', 'El Color Principal debe ser un código hexadecimal válido (ej., #RRGGBB o #RGB).', 'Main Color must be a valid hex code (e.g., #RRGGBB or #RGB).'),
('admin_ui_error_save_failed', 'Error guardando ajustes: {error}', 'Error saving settings: {error}'),
('db_connection_not_available', 'Conexión a base de datos no disponible. Por favor, revisa la configuración.', 'Database connection not available. Please check configuration.'),
('site_title_label', 'Título del Sitio', 'Site Title'),
('site_title_help_text', 'Este título se usará en la pestaña del navegador y potencialmente en otros lugares de la interfaz.', 'This title will be used in the browser tab and potentially other places in the UI.'),
('main_color_label', 'Color Principal (Código Hex)', 'Main Color (Hex Code)'),
('main_color_help_text', 'Elige un color principal para el tema de la aplicación (ej., para botones, resaltados).', 'Choose a main theme color for the application (e.g., for buttons, highlights).'),
('save_changes_button', 'Guardar Cambios', 'Save Changes'),

('admin_translations_page_title', 'Gestión de Traducciones', 'Translation Management'),
('admin_translations_title', 'Gestión de Traducciones', 'Translation Management'),
('admin_translations_add_new_title', 'Añadir Nueva Clave de Traducción', 'Add New Translation Key'),
('admin_translations_key_label', 'Clave de Idioma', 'Language Key'),
('admin_translations_es_label', 'Español (es)', 'Spanish (es)'),
('admin_translations_en_label', 'Inglés (en)', 'English (en)'),
('admin_translations_add_button', 'Añadir Traducción', 'Add Translation'),
('admin_translations_existing_title', 'Traducciones Existentes', 'Existing Translations'),
('admin_translations_none_found', 'No se encontraron traducciones. Añade algunas usando el formulario de arriba.', 'No translations found. Add some using the form above.'),
('admin_translations_key_header', 'Clave', 'Key'),
('admin_translations_es_header', 'Español (es)', 'Spanish (es)'),
('admin_translations_en_header', 'Inglés (en)', 'English (en)'),
('admin_translations_save_all_button', 'Guardar Todos los Cambios', 'Save All Changes');

-- Keys from index.php refactoring
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('html_lang', 'es', 'en');
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('frontend_url_label_sr', 'URL del Sitio Web', 'Website URL');
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('analyze_form_action_url', 'api/analyze.php', 'api/analyze.php');

-- Keys from admin/layout.php and admin/login.php refactoring
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('admin_text', 'Admin', 'Admin');
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('admin_area_default_title', 'Área de Admin', 'Admin Area');
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('admin_menu_logout_param', 'Cerrar Sesión ({username})', 'Logout ({username})');
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('login_error_db_connection_specific', 'La conexión a la base de datos falló. Revise los logs del servidor y api/config.php.', 'Database connection failed. Please check server logs and api/config.php.');
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('login_error_cannot_process_no_db', 'No se puede procesar el login: Conexión a BD no establecida.', 'Cannot process login: Database connection not established.');
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('login_error_db_connection_verify_settings', 'No se pudo conectar a la base de datos. Por favor verifique la configuración en api/config.php o ejecute el instalador.', 'Could not connect to the database. Please verify settings in api/config.php or run the installer.');

-- Key from admin/index.php refactoring (dashboard quick links)
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('admin_dashboard_check_updates_link_title', 'Usar el enlace de la barra lateral para la funcionalidad completa de POST', 'Use sidebar link for full POST functionality');

-- Key from frontend_layout.php
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('app_title_logo_alt', 'Logo del Analizador SEO', 'SEO Analyzer Logo');

-- Keys from admin/changelog.php
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES
('admin_changelog_page_title', 'Historial de Actualizaciones / Changelog', 'Update History / Changelog'),
('admin_changelog_error_exec_failed', 'Falló la ejecución del comando git log. Asegúrese que shell_exec está habilitado y git instalado.', 'Failed to execute git log command. Ensure shell_exec is enabled and git is installed.'),
('admin_changelog_error_git_command', 'Error ejecutando el comando git log:', 'Error executing git log command:'),
('admin_changelog_error_no_commits_parsed', 'No se pudo analizar el historial de commits, o no se encontraron commits en el formato esperado.', 'Could not parse commit history, or no commits found in the expected format.'),
('admin_changelog_no_history', 'No se encontró historial de commits o la salida de git log estaba vacía.', 'No commit history found or git log output was empty.'),
('admin_changelog_error_repo_path', 'Error: No se pudo determinar la ruta raíz del repositorio.', 'Error: Could not determine the repository root path.'),
('admin_changelog_commit_hash_label', 'Hash del Commit:', 'Commit hash:'),
('admin_changelog_displaying_commits', 'Mostrando los últimos {count} commits.', 'Displaying last {count} commits.'),
('admin_changelog_no_commits_display', 'No hay commits para mostrar.', 'No commits to display.');

-- Key from index.php (results area)
INSERT INTO `translations` (`lang_key`, `es`, `en`) VALUES ('frontend_seo_score_title', 'Puntuación SEO', 'SEO Score');
