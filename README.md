# MofgForm [![CircleCI](https://circleci.com/gh/g737a6b/php-form.svg?style=svg)](https://circleci.com/gh/g737a6b/php-form)

PHP form library.

## Examples of use

See `demo/`.

https://mofg.net/oss/php-form/demo/

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

### Run tests

```sh
docker run -it --rm -v $(pwd):/app composer:1.8 run-script tests
```

## License

[The MIT License](http://opensource.org/licenses/MIT)

Copyright (c) 2019 [Hiroyuki Suzuki](https://mofg.net)
