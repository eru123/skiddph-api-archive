# API
An out of the box complete and centralized general purpose API system that aims to power you web development journey.

# Planned Features
**NOTE:** The planned feaures are still not final, anyone can add or request a feature via [ISSUES](https://github.com/eru123/api/issues)
### Core Features
 - [ ] Database
   - [ ] Database Object-Relational Mapping (ORM)
   - [ ] Multi-Connection Support
 - [ ] Users
   - [ ] Authentication System
   - [ ] User Access Control
   - [ ] User Management System
 - [ ] API Plugin System
   - [ ] Plugin Access Control
   - [ ] Middlewares
 - [ ] Request Access Control
   - [ ] Rate Limiter
   - [ ] Spam Guard
   
### Built-in Plugins
 - [ ] URL Shortener
   - [ ] Custom URL Support
   - [ ] URL Generator
   - [ ] URL Analytics
   - [ ] URL Expiration
   - [ ] URL Password Protection
   - [ ] URL Click Tracking
 - [ ] Services and Products Licensing System
 - [ ] Dev/Student Tools
   - [ ] Encryption
   - [ ] Decryption
   - [ ] Date Tools
   - [ ] Math Problems Solver
 - [ ] File hosting
 - [ ] Proxy
 - [ ] Wiki
 - [ ] CPanel Integration
   - [ ] Email System Associated with User Accounts
 - [ ] POS System
   - [ ] Point of Sale System
   - [ ] Inventory System
   - [ ] Store and Branch based Management System
   - [ ] Item Tracking System or Delivery Report System
 - [ ] HR Tools
   - [ ] Employees
     - [ ] Employee Management System
     - [ ] Hiring/Onboarding System
   - [ ] Shared Calendar
   - [ ] Scheduling System
   - [ ] Analytics and Reporting System
   - [ ] Bio Metrics
     - [ ] Mobile Fingerprint Support
     - [ ] Mobile Face Recognition Support
     - [ ] QR/Bar Code Support
     - [ ] Manual Input Support
   - [ ] Time Tracking
   - [ ] Salary Management System
 - [ ] Learning Management System
   - [ ] Forum
   - [ ] Library System
   - [ ] Attendance System
   - [ ] Permission Based Storage
   - [ ] Registrar
   - [ ] Teacher
     - [ ] Work Load Schedule Distribution
     - [ ] Grading System
     - [ ] Exam System
     - [ ] Discussion
     - [ ] Proctor Support
     - [ ] Syllabus
   - [ ] Student
     - [ ] Group Discussion
     - [ ] Group File Sharing

    
 
# Setup
The system is planned to host on any system that has an ftp server for uploading files, with the help of GitHub actions. As of now, this is the only supported deployment method, but we also planned to support on other major platforms such as AWS, Azure, GCP, Docker, Vercel, Heroku, and Etc.

## Required GitHub Secrets

Go to `https://github.com/{username}/{repository}/settings/secrets/actions` and add required secrets for deployment.

| Name | Description | Example |
| --- | --- | --- |
| `FTP_PASSWORD` | Password for FTP Server | `123456` |
| `FTP_USERNAME` | Username for FTP Server | `admin` |
| `FTP_SERVER` | FTP Server Address | `ftp.domain.com` |
| `JWT_SECRET` | JWT Secret token | `My5up3r53cr3tK3Y` |

## Required Environment Variables

The system will look for `.env` file in the root directory of the project. If it doesn't exist, it will look for environment variables registered in the system.

| Name | Description | Example |
| --- | --- | --- |
| `JWT_SECRET` | Secret token that will mainly use in Authentication | `My5up3r53cr3tK3Y` |

# LICENSE
This project is licensed under [Apache License 2.0](LICENSE)
