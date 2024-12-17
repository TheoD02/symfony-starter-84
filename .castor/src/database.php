<?php

declare(strict_types=1);

namespace database;

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\fs;
use function Castor\io;
use function Castor\run;
use function utils\ensure_directory_exists;

#[AsTask(name: 'database:reset', description: 'Reset the database', aliases: ['db:reset'])]
function resetDatabase(): void
{
    $output = run(
        command: "docker compose exec -it database sh -c \"psql -d app -c '\\l'\"",
        context: context()->withQuiet(),
    )->getOutput();

    if (str_contains($output, 'app')) {
        io()->info('Database "app" already exists.');

        if (io()->confirm('Do you want to drop the database and recreate it?') === false) {
            return;
        }
    }

    console(commands: ['doctrine:database:create', '--if-not-exists'])->run();
    console(commands: ['doctrine:schema:drop', '--full-database', '--force'])->run();
    console(commands: ['doctrine:migrations:migrate', '--no-interaction', '--allow-no-migration'])->run();
    console(commands: ['doctrine:fixtures:load', '--no-interaction', '--append'])->run();
}

#[AsTask(name: 'backup', description: 'Backup the database', aliases: ['db:backup'])]
function db_backup(): void
{
    $backupDirectory = root_context()->data['backupDirectory'] ?? root_context()->workingDirectory . '/.backup/database';

    ensure_directory_exists($backupDirectory);

    $backupFile = "{$backupDirectory}/database.sql";

    io()->info("Backing up the database to {$backupFile}");

    if (fs()->exists($backupFile)) {
        io()->info(
            "A backup already exists at {$backupFile}. If you want to create a new backup, delete the existing backup file."
        );

        return;
    }

    run("docker compose exec -T database pg_dump -U root app > {$backupFile}", context: context()->withTty());

    io()->success("Database backed up to {$backupFile}");
}

#[AsTask(name: 'restore', description: 'Restore the database from a backup', aliases: ['db:restore'])]
function db_restore(): void
{
    $backupDirectory = root_context()->data['backupDirectory'] ?? root_context()->workingDirectory . '/.backup/database';

    ensure_directory_exists($backupDirectory);

    $backupFile = "{$backupDirectory}/database.sql";

    io()->info("Restoring the database from {$backupFile}");

    if (! fs()->exists($backupFile)) {
        io()->error("No backup found at {$backupFile}");

        return;
    }

    run("cat {$backupFile} | docker-compose exec -T database psql -U root app", context: context()->withTty());

    io()->success("Database restored from {$backupFile}");
}
