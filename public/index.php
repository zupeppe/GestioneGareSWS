<?php
/**
 * Front Controller
 * 
 * Gestisce tutte le richieste in ingresso e le indirizza al controller appropriato.
 */

// Percorso base dell'applicazione
define('BASE_PATH', dirname(__DIR__));
// Modifica in base a dove si trova il progetto (es. '/GestioneGareSWS' in locale, '/endurance' su Aruba)
define('BASE_URL', '/GestioneGareSWS');
// Autoloader semplice per caricare automaticamente le classi
spl_autoload_register(function ($class) {
    // Sostituisce il namespace "App" con "app" per rispettare l'alberatura
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Routing basilare
$url = isset($_GET['url']) && $_GET['url'] !== '' ? rtrim($_GET['url'], '/') : 'home';
$urlParts = explode('/', filter_var($url, FILTER_SANITIZE_URL));

$controllerName = ucfirst($urlParts[0]) . 'Controller';
$methodName = isset($urlParts[1]) ? $urlParts[1] : 'index';

$controllerClass = "App\\Controllers\\" . $controllerName;
$controllerPath = BASE_PATH . '/app/Controllers/' . $controllerName . '.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $methodName)) {
            $params = array_slice($urlParts, 2);
            call_user_func_array([$controller, $methodName], $params);
        } else {
            http_response_code(404);
            echo "Errore 404: Metodo $methodName non trovato.";
        }
    } else {
        http_response_code(404);
        echo "Errore 404: Classe $controllerClass non trovata.";
    }
} else {
    http_response_code(404);
    echo "Errore 404: Controller $controllerName non trovato.";
}
