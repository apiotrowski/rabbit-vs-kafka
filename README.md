# Rabbit

### Dependencies ###
* Docker CE (https://docs.docker.com/install/)
* Docker Compose (https://docs.docker.com/compose/install/)

Please add below entry to hosts file (on Linux it's `/etc/hosts`:
* `127.0.0.1 app.local rabbitmq.app.local mailhog.app.local` 

Copy file .env.dist as .env to project directory.

#### URL ####
* `http://app.local/` - app homepage
* `http://rabbitmq.app.local/` - RabbitMQ web UI
* `http://mailhog.app.local/` - an email testing tool (you can browse sent emails here)

### Init ###
* Run docker by calling make docker-up
* You are ready to go

### Executable ###
* Command:
```
make symfony CMD="consumer:rabbit --run-time=15"
```
* In web browser:
  * [Publish + declare queue using Bunny](http://app.local/publish-bunny)
  * [Publish + declare queue using Native](http://app.local/publish-native)
  * [Publish](http://app.local/publish)
  * [Consume Single Message](http://app.local/consume-single-message)
  * [Death Letter Queue init](http://app.local/death-letter-init)
  * [Retry message](http://app.local/publish-to-death-letter)
  * [Validate message](http://app.local/validate-message)

## Basic Information ##

### Exchange types ###
* [Exchange Types](https://www.rabbitmq.com/tutorials/amqp-concepts.html)

### Dead Letter Exchanges ###
* [Death Letter Exchanges](https://www.rabbitmq.com/dlx.html)

### Validation ###

#### Validation message format ####
* [JSON RPC 2.0](https://en.wikipedia.org/wiki/JSON-RPC#Version_2.0)

#### JSON Schema ####
* [Json Schema Specification](https://json-schema.org/)
* [Json Schema Online Validator](https://www.jsonschemavalidator.net/)
* [Json Schema Validator library](https://packagist.org/packages/justinrainbow/json-schema)

##### Message: #####
```json
{"jsonrpc": "2.0", "method": "CreatedProduct", "params": {"name": "Buty", "createdAt": "2018-08-28"}, "id": "123e4567-e89b-12d3-a456-426655440000"}
```

##### Schema Example #####
```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "$id": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "properties": {
    "jsonrpc": {
      "$id": "/properties/jsonrpc",
      "type": "string",
      "title": "num standard",
      "examples": [
        "2.0"
      ],
      "enum": [
        "2.0"
      ]
    },
    "method": {
      "$id": "/properties/method",
      "type": "string",
      "title": "Method name/Event name",
      "example": "CreateProject",
      "enum": [
        "CreatedProduct",
        "SoldProduct"
      ]
    },
    "id": {
      "$id": "/properties/id",
      "type": "string",
      "pattern": "^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$",
      "example": "123e4567-e89b-12d3-a456-426655440000"
    },
    "params": {
      "$id": "/properties/params",
      "type": "object",
      "properties": {
        "name": {
          "$id": "/properties/params",
          "type": "string",
          "example": "But"
        },
        "createdAt": {
          "$id": "/properties/params/createdAt",
          "type": "string",
          "format": "date",
          "example": "2022-06-09"
        }
      },
      "required": [
        "name",
        "createdAt"
      ]
    }
  },
  "required": [
    "jsonrpc",
    "method",
    "params",
    "id"
  ]
}
```

Example: [JSON online validation example](https://www.jsonschemavalidator.net/s/fsuICVan)