### PROJECT MOVED
Project has been moved to https://github.com/eru123/jericho.work

# API
An out of the box complete and centralized general purpose API system that aims to power you web development journey.

> **Warning!!** This project is in experimental and active development, structure and features may change at any time.

### Useful links
 - [Roadmap](https://github.com/eru123/api/wiki/Roadmap)

# Setup
The system is planned to host on any system that has an ftp server for uploading files, with the help of GitHub actions. As of now, this is the only supported deployment method, but we also planned to support on other major platforms such as AWS, Azure, GCP, Docker, Vercel, Heroku, and Etc.

## Required Environment Variables

The system will look for `.env` file in the root directory of the project. If it doesn't exist, it will look for environment variables registered in the system.

| Name | Description | Example |
| --- | --- | --- |
| `JWT_SECRET` | Secret token that will mainly use in Authentication | `My5up3r53cr3tK3Y` |
| `DEFAULT_DB_DSN` | Default Database DSN | `mysql:host=localhost;port=3306;dbname=development_db` |
| `DEFAULT_DB_USER` | Default Database Username | `root` |
| `DEFAULT_DB_PASS` | Default Database Password | `root` |


# LICENSE
This project is licensed under [Apache License 2.0](LICENSE)
