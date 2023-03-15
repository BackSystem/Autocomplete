<?php

use BackSystem\Autocomplete\Controller\ApiController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {
    $routes->add('autocomplete_search', '/{endpoint}')
        ->methods(['GET'])
        ->controller(ApiController::class)
    ;
};
