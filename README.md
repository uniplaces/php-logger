# Uniplaces monolog extension
Package to support logging standards at uniplaces (www.uniplaces.com).
The first only define a processor to decorate log with specific fields used to create filters.

## Prerequisites

This package needs php 7.0 or a higher version and is meant to run in a symfony 4 application.
Also, is supposed to have composer in the machine where is the project is going to be mounted.

## Installing

To install it locally (for developing purpose) run:

```bash
$ make setup
```

In order to use the common processor you have to register it in the service.yaml:
```yaml
    monolog.common_processor:
        class: Uniplaces\Monolog\Processors\CommonProcessor
        arguments:
            - "@request_stack"
            - '%env(APP_ID)%'
            - "%env(GIT_HASH)%"
            - "%kernel.environment%"
        tags:
            - { name: monolog.processor, method: processRecord }
```

The processor expextes `APP_ID` and `GIT_HASH` to be defined in the environment.

Also define a json formatter in order to be able to add the fields the log must be in json format; because of this you have to register `Monolog\Formatter\JsonFormatter` optionally you can add a coll to includeStacktraces to add stack trace.

```yaml
    monolog.json_formatter:
        class: Monolog\Formatter\JsonFormatter
        calls:
            - [includeStacktraces]
```

## Running the tests

```bash
$ make tests
```

## Contributing

Please read [CONTRIBUTING.md](https://gist.github.com/PurpleBooth/b24679402957c63ec426) for details on our code of conduct, and the process for submitting pull requests to us.

## Authors

Made with :heart: at [uniplaces](www.uniplaces.com)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
