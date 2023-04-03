1) run docker-compose up --build
2) run docker ps (remember tne name of the container with php)
3) run docker exec -it <name of the coinainer with php> bash
4) configure msql connection with credentials from .env 
5) run in container "php bin/console doctrine:migrations:migrate"
6) run "php bin/console img-parser "https://example.com"  <depth(example:5)> <timeout(example:20)> <max-pages(example:2)
7) open "localhost:85"
8) run in container "vendor/bin/phpunit tests/SiteParserTest.php" to check tests

