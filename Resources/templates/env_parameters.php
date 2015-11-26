<?php

foreach($container->getParameterBag()->all() as $key => $value) {
    $envValue = getenv('SYMFONY__' . strtoupper($key));
    if ($envValue !== false) {
        $container->setParameter($key, $envValue);
    }
}

// Use the DYNO env param to detect if we are on Heroku and redirect logs to stderr
if (getenv('DYNO') !== false) {
    $container->setParameter('logging_location', 'php://stderr');
} else {
    $location = $container->getParameter('kernel.logs_dir') . '/' . $container->getParameter('kernel.environment') . '.log';
    $container->setParameter('logging_location', $location);
}