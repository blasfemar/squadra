-- Migration to add the 'translations' table for i18n

CREATE TABLE translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lang_key VARCHAR(100) NOT NULL UNIQUE COMMENT 'The language key, e.g., main_title, admin_dashboard_title',
    es TEXT COMMENT 'Spanish translation',
    en TEXT COMMENT 'English translation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT 'Stores string translations for multilingual support';

-- Optional: Insert some initial critical keys if desired, though these might be better managed via the app later.
-- INSERT INTO translations (lang_key, es, en) VALUES ('app_name', 'Analizador SEO', 'SEO Analyzer');
-- INSERT INTO translations (lang_key, es, en) VALUES ('admin_login_button', 'Admin Login', 'Admin Login');
