# HR-Management

To get started, make sure you have Docker installed on your system, and then clone this repository.

Next, navigate in your terminal to the directory you cloned this, and spin up the containers for the web server by running 'docker-compose up -d --build app'

Note: Your MySQL database host name should be: 'mysql', not localhost. The username and database should both be: 'homestead' with a password of secret.

Three additional containers are included that handle Composer, NPM, and Artisan commands

'docker-compose run --rm composer update'

'docker-compose run --rm npm run dev'

'docker-compose run --rm artisan migrate'

Bring any container(s) down with: 'docker-compose down'


Next, from your terminal running: 'docker-compose run --rm composer update'

then migrate by running: 'docker-compose run --rm artisan migrate'

after that set the encryption key for passport package: 'docker-compose run --rm artisan passport:install'

at the last run:  'docker-compose run --rm artisan key:generate'

Now you reeady to run the project :)

# Queue-Job
the queue job has been used in import csv file of employee to DB aslo for send mail, make sure to run: 'docker-compose run --rm artisan queue:work'

Mailtrap used for testing mail.


# Task-schedule
The task schedule used to delete the logs info that have been created older than month ago, to run schedule: 'docker-compose run --rm artisan schedule:work'

# UnitTest
Automated test used also to make sure the system is reliable and unbroken, to use test: 'docker-compose run --rm artisan test'

# Custom-Command
1- custom command to export database: 'docker-compose run --rm artisan export:DB'

2- custom command to remove log files: 'docker-compose run --rm artisan remove:log'

3- custom command to export all employees to a json file: 'docker-compose run --rm artisan export:employee'

4- custom command to insert 1000 rows of fake data into employees table: 'docker-compose run --rm artisan fake:employee'
