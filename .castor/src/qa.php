<?php

declare(strict_types=1);

namespace qa;

use Castor\Attribute\AsTask;

use function Castor\io;

#[AsTask(description: 'Run all the QA tasks')]
function all(bool $fix = false): void
{
    io()->title('Running all the QA tasks');

    io()->section('Running the Rectors task');
    rector($fix);

    io()->section('Running the ECS task');
    ecs($fix);

    io()->section('Running the PHPStan task');
    phpstan();
    // phparkitect();

    io()->section('Running the PHPMD task');
    phpmd();
    // class_leak_check();

    io()->section('Running the PHPUnit task');
    phpunit();
}

#[AsTask(description: 'Run the ECS task')]
function ecs(bool $fix = false): void
{
    composer(['run'])
        ->addIf(! $fix, 'ecs')
        ->addIf($fix, 'ecs-fix')
        ->run()
    ;
}

#[AsTask(description: 'Run the Rector task')]
function rector(bool $fix = false): void
{
    composer(['run'])
        ->addIf(! $fix, 'rector')
        ->addIf($fix, 'rector-fix')
        ->run()
    ;
}

#[AsTask(description: 'Run the PHPStan task')]
function phpstan(bool $pro = false): void
{
    composer(['run'])
        ->addIf(! $pro, 'phpstan')
        ->addIf($pro, 'phpstan-pro')
        ->run()
    ;
}

#[AsTask(description: 'Run the PHParkitect task')]
function phparkitect(): void
{
    composer(['run', 'phparkitect'])->run();
}

#[AsTask(description: 'Run the PHPMD task')]
function phpmd(): void
{
    composer(['run', 'phpmd'])->run();
}

#[AsTask(description: 'Run the class leak check task')]
function class_leak_check(): void
{
    composer(['run', 'class-leak-check'])->run();
}

#[AsTask(description: 'Run the PHPUnit task')]
function phpunit(): void
{
    composer(['exec', 'phpunit'])->run();
}
