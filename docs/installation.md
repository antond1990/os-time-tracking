## Introduction

OS Time Tracking is installed like a normal Symfony project:

## 1. Pull or download

Clone with SSH: `git@git.webworks-nuernberg.de:wwnbg/timetracking.git`  
**Note:** If you use ssh to clone the project, you must have a GitLab account at git.webworks-nuernberg.de to add your ssh pub key. See https://docs.gitlab.com/ee/gitlab-basics/create-your-ssh-keys.html for instructions.

Clone with HTTPS: `https://git.webworks-nuernberg.de/wwnbg/timetracking.git`

### Download

Download the latest release from https://git.webworks-nuernberg.de/wwnbg/timetracking/-/releases

After download, extract the files from the archive.

## 2. Create your config

Please see the `.env` file for config parameters:
```
###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=d547f38b2614e65cd77cb2252ff6cf00
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
###< doctrine/doctrine-bundle ###

###> symfony/swiftmailer-bundle ###
MAILER_URL=null://localhost
###< symfony/swiftmailer-bundle ###

APP_LOGO_PATH=/build/images/webworks-nuernberg-logo.jpg

LOCALE=en
```

See https://symfony.com/doc/current/components/dotenv.html for questions about DotEnv with Symfony.

## 3. Install required packages via composer

Run `composer install` in the root folder of the project.

## 4. Create the database

1. Create migration: `php sf doctrine:migrations:diff`
2. Run migrations: `php sf doctrine:migrations:migrate`

### For dev: Load data fixtures:

The database will be purged if you run this command!

Run `php sf doctrine:fixtures:load`

## 5. Configure your web server 

Please use the Symfony documentation: https://symfony.com/doc/current/setup/web_server_configuration.html 

## 6. Install npm packages

You need npm on your machine. Please read the docs about installation of npm: https://www.npmjs.com/get-npm

Install packages from `packages.json`:
`npm install`

### Run gulp

Run the gulp file:

`gulp`

or 

`gulp dev`

or

`gulp watch`

## 7. Create admin user

You can create an first admin user with the following command:

`php bin/console app:create:admin --env=prod`

You will be asked for the required data.
