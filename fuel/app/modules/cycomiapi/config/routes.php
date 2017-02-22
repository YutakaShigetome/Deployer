<?php

use \Fuel\Core\Fuel;
use Fuel\Core\Route;

// FuelPHP が環境別設定対応をしてくれていないので自力。
if (Fuel::$env === 'local') {
    // Swagger 用。
    return [
        '(:any)' => [['OPTIONS', new Route('cycomiapi/swagger/preflight')]]
    ];
} else {
    return [];
}
