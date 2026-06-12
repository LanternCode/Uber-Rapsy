# RAPPAR
Formerly known as Uber Rapsy, RAPPAR is a fullstack web application dedicated to reviewing Polish Hip-Hop music.

## Project Configuration
1. Include client_secret.json into the "application/api" directory.


2. Include api_key.txt into the "application/api" directory.

    Source: https://console.cloud.google.com/apis/credentials.


3. Include refresh_token.txt into the "application/api" directory.

    Source: Generate New Refresh Token within the app.


4. Include database_credentials.txt into the "application/api" directory.

    File Format:

    _hostname_

    _username_

    _password_

    Source: Default localhost credentials or hosting provider.


5. Include smtp.json into the "application/api" directory to send emails. The format must match that of MailService.php and contain the following fields: host, port, username, password, from_email, from_name, reply_to.

    Source: Setup an inbox within your hosting provider. Connect it with PHPMailer using the information above.


6. Include maintenance_ips.txt into the "application/api" to allow a maintainer to update the app in maintenance mode. Include a single ip address in the first line.


7. Import the rappar.sql export into your phpMyAdmin.


8. For admin privileges, set your account's role to 'reviewer' in the database.


9. Set up the certificate to launch on localhost.

    Source: https://stackoverflow.com/questions/60788072/curl-error-60-ssl-certificate-problem-unable-to-get-local-issuer-certificate.


10. Run ``composer update`` in the cmd/git bash in the application root and make sure "RAPPAR/vendor" exists.

## Coding Guidelines
1. All PHP code must comply with PSR-1 and PSR-12.
2. All PHP methods and classes must be documented using PHPDocs.
3. The following models are part of autoload: LogModel, SecurityModel and UtilityModel. The following libraries and services are part of autoload: htmlsanitiser (HTML Purifier). No new entries may be added without the product owner's permission.
4. Direct access to $_SESSION is disabled. Use the getters and setters in SecurityModel to authenticate and authorise users.
5. Page titles must match the following format: {what action is being carried out on which item, or a general description of the page if it does not touch a specific item} | {(optional) details of or about the action} - RAPPAR.

## Copyright Notice

All Rights Reserved &copy; LanternCode 2019
