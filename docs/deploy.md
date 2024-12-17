# Deployment to Dokploy Documentation

## Introduction

This documentation explains how to set up and deploy to Dokploy using the Castor CLI or via GitHub Actions.

## Deploying from a Local Source

### Prerequisites

- Have completed the application setup and answered **Yes** to the question about using Docker image deployment.
- Have a Docker registry to push your images to (Dokploy provides a self-hosted registry, but you can use another
  registry).
- Have your Docker registry credentials (username, password, URL).

### Castor Configuration

Edit the `.castor/src/infra.php` file to define the correct variables for your Docker deployment. Example:

```php
#[AsContext(name: 'docker')]
function docker(): Context
{
    return new Context(
        data: [
            'registry' => 'https://docker-registry.theo-corp.fr/theod',
            'image' => 'sf-test',
        ],
    );
}
```

### Logging into the Docker Registry

Before deploying, log in to your Docker registry using the following command:

```bash
docker login
```

Alternatively, you can use this command to prompt for your credentials:

```bash
castor login
```

### Local Deployment

Once logged in, run the following command to build and push your Docker image:

```bash
castor deploy
```

Use the `--override` option to keep the current version and just rebuild and push the image.

Use the `--help` option to see all available options.

### Manual Deployment on Dokploy

Currently, automatic deployment from the local environment is not supported by Dokploy. You will need to manually
configure your application with the correct image and version, then deploy manually through the Dokploy interface.

---

## Automatic Deployment via GitHub Actions

To automate deployment using GitHub Actions, you need to add variables and secrets in your GitHub repository.

### GitHub Secrets

- **DOCKER_REGISTRY**: Link to your Docker registry without `http/https`
- **DOCKER_USERNAME**: Username for registry access
- **DOCKER_PASSWORD**: Password for registry access
- **DOKPLOY_API_KEY**: Dokploy API Key (generated or found under `Settings` > `Profile` > `API/CLI`, see:
  `<your-dokploy-url>/dashboard/settings/profile`)

### GitHub Variables

- **DOKPLOY_APPLICATION_ID**: Application ID found in your Dokploy project URL:
  `<your-dokploy-url>/dashboard/project/{project-id}/services/application/{application-id}` (the ID is
  `{application-id}`)
- **DOKPLOY_BASE_URL**: Base URL for accessing Dokploy, e.g., `https://dokploy.domain.com`
- **DOKPLOY_DOCKER_IMAGE**: Docker image name to deploy, including the registry name (e.g.,
  `docker-registry.domain.com/theod02/sf-test`)

### Deployment via GitHub Actions

1. Go to the **Actions** page of your GitHub repository.
2. Start the action named `Deploy Docker Image`, which will build and push the Docker image. This trigger is manual and
   requires an **input** for the `Tag to deploy`. Please follow SemVer conventions when tagging.
3. To deploy this image to Dokploy, manually trigger the `Deploy to Dokploy` action, which will prompt you for the image
   tag to deploy.

### Monitoring Deployment

Once you run the deployment action, Dokploy will trigger a deployment for the new tag. You can follow the progress
directly in the Dokploy interface.

**Note:** For now, there is no feedback in GitHub Actions on whether the deployment was successful or not.

## TODO

- Add support for managing VERSION file indepently of the `castor deploy` command
- Add support for feedback on GitHub Actions on whether the deployment was successful or not

## Conclusion

This documentation outlines the process of deploying your application via Dokploy, either from a local environment or
automatically via GitHub Actions.