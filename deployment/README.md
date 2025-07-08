# Deployment Documentation

## Overview

The Interactive Landscape Tool is deployed using a GitLab CI/CD pipeline with Helm charts across three environments using Kubernetes with Docker containers.

## Environments

- **DEV**: `itl.dev.deployai.eu` (automatic deployment on `main` branch)
- **STAGING**: `itl.stage.deployai.eu` (manual deployment)
- **PRODUCTION**: `business-navigator.aiodp.ai` (manual deployment)

## Required GitLab CI/CD Variables

Configure these variables in GitLab with **Protect variable** and **Mask and hidden** flags:

| Variable | Description |
|----------|-------------|
| `OPENCAGE_API_KEY` | OpenCage geocoding API key |
| `MAPBOX_ACCESS_TOKEN` | Mapbox access token (URL-restricted) |
| `LARAVEL_APP_KEY` | Laravel application key |
| `DATABASE_PASSWORD` | Database password |
| `DATABASE_ROOT_PASSWORD` | Database root password |
| `BASIC_AUTH` | Basic authentication credentials |
| `DEFAULT_ADMIN_EMAIL` | Admin email for user seeding |
| `DEFAULT_USER_PASSWORD` | Default password for seeded users |

**Important**: Do NOT enable "Expand variable reference" for these variables.

## Deployment Architecture

- **Container Platform**: Kubernetes with Helm charts
- **Docker Registry**: Harbor (`harbor.deployai.eu`)
- **Environment Configuration**: Via `envsubst` using `.env.example.production`
- **Secrets Management**: Kubernetes secrets with base64 encoding
- **Storage**: Persistent volumes for cache and file storage

## Files Structure

```
deployment/
├── docker/laravel/php/entrypoint.d/99-create-env.sh  # Environment setup
├── helm/
│   ├── templates/
│   │   ├── deployment.yaml           # Kubernetes deployment
│   │   ├── laravel-secrets.yaml      # Application secrets
│   │   └── ...
│   ├── values-dev.yaml               # DEV configuration
│   ├── values-staging.yaml           # STAGING configuration
│   └── values-production.yaml        # PRODUCTION configuration
└── .gitlab-ci.yml                    # CI/CD pipeline
```

## Environment Variable Flow

The same API key (e.g., `OPENCAGE_API_KEY`) appears in multiple files due to **separation of concerns** in Kubernetes:

### 1. GitLab CI/CD Variable (Source)
```
GitLab UI: OPENCAGE_API_KEY = "your-actual-api-key"
```
**Purpose**: Secure storage of the actual secret value

### 2. `.gitlab-ci.yml` (Pipeline)
```yaml
--set laravel.opencage_api_key=${OPENCAGE_API_KEY}
```
**Purpose**: Passes the secret from GitLab to Helm during deployment

### 3. `values-production.yaml` (Helm Values)
```yaml
laravel:
  opencage_api_key:  # This gets filled by the pipeline
```
**Purpose**: Defines the structure for Helm to receive the value

### 4. `laravel-secrets.yaml` (Kubernetes Secret)
```yaml
data:
  OPENCAGE_API_KEY: {{ .Values.laravel.opencage_api_key | b64enc | quote }}
```
**Purpose**: Creates an encrypted Kubernetes secret with base64 encoding

### 5. `deployment.yaml` (Pod Environment)
```yaml
env:
- name: OPENCAGE_API_KEY
  valueFrom:
    secretKeyRef:
      name: "{{ .Release.Name }}-wp-secrets"
      key: OPENCAGE_API_KEY
```
**Purpose**: Injects the secret as an environment variable into the container

### 6. `.env.example.production` (Laravel Config)
```env
OPENCAGE_API_KEY=$OPENCAGE_API_KEY
```
**Purpose**: Template that gets the actual value substituted by `envsubst`

### Why This Complex Flow?

**Security & Separation**: Each layer has a specific responsibility:
- **GitLab**: Stores the actual secret securely
- **Helm**: Manages deployment configuration
- **Kubernetes**: Encrypts secrets at rest
- **Container**: Gets environment variables securely
- **Laravel**: Uses final configuration

Secrets never appear in plain text in configuration files—only in encrypted Kubernetes secrets.

## Deployment Process

### Automatic Deployment (DEV)
- Triggered on every push to `main` branch
- Uses `values-dev.yaml` configuration

### Manual Deployment (STAGING/PRODUCTION)
1. Navigate to GitLab project → CI/CD → Pipelines
2. Find the pipeline for your commit
3. Click the manual deployment button for the desired environment
4. Confirm the deployment when prompted

## Troubleshooting

### Common Issues

1. **Missing GitLab Variables**: Ensure all required variables are configured with proper flags
2. **Environment Substitution Failure**: Check that variable names match between GitLab and Helm values
3. **Deployment Timeout**: Monitor Kubernetes logs for pod startup issues
4. **Database Connection**: Verify database credentials and connectivity

### Monitoring

- **GitLab CI/CD**: Check pipeline logs for deployment status
- **Kubernetes**: Monitor pod health and resource usage
- **Application**: Use Laravel Pail for real-time log monitoring
