<?php

foreach($container->getParameterBag()->all() as $key => $value) {
    $envValue = getenv('SYMFONY__' . strtoupper($key));
    if ($envValue !== false) {
        $container->setParameter($key, $envValue);
    }
}