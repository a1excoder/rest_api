# My first api application on native PHP

## How to start


```
migrate database file 'rest_api.sql' to mysql server
php -S localhost:800 main.php
if you use linux -> sudo php -S localhost:800 main.php
```

## Contributing

Routes:
```
index page: localhost:800 || localhost:800/page/{page by pagination}
post page by id: localhost:800/post/{id}
page for creating new post by POST method: localhost:800/post/new
page for update post by PATCH method: localhost:800/post/update/{id}
page for delete post by DELETE method: localhost:800/post/delete/{id}
```
