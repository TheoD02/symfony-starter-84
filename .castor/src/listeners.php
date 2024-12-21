<?php

declare(strict_types=1);

use Castor\Attribute\AsListener;
use Castor\Event\BeforeExecuteTaskEvent;
use Symfony\Component\Process\ExecutableFinder;

use function Castor\check;
use function Castor\context;
use function Castor\finder;
use function Castor\fingerprint_exists;
use function Castor\fs;
use function Castor\io;
use function Castor\task;
use function symfony\symfony_install;
use function ui\ui_install;

#[AsListener(BeforeExecuteTaskEvent::class)]
function check_docker_presence(): void
{
    check(
        'Check if the docker is installed',
        'Docker is required to run this project. Please install it.',
        static fn (): bool => new ExecutableFinder()->find('docker') !== null,
    );
}

#[AsListener(BeforeExecuteTaskEvent::class)]
function prevent_running_certains_command_inside_docker(): void
{
    $taskName = task()?->getName();

    if (in_array($taskName, ['start', 'stop', 'restart', 'build'], true)) {
        docker()->preventRunningInsideDocker();
    }
}

#[AsListener(BeforeExecuteTaskEvent::class)]
function ensure_project_has_run_setup_before_any(): void
{
    $taskName = task()?->getName();

    if ($taskName === 'setup') {
        return;
    }

    $files = finder()
        ->in(root_context()->workingDirectory)
        ->name(['*.yml', '*.yaml', '*.json', '*.bru'])
        ->notName(['vendor', 'node_modules'])
        ->contains(['{{PREFIX_CONTAINER}}', '{{PREFIX_URL}}', 'PROJECT_NAME'])
        ->count()
    ;

    if ($files > 0) {
        io()->error(['The project has not been set up yet.', 'Please run the `castor setup` command first.']);

        exit(1);
    }
}

#[AsListener(BeforeExecuteTaskEvent::class)]
function prevent_running_wrong_context_for_deploy(): void
{
    $taskName = task()?->getName();
    $contextName = context()->name;

    if ($taskName === 'deploy:both') {
        return;
    }

    $deployContextNames = [symfony_context()->name, frontend_context()->name];
    if ($taskName === 'deploy' || str_contains($taskName, 'deploy:')) {
        if (! in_array($contextName, $deployContextNames, true)) {
            io()->error([
                'You are trying to deploy the project using the wrong context.',
                'Please use the ' . implode(' or ', $deployContextNames) . ' context.',
            ]);

            exit(1);
        }

        $data = context()->data;

        if (! isset($data['registry'], $data['image'])) {
            io()->error('The data key "registry" and "image" are missing in the context.');
            exit(1);
        }
    }
}

#[AsListener(BeforeExecuteTaskEvent::class)]
function check_docker_containers_is_running(): void
{
    if (docker()->isRunningInDocker()) {
        return;
    }

    $taskName = task()?->getName();

    if ($taskName === 'deploy' || str_contains($taskName, 'deploy:')) {
        return;
    }

    if (in_array($taskName, ['reset-project', 'setup', 'start', 'stop', 'restart', 'install', 'build'], true)) {
        return;
    }

    $notRunningServices = docker()->isServicesRun(['php', 'front', 'database']);
    if ($notRunningServices) {
        io()->note('The following services are not running:');
        io()->listing($notRunningServices);

        io()->error([
            'Please run the `castor start` command to start the services.',
            'If start fail, debug the issue by seeing the logs with `docker compose logs <service>`.',
        ]);

        exit(1);
    }
}

#[AsListener(BeforeExecuteTaskEvent::class)]
function autorun_install_when_missing_deps(): void
{
    $taskName = task()?->getName();

    if (in_array($taskName, ['install', 'reset-project', 'setup', 'start', 'stop', 'restart', 'build'], true)) {
        return;
    }

    $backendFolder = symfony_context()->workingDirectory;
    $frontendFolder = frontend_context()->workingDirectory;

    $missingDeps = [];

    if (fs()->exists("{$backendFolder}/composer.json") && ! fs()->exists("{$backendFolder}/vendor")) {
        $missingDeps['Composer dependencies'] = static fn () => symfony_install(force: true);
    }

    if (fs()->exists("{$frontendFolder}/package.json") && ! fs()->exists("{$frontendFolder}/node_modules")) {
        $missingDeps['Node dependencies'] = static fn () => ui_install(force: true);
    }

    if ($missingDeps) {
        io()->warning('Some dependencies seem to be missing:');
        io()->listing(array_keys($missingDeps));

        io()->writeln('Installing them now...');
        foreach ($missingDeps as $depName => $installTask) {
            io()->writeln("Installing {$depName}...");
            $installTask();
        }
    }
}

#[AsListener(BeforeExecuteTaskEvent::class)]
function check_outdated_deps(): void
{
    $taskName = task()?->getName();

    if (in_array($taskName, ['install', 'reset-project', 'setup', 'start', 'stop', 'restart', 'build'], true)) {
        return;
    }

    $backendFolder = symfony_context()->workingDirectory;
    $frontendFolder = frontend_context()->workingDirectory;

    $outdatedFingerprints = [];

    if (fs()->exists("{$backendFolder}/composer.json") && fingerprint_exists(
        'composer-install',
        fgp()->composer()
    ) === false) {
        $outdatedFingerprints['Composer dependencies'] = null;
    }

    if (fs()->exists("{$frontendFolder}/package.json") && fingerprint_exists('yarn-install', fgp()->yarn()) === false) {
        $outdatedFingerprints['Node dependencies'] = null;
    }

    if ($outdatedFingerprints) {
        io()->warning('Some dependencies seem to be outdated:');
        io()->listing(array_keys($outdatedFingerprints));
    }
}
