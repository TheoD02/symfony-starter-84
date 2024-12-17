<?php

declare(strict_types=1);

namespace ui;

use Castor\Attribute\AsTask;

use function Castor\fingerprint;
use function Castor\fs;
use function Castor\io;

#[AsTask(name: 'install', description: 'Install the project dependencies')]
function ui_install(bool $force = false): void
{
    if (
        ! fingerprint(
            callback: static fn () => yarn(['install'])->run(),
            id: 'yarn-install',
            fingerprint: fgp()->yarn(),
            force: $force || ! fs()->exists(frontend_context()->workingDirectory . '/node_modules'),
        )
    ) {
        io()->note('The package.json file has not changed since the last run, skipping the yarn install command.');
    }
}

#[AsTask(name: 'build', description: 'Build the project')]
function ui_build(): void
{
    yarn(['build'])->run();
}

#[AsTask(name: 'dev', description: 'Run the development server')]
function ui_dev(): void
{
    yarn(['dev'])->run();
}
