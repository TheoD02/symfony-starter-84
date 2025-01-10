<?php

declare(strict_types=1);

use Castor\Attribute\AsTask;

use function Castor\fingerprint;
use function Castor\import;
use function Castor\io;
use function Castor\variable;
use function symfony\symfony_install;
use function ui\ui_install;

import(__DIR__ . '/src');

#[AsTask(description: 'Build the docker containers')]
function build(bool $force = false): void
{
    if (
        ! fingerprint(
            callback: static fn () => docker([
                'compose',
                '--progress', 'plain',
                '-f', 'compose.yaml', '-f', 'compose.override.yaml',
                'build',
                '--build-arg', sprintf('USER_ID=%s', variable('user.id')),
                '--build-arg', sprintf('GROUP_ID=%s', variable('user.group')),
            ])->run(),
            id: 'docker-build',
            fingerprint: fgp()->docker(),
            force: $force || ! docker()->hasImages(['{{PREFIX_CONTAINER}}-php', '{{PREFIX_CONTAINER}}-front']),
        )
    ) {
        io()->note(
            'The Dockerfile or the docker-compose files have not changed since the last run, skipping the docker build command.',
        );
    }
}

#[AsTask(description: 'Build and start the docker containers')]
function start(bool $force = false): void
{
    build($force);

    docker(['compose', 'up', '-d', '--wait'])->run();
}

#[AsTask(description: 'Stop the docker containers')]
function stop(): void
{
    docker(['compose', 'down'])->run();
}

#[AsTask(description: 'Restart the docker containers')]
function restart(bool $force = false): void
{
    stop();
    start($force);
}

#[AsTask(description: 'Install the project dependencies')]
function install(bool $force = false, bool $noStart = false): void
{
    if ($noStart === false && ! docker()->isRunningInDocker()) {
        start();
    }

    symfony_install($force);

    ui_install($force);
}

#[AsTask(description: 'Run the shell in the PHP container')]
function shell(string $user = 'www-data', string $shell = 'fish'): void
{
    php([$shell], user: $user)->run();
}

#[AsTask(name: 'console', description: 'Run the Symfony console in the PHP container')]
function sf_console(array $cmds): void
{
    console($cmds)->run();
}
