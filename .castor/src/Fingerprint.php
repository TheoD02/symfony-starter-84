<?php

declare(strict_types=1);

use function Castor\finder;
use function Castor\hasher;

class Fingerprint
{
    public function docker(): string
    {
        $backendFolder = symfony_context()->workingDirectory;
        $frontendFolder = frontend_context()->workingDirectory;

        return hasher()
            ->writeFile("{$backendFolder}/Dockerfile")
            ->writeFile("{$backendFolder}/compose.override.yaml")
            ->writeFile("{$backendFolder}/compose.yaml")
            ->writeWithFinder(finder()->in("{$backendFolder}/.docker")->files())
            ->writeFile("{$frontendFolder}/Dockerfile")
            ->writeFile("{$frontendFolder}/compose.override.yaml")
            ->writeFile("{$frontendFolder}/compose.yaml")
            ->finish()
        ;
    }

    public function composer(): string
    {
        $folder = symfony_context()->workingDirectory;

        return hasher()
            ->writeFile("{$folder}/composer.json")
            ->writeFile("{$folder}/composer.lock")
            ->finish()
        ;
    }

    public function yarn(): string
    {
        $folder = frontend_context()->workingDirectory;

        return hasher()
            ->writeFile("{$folder}/package.json")
            ->writeFile("{$folder}/yarn.lock")
            ->finish()
        ;
    }
}
