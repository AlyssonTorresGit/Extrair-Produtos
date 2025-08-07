<?php
// Caminho absoluto até a raiz do projeto
define('BASE_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

// Configurações
define('PATH_CONFIGS', BASE_PATH . 'configs' . DIRECTORY_SEPARATOR);

// Templates (menus, formulários reutilizáveis)
define('PATH_TEMPLATES', BASE_PATH . 'templates' . DIRECTORY_SEPARATOR);

// Includes (headers, footers, etc.)
define('PATH_INCLUDES', BASE_PATH . 'includes' . DIRECTORY_SEPARATOR);

// CSS
define('PATH_CSS', BASE_PATH . 'css' . DIRECTORY_SEPARATOR);
define('PATH_CSS_LISTA_ERROS', PATH_CSS . 'lista_erros' . DIRECTORY_SEPARATOR);
define('PATH_CSS_GERAL', PATH_CSS . 'geral' . DIRECTORY_SEPARATOR);
define('PATH_CSS_COMANDOS', PATH_CSS . 'comandos' . DIRECTORY_SEPARATOR);
define('PATH_CSS_XML', PATH_CSS . 'xml' . DIRECTORY_SEPARATOR);

// Páginas
define('PATH_PAGES', BASE_PATH . 'pages' . DIRECTORY_SEPARATOR);
define('PATH_PAGES_ERROS', PATH_PAGES . 'lista_erros' . DIRECTORY_SEPARATOR);
define('PATH_PAGES_TUTORIAIS', PATH_PAGES . 'tutoriais' . DIRECTORY_SEPARATOR);
define('PATH_PAGES_COMANDOS', PATH_PAGES . 'comandos_sqls' . DIRECTORY_SEPARATOR);
define('PATH_PAGES_XMLS', PATH_PAGES . 'importar_xmls' . DIRECTORY_SEPARATOR);
define('PATH_PAGES_NOVIDADES', PATH_PAGES . 'novidades' . DIRECTORY_SEPARATOR);

// Uploads e Imagens
define('PATH_STORAGE', BASE_PATH . 'storage' . DIRECTORY_SEPARATOR);
define('PATH_IMAGENS', BASE_PATH . 'assets/imagens' . DIRECTORY_SEPARATOR);

// XMLs
define('PATH_XMLS', BASE_PATH . 'xmls' . DIRECTORY_SEPARATOR);
