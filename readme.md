# Uber-Rapsy

1. Include client_secret.json into the "application/api" directory

2. Include api_key.txt into the "application/api" directory

Source: https://console.cloud.google.com/apis/credentials

3. Include database_credentials.txt into the "application/api" directory

File Format:

_hostname_

_username_

_password_

Source: Default localhost credentials or hosting provider

4. Import the lanternc_uberrapsy.sql export into phpMyAdmin

5. For admin privileges, set your account's role to 'reviewer' in the database

6. Set up the certificate to launch on localhost

Source: https://stackoverflow.com/questions/60788072/curl-error-60-ssl-certificate-problem-unable-to-get-local-issuer-certificate

7. Run ``composer update`` in the cmd/git bash in the application root and make sure "Uber-Rapsy/vendor" exists

8. Include refresh_token.txt into the "application/api" directory

Source: Generate New Refresh Token within the app

All Rights Reserved &copy; LanternCode 2019
