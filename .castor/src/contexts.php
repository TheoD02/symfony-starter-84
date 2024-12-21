<?php

declare(strict_types=1);

use Castor\Attribute\AsContext;
use Castor\Context;

use function Castor\capture;

define('ROOT_DIR', dirname(__DIR__, 2));

#[AsContext(default: true)]
function root_context(): Context
{
    return new Context(
        data: [
            'user.id' => capture(['id', '-u']),
            'user.group' => capture(['id', '-g']),
        ],
        workingDirectory: ROOT_DIR
    );
}

#[AsContext(name: 'symfony')]
function symfony_context(): Context
{
    return root_context()
        ->withWorkingDirectory(ROOT_DIR . '/app')
        ->withData([
            'registry' => 'docker-registry.domain.fr',
            'image' => 'theod02/demo-app-symfony',
        ])
    ;
}

#[AsContext(name: 'frontend')]
function frontend_context(): Context
{
    return root_context()
        ->withWorkingDirectory(ROOT_DIR . '/front')
        ->withData([
            'registry' => 'docker-registry.domain.fr',
            'image' => 'theod02/demo-app-frontend',
        ])
    ;
}
