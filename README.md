# MofgForm ![main](https://github.com/g737a6b/php-form/workflows/main/badge.svg)

PHP form library.

## Examples of use

See `demo/`.

```sh
docker run -it --rm -p 8080:80 -v $(pwd):/var/www/html php:7.4-apache
# http://localhost:8080/demo/
```

## Features

MofgForm is suited to all web forms.

- All basic input types (text, select, radio, checkbox, textarea and password)
- Unlimited pages
- Validation
- Filtering
- HTML generation
- Sending email
- Summarizing submitted form
- Lightweight
- Installation using Composer
- MIT Licence

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
docker run -it --rm -v $(pwd):/app composer:2.0 install
```

### Run tests

```sh
docker run -it --rm -v $(pwd):/app composer:2.0 run-script tests
```

## License

[The MIT License](http://opensource.org/licenses/MIT)

Copyright (c) 2021 [Hiroyuki Suzuki](https://mofg.net)
