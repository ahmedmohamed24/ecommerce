# change directory to src
`cd src` 
# install new laravel app 
`composer create-project --prefer-dist laravel/laravel .`
# install npm dependencies
`npm install`
# build the docker-compose.yml 
`docker-compose up -d --build`
- **nginx** - `:8080`
- **mysql** - `:3306`
- **php** - `:9000`

visit localhost:8080 to test your website 

to stop the service 
`docker-compose down`