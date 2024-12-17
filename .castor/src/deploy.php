<?php

declare(strict_types=1);

namespace deploy;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Castor\Exception\ProblemException;
use PHLAK\SemVer\Version;

use function Castor\context;
use function Castor\fs;
use function Castor\import;
use function Castor\io;
use function Castor\with;

import('composer://phlak/semver');

#[AsTask(name: 'build-image', description: 'Build the image for deployment')]
function deploy_build_image(string $tag = 'latest'): void
{
    $buildArgs = [
        'BUILD_TIME' => new \DateTime(timezone: new \DateTimeZone('Europe/Paris'))->format('Y-m-d\TH:i:s'),
    ];

    docker([
        'build',
        '--progress', 'plain',
        '--build-arg', ...array_map(
            static fn ($key, $value) => \sprintf('%s="%s"', $key, $value),
            array_keys($buildArgs),
            $buildArgs
        ),
        '-t', generate_image_name($tag),
        context()->workingDirectory,
    ])
        ->run()
    ;
}

#[AsTask(name: 'push-image', description: 'Push the image to the registry')]
function deploy_push_image(string $tag = 'latest'): void
{
    docker(['push', generate_image_name($tag)])->run();
}

function build_and_push(string $tag = 'latest'): void
{
    deploy_build_image($tag);
    deploy_push_image($tag);
}

#[AsTask(name: 'deploy:both', namespace: '', description: 'Build and push the image for symfony and frontend')]
function deploy_both(
    #[AsOption(name: 'override', description: 'Keep the current version, and rebuild the image')]
    bool $override = false,
): void {
    io()->title('Deploying Symfony');
    with(static fn () => deploy($override), context: symfony_context());

    io()->title('Deploying Frontend');
    with(static fn () => deploy($override), context: frontend_context());
}

#[AsTask(name: 'deploy', namespace: '', description: 'Deploy the application')]
function deploy(
    #[AsOption(name: 'override', description: 'Keep the current version, and rebuild the image')]
    bool $override = false,
): void {
    $rootDirectory = context()->workingDirectory;
    $versionFile = $rootDirectory . '/VERSION';

    if (fs()->exists($versionFile) === false) {
        fs()->dumpFile($versionFile, 'v0.0.0-dev');
    }

    $textVersion = file_get_contents($versionFile);
    $version = Version::parse($textVersion);

    io()->writeln(\sprintf('Current version: %s', $version));

    if ($override) {
        io()->warning('Version overriden');
        if (io()->confirm('You are sure you want to override the version?')) {
            build_and_push($version);
        }

        return;
    }

    $releaseType = io()->choice('Release type', ['patch', 'minor', 'major', 'hotfix', 'prerelease'], 'patch');
    switch ($releaseType) {
        case 'patch':
            $version->incrementPatch();
            break;
        case 'minor':
            $version->incrementMinor();
            break;
        case 'major':
            $version->incrementMajor();
            break;
        case 'hotfix':
            $version->incrementPatch();
            $version->setPreRelease(null);
            break;
        case 'prerelease':
            if ($version->isPreRelease()) {
                $version->incrementPreRelease();
            } else {
                $version->setPreRelease(io()->ask('Pre-release name: ', 'pre-release'));
            }
            break;
        default:
            throw new ProblemException('Invalid release type');
    }

    io()->writeln(\sprintf('New version is %s', $version));
    if (io()->confirm('Do you want to build and push the image?')) {
        file_put_contents($versionFile, (string) $version);
        build_and_push((string) $version);
    }
}

function generate_image_name(string $tag = 'latest'): string
{
    return \sprintf('%s/%s:%s', context()->data['registry'], context()->data['image'], $tag);
}
