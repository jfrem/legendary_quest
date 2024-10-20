<?php
// autoload.php

/**
 * Registra una función de autoload para cargar clases automáticamente.
 * 
 * Esta función buscará la clase solicitada en diferentes directorios 
 * (controllers, models, core y config) y la incluirá si se encuentra.
 * 
 * @return void
 */
spl_autoload_register(function ($class) {
    // Definir rutas posibles para las clases.
    $paths = [
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/core/' . $class . '.php',
        __DIR__ . '/config/' . $class . '.php',
    ];

    // Iterar sobre las rutas y cargar la clase si existe.
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path; // Incluir la clase encontrada.
            return; // Salir una vez que la clase se haya cargado.
        }
    }
});
