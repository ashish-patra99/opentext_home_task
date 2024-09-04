
# Debricked Rule Engine

This application is built using the PHP Symfony framework and integrates with Debricked APIs to upload and scan dependency files.

## Table of Contents

* [Overview](#overview)
* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
* [API Documentation](#api-documentation)
* [Contributing](#contributing)
* [License](#license)

## Overview

The Debricked Rule Engine is designed to simplify the process of scanning and analyzing dependency files using Debricked APIs. This application can be used from any HTTP client tool supporting REST API for uploading and scanning dependency files, making it easier to identify potential security vulnerabilities and trigger notifications.

## Requirements

* PHP 7.4 or higher
* Symfony 6.1 or higher
* Debricked API credentials

## Installation

1. Clone the repository: `git clone https://github.com/ashish-patra99/opentext_home_task.git`
2. Install dependencies: `composer install`
3. Configure Debricked API credentials: update `.env` with your credentials
4. Configure Database credentials , slack channel DSN and mailer DSN in .env file to use notification
5. Execute migrations:- 
    php bin/console make:migration 
    php bin/console doctrine:migrations:migrate 
6. Execute to consume messages: php bin/console messenger:consume async    (php amqp extension must be installed)



## Usage

1. We can start with docker container 
### Start
```bash
docker-compose up -d

```

### Stop
```bash
docker-compose down 
```

2. Create a JWT token: setup your Debricked access token in .env file and send a GET request to http://yourhost/api/jwt (Ex:-http://localhost:8080/apijwt, http://opentextapi.local/api/jwt(virtual host setup))

3. Upload dependency files:
4. Scan dependency files: Send a POST request to http://yourhost/api/scanFiles attaching single or multiple files in body
5. Get scan results: Send a GET request to http://yourhost/api/scanPending
6. Schedule your command in docker crontab file  `php bin/console app:send-notification` to execute in every 5 mins to check if scanning is completed for uploaded files and trigger notification 

## API Documentation

API documentation can be found at [Debricked API Documentation](https://debricked.com/api/doc/open). Check APIs in category "Dependency File management"


## License
[MIT](https://choosealicense.com/licenses/mit/)