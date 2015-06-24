# What is this?

This project is a demo to show how php can handle stuff asynchronously using the react event loop.

# How to use

Before you start you should install the eio extension if you do not already have it. This can be done using pecl:

```
sudo pecl install eio
```


Clone the project using git, get into the project directory and run:

```
composer install
```

After that you should be able to run:

```
php server.php
```

Go to [localhost:1337/index.html](http://localhost:1337/index.html) no webserver like apache/nginx required.
