<?php

return [

    'title' => 'Agora Invoicing Installer',
    'probe' => 'Agora Invoicing Prüfungen',
    'magic_phrase' => 'Was ist die magische Phrase?',
    'server_requirements' => 'Serveranforderungen',
    'database_setup' => 'Datenbankeinrichtung',
    'getting_started' => 'Erste Schritte',
    'final' => 'Abschluss',
    'directory' => 'Verzeichnis',
    'permissions' => 'Berechtigungen',
    'requisites' => 'Voraussetzungen',
    'status' => 'Status',
    'php_extensions' => 'PHP-Erweiterungen',
    'not_enabled' => 'Nicht aktiviert',
    'extension_not_enabled' => 'Nicht aktiviert: Um dies zu aktivieren, installieren Sie bitte die Erweiterung auf Ihrem Server und aktualisieren Sie :php_ini_file, um :extensionName zu aktivieren. <a href=":url" target="_blank">Wie installiere ich PHP-Erweiterungen auf meinem Server?</a>',
    'mod_rewrite' => 'Mod Rewrite',
    'off_apache' => 'AUS (Falls Sie Apache verwenden, stellen Sie sicher, dass <var><strong>AllowOverride</strong></var> in der Apache-Konfiguration auf <var><strong>All</strong></var> gesetzt ist)',
    'rewrite_engine' => 'Rewrite Engine',
    'user_url' => 'Benutzerfreundliche URL',

    'host' => 'Host',
    'host_tooltip' => 'Wenn Ihre MySQL-Datenbank auf demselben Server wie Agora Invoicing installiert ist, lassen Sie es auf localhost',
    'database_name_label' => 'Datenbankname',
    'mysql_port_label' => 'MySQL-Portnummer',
    'mysql_port_tooltip' => 'Portnummer, auf der Ihr MySQL-Server läuft. Standardmäßig ist es 3306',
    'username' => 'Benutzername',
    'password_label' => 'Passwort',
    'test_prerequisites_message' => 'Dieser Test überprüft die Voraussetzungen für die Installation von Agora Invoicing',
    'previous' => 'Zurück',

    'sign_up_as_admin' => 'Als Admin registrieren',
    'first_name' => 'Vorname',
    'first_name_required' => 'Vorname ist erforderlich',
    'last_name' => 'Nachname',
    'last_name_required' => 'Nachname ist erforderlich',
    'username_info' => 'Der Benutzername darf nur alphanumerische Zeichen, Leerzeichen, Unterstriche, Bindestriche, Punkte und das @-Symbol enthalten.',
    'email' => 'E-Mail',
    'email_required' => 'E-Mail-Adresse ist erforderlich',
    'password_required' => 'Passwort ist erforderlich',
    'confirm_password' => 'Passwort bestätigen',
    'confirm_password_required' => 'Passwortbestätigung ist erforderlich',
    'password_requirements' => 'Ihr Passwort muss enthalten:',
    'password_requirements_list' => [
        ['id' => 'length', 'text' => 'Zwischen 8-16 Zeichen'],
        ['id' => 'letter', 'text' => 'Kleinbuchstaben (a-z)'],
        ['id' => 'capital', 'text' => 'Großbuchstaben (A-Z)'],
        ['id' => 'number', 'text' => 'Zahlen (0-9)'],
        ['id' => 'space', 'text' => 'Sonderzeichen (~*!@$#%_+.?:,{ })'],
    ],

    // Systeminformationen
    'system_information' => 'Systeminformationen',
    'environment' => 'Umgebung',
    'environment_required' => 'Umgebung ist erforderlich',
    'production' => 'Produktiv',
    'development' => 'Entwicklung',
    'testing' => 'Test',
    'cache_driver' => 'Cache-Treiber',
    'cache_driver_required' => 'Cache-Treiber ist erforderlich',
    'file' => 'Datei',
    'redis' => 'Redis',
    'password' => 'Passwort',

    // Redis-Einrichtung
    'redis_setup' => 'Redis-Einrichtung',
    'redis_host' => 'Redis-Host',
    'redis_port' => 'Redis-Port',
    'redis_password' => 'Redis-Passwort',

    // Buttons
    'continue' => 'Weiter',

    // Abschließende Einrichtung
    'final_setup' => 'Ihre Agora Invoicing-Anwendung ist bereit!',
    'installation_complete' => 'Alles klar! Sie haben die Installation erfolgreich abgeschlossen.',

    // Mehr erfahren
    'learn_more' => 'Mehr erfahren',
    'knowledge_base' => 'Wissensdatenbank',
    'email_support' => 'E-Mail-Support',

    // Nächster Schritt
    'next_step' => 'Nächster Schritt',
    'login_button' => 'Bei Agora Invoicing anmelden',

    'pre_migration_success' => 'Vor-Migration wurde erfolgreich getestet',
    'migrating_tables' => 'Tabellen in die Datenbank migrieren',
    'db_connection_error' => 'Datenbankverbindung wurde nicht aktualisiert.',
    'database_setup_success' => 'Datenbank wurde erfolgreich eingerichtet.',
    'env_file_created' => 'Umgebungskonfigurationsdatei wurde erfolgreich erstellt',
    'pre_migration_test' => 'Vor-Migrationstest wird ausgeführt',

    'redis_host_required' => 'Redis-Host ist erforderlich.',
    'redis_password_required' => 'Redis-Passwort ist erforderlich.',
    'redis_port_required' => 'Redis-Port ist erforderlich.',
    'password_regex' => 'Das Passwort muss mindestens 8 Zeichen lang sein und mindestens einen Großbuchstaben, einen Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten.',
    'setup_completed' => 'Einrichtung erfolgreich abgeschlossen!',

    'database' => 'Datenbank',
    'selected' => 'Ausgewählt',
    'mysql_version_is' => 'MySQL-Version ist',
    'database_empty' => 'Datenbank ist leer',
    'database_not_empty' => 'Die Installation von Agora Invoicing erfordert eine leere Datenbank. Ihre Datenbank enthält bereits Tabellen und Daten.',
    'mysql_version_required' => 'Wir empfehlen ein Upgrade auf mindestens MySQL 5.6 oder MariaDB 10.3!',
    'database_connection_unsuccessful' => 'Datenbankverbindung nicht erfolgreich.',
    'connected_as' => 'Verbunden mit der Datenbank als',
    'failed_connection' => 'Verbindung zur Datenbank fehlgeschlagen.',
    'magic_phrase_not_work' => 'Die eingegebene magische Phrase funktioniert nicht.',
    'magic_required' => 'Die magische Phrase ist erforderlich.',
    'user_name_regex' => 'Der Benutzername muss zwischen 3 und 20 Zeichen lang sein und darf nur Buchstaben, Zahlen, Leerzeichen, Unterstriche, Bindestriche, Punkte und das @-Symbol enthalten.',

];
