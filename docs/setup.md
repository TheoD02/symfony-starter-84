# Symfony & React Project Repository

This repository contains a Symfony and React project with a split frontend and backend architecture.

## Backend

The backend is a Symfony `7.2.*` application with the following default packages:

### Core Packages:
- `orm`  
- `orm-fixtures`  
- `serializer`  
- `validator`  
- `nelmio/api-doc-bundle`  
- `nelmio/cors-bundle`  
- `logger`  
- `uid`  
- `twig`  
- `asset`

### Development Packages:
- `maker`  
- `foundry`  
- `debug`  
- `web-profiler-bundle`  
- `phpunit/phpunit`

## Requirements

To run this project, the following tools must be installed on your machine:
- **Docker**
- **A running Traefik instance**  
  If you don't already have Traefik set up, you can use this repository: [web-traefik](https://github.com/TheoD02/web-traefik).
- **Castor** (version `0.21.0+`, compatible with **PHP 8.4+**)  
  You can download Castor here: [Castor Documentation](https://castor.jolicode.com/).

## Installation

To install and set up the project, follow these steps:

1. Run the command:  
   ```bash
   castor setup
   ```
This initializes the project.

2. Once the setup is complete, you can start the project with the following command:
   ```bash
   castor start
   ```

## Usage

After running `castor start`, the project will be ready to use by default.

### Resetting the Project

If you want to reset the project to its original Symfony state, you can use the following command:
```bash
castor reset-project
```
This step is **optional** and is not required for the project to function.

#### PHPUnit Warning
When resetting the project, PHPUnit must be manually set up again. To do this:
1. Run `castor shell`.
2. Execute the command:
   ```bash
   vendor/bin/phpunit --generate-configuration
   ```
This will regenerate the PHPUnit configuration file.
