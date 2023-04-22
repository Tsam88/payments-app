## Install PaymentApp

- Open a terminal in the desired directory where you would like to clone the project
- Run the command 'git clone https://github.com/Tsam88/payments-app.git'
- Run 'cd payments-app/'
- Copy the .env.example to .env (cp .env.example .env)
- In the .env file replace the DB variables:
  - DB_CONNECTION=mysql 
  - DB_HOST=payments-db
  - DB_PORT=3306
  - DB_DATABASE=payments
  - DB_USERNAME=root
  - DB_PASSWORD=payments
- In the .env file the two variables (DOCKER_USER=www, DOCKER_USER_ID=1000) point at the variables (user, uid) in the Dockerfile file. You may have to change them accordingly if you are not logged-in as root user
- In the '/payments-app' directory build and start docker containers running the commands:
  - docker-compose build
  - docker-compose up -d
- Get into the payments-app container running the command 'docker exec -it payments-app bash'
- And into the container run the commands below:
  - php artisan key:generate
  - composer install
  - php artisan migrate

NOTE: Running the migrations, two Users with Merchant role are created. 
Their Api Keys (for sandbox accounts (Stripe and Everypay)) have been already set in the merchants_settings table in the DB. 
You can log in using the credentials below:
- stripe.merchant@test.com::test1234
- everypay.merchant@test.com::test1234

## Api End Points and Payloads

- Register (The app supports two different user roles, Customer and Merchant)
- Register a Customer
  - POST /users/register
  -     {
            "name": "customer",
            "email": "customer@test.gr",
            "password": "test1234",
            "user_role_id": 1
        }
  - user_role_id: indicates the id of the available user roles in the table user_roles in the DB


- Register a Merchant
  - POST /users/register
  -     {
            "name": "merchant",
            "email": "merchant@test.gr",
            "password": "test1234",
            "user_role_id": 2,
            "merchant_settings": {
                 "psp_api_key": "sk_iJOL1iWlKDylHRjCxnBenXN7wGfZjw48",
                 "payment_service_provider_id": 2,
            },
        }
  - user_role_id: indicates the id of the available user roles in the table user_roles in the DB
  - payment_service_provider_id: indicates the id of the available Payment Service Providers in the table payment_service_providers in the DB


- Login
  - POST /users/login
  -     {
            "email": "everypay.merchant@test.com",
            "password": "test1234"
        }
    Note: The app uses Bearer token as authentication type. So the token that is returned can be used for the end points that require authorization.

    
- Logout
  - POST /users/logout


- Update Merchant's PSP method and ApiKey
  - PATCH /merchant-settings/update/{merchantSettingsId}
  -     {
            "payment_service_provider_id": 1,
            "psp_api_key": "sk_iJOL1iWlKDylHRjCxnBenXN7wGfZjw48",
        }
  - payment_service_provider_id: indicates the id of the available Payment Service Providers in the table payment_service_providers in the DB
  - psp_api_key: Payment Service Provider's Api Key


- Create a payment
  - POST /payments/create/{merchantId}
  -     {
            "card": {
                 "card_number": "4111111111111111",
                 "expiration_date": "12/2023",
                 "cvv": 123,
                 "cardholder_name": "John Doe"
            },
            "amount": 1.5
        }
