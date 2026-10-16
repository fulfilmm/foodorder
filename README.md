## Installation Setup
```
git clone https://github.com/fulfilmm/foodorder.git
```
  - Install PHP 8.2 or switch PHP version to 8.2 on OS
  - Install Node(npm) on OS
  - Install Composer on OS
  - Go to project directory path ```cd foodorder ```
  - Create Database in mysql or db server
  - Create new env by copy env.example and then Update .env file DB Name, DB Password
  - Go to  pusher dashboard ``` https://dashboard.pusher.com/apps/2202770/keys ```
  - Make sure the pusher credential (development) is correct or not
  ```
  BROADCAST_DRIVER=pusher
  PUSHER_APP_ID=2202770
  PUSHER_APP_KEY=9c6c62a720e9afb0262e
  PUSHER_APP_SECRET=1659324facd12e940532
  PUSHER_APP_CLUSTER=ap3
  ```
  - Run the following command one by one 
  ```
 composer install
  ```
  ``` 
  npm install
 ```
  ```
  php artisan key:generate
 ```
  ``` 
  php artian migrate
 ```
  ``` 
  php artisan db:seed
 ```
  ``` 
  php artisan serve
 ```
  and then open a new terminal tab and the run the following queue work command for live time notification
 ```
 php artisan queue:work
```

 
