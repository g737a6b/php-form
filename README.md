# MofgForm ![main](https://github.com/g737a6b/php-form/workflows/main/badge.svg)

A PHP library for building and handling web forms.

## Usage Examples

See `demo/`.

```sh
docker run -it --rm -p 8080:80 -v $(pwd):/var/www/html php:8.5-apache
# http://localhost:8080/demo/
```

## Features

MofgForm is designed for building a wide range of web forms.

- All basic input types (text, select, radio, checkbox, textarea and password)
- Unlimited pages
- Validation
- Filtering
- HTML generation
- Email sending
- Form submission summary
- Lightweight
- Installation using Composer
- MIT License

## Installation

### Composer

Add a dependency to your project's `composer.json` file.

```json
{
	"require": {
		"g737a6b/php-form": "*"
	}
}
```

## Development

### Install dependencies

```sh
docker run -it --rm -v $(pwd):/app composer:2.9.2 install
```

### Run tests

```sh
docker run -it --rm -v $(pwd):/app -w /app php:8.5 ./vendor/bin/phpunit ./tests
```

### Format code

```sh
docker run -it --rm -v $(pwd):/app -w /app php:8.3 ./vendor/bin/php-cs-fixer fix ./src
```

## License

[The MIT License](http://opensource.org/licenses/MIT)

Copyright (c) 2016-2026 [Hiroyuki Suzuki](https://mofg-in-progress.com)
