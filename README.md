# Install

Config .env. Open cmd and go to project folder, then run command:
+ composer install
+ php artisan migrate
+ php artisan db:seed

# Get users API
+ return list user most charge
+ url: your_domain/api/public/api/v1/users?limit=
+ param: limit : number users limit
 

# Send email
+ send email to users
+ url: your_domain/api/public/api/v1/users/sendemails?type=
+ param: type with value "30m" or "1day"
