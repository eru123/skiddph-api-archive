# api
PHP Based API Platform for General use Microservices and Centralized Authentication System

# Setup
## Require GitHub Secrets

Go to `https://github.com/{username}/{repository}/settings/secrets/actions` and add required secrets for deployment.

| Name | Description | Example |
| --- | --- | --- |
| `FTP_PASSWORD` | Password for FTP Server | `123456` |
| `FTP_USERNAME` | Username for FTP Server | `admin` |
| `FTP_SERVER` | FTP Server Address | `ftp.domain.com` |
| `ENV_JWT_SECRET` | `JWT_SECRET` ENV var | `My5up3r53cr3tK3Y` |

## Required Environment Variables

The system will look for `.env` file in the root directory of the project. If it doesn't exist, it will look for environment variables registered in the system.

| Name | Description | Example |
| --- | --- | --- |
| `JWT_SECRET` | Secret token that will mainly use in Authentication | `My5up3r53cr3tK3Y` |