<?php
use Cake\Routing\Router;

Router::plugin(
    'Cms',
    function ($routes) {
        $routes->extensions(['json']);

        $routes->scope('/site', function ($routes) {
            $routes->connect('/:slug/categories/:action/*', ['controller' => 'Categories'], ['pass' => ['slug']]);
            $routes->connect('/:slug/articles/:action/*', ['controller' => 'Articles'], ['pass' => ['slug']]);
        });

        $routes->fallbacks('DashedRoute');
    }
);
