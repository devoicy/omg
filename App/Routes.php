<?php
use Slim\App;

return function (App $app) {
    // Router umum
    (require __DIR__ . '/Routes/Public.php')($app);

    // Router admin
    (require __DIR__ . '/Routes/Admin.php')($app);
};
