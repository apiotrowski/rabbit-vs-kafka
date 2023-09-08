# Rabbit vs Kafka comparison example app #

This app was build as a pet project to compare RabbitMQ and Apache Kafka.

You can download it and use it both tools after you run docker-compose.

Furthermore, I created a presentation in which compare both technologies. 

If you want to ask me about Rabbit or Kafka, please contact me using email: [andrzej.piotrowski@stronawww.net](mailto:andrzej.piotrowski@stronawww.net).

### Presentation ###
* `./docs/MODIVO TECH MEETUP — Rabbit vs Kafka.pptx`

### Dependencies ###
* Docker CE (https://docs.docker.com/install/)
* Docker Compose (https://docs.docker.com/compose/install/)

#### Please add below entry to hosts file (on Linux it's `/etc/hosts`: ####
* `127.0.0.1 app.local rabbitmq.app.local mailhog.app.local`

### How to start ###
* Run docker by executing in cli `make docker-up`
* After you finish setup, you are ready to go.

#### URL ####
* `http://app.local/` - app homepage
* `http://rabbitmq.app.local/` - RabbitMQ management panel (login: guest, password: guest)
* `http://localhost:9094` - Kafka server
* `http://localhost:2181` - Zookeeper server

### Executable ###
* In web browser:
  * [Publish rabbit message via messenger](http://app.local/rabbit/publish-messenger)
  * [Publish kafka message via messenger](http://app.local/kafka/publish-messenger)

There are more actions in each Controllers. Open code and figure out by yourself. 

* In CLI:
  * `make symfony CMD="messenger:consume rabbit_async -vv"`
  * `make symfony CMD="messenger:consume kafka_consume -vv"`

## Basic information about Apache Kafka ##

Apache Kafka does not have any visual tool to manage Kafka servers. There is couple app in the market which you can install and use it.
Conduktor app is able to create Apache Kafka Server and Zookeeper itself, in other hand is allow to connect to existed broker from docker-compose.

Apache Kafka has a `Cli Command Tool` in which you are able to manage whole server.

* [https://kafka.apache.org/](https://kafka.apache.org/) - page of the project
* If you want to install it, use link: [Conductor app](https://www.conduktor.io/kafka/how-to-start-kafka-with-conduktor) - tool to manage Apache Kafka

## Basic Information about Rabbit ##

### Exchange types ###
* [Exchange Types](https://www.rabbitmq.com/tutorials/amqp-concepts.html)

### Dead Letter Exchanges ###
* [Death Letter Exchanges](https://www.rabbitmq.com/dlx.html)

#### JSON Schema ####
* [Json Schema Specification](https://json-schema.org/)
* [Json Schema Online Validator](https://www.jsonschemavalidator.net/)
* [Json Schema Validator library](https://packagist.org/packages/justinrainbow/json-schema)

##### Message: #####
```bash
curl -X POST http://app.local/validate \
   -H "Content-Type: application/json" \
   -d '{"jsonrpc": "2.0", "method": "NewProduct", "params": {"name": "Buty", "createdAt": "2018-08-28"}, "id": "123e4567-e89b-12d3-a456-426655440000"}'
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
        "NewProduct",
        "NewOrder"
      ]
    },
    "id": {
      "$id": "/properties/id",
      "type": "string",
      "pattern": "^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$",
      "example": "123e4567-e89b-12d3-a456-426655440000"
    },
    "params": {
      "oneOf": [
        {
          "$ref": "#/definitions/newProduct"
        },
        {
          "$ref": "#/definitions/newOrder"
        }
      ]
    }
  },
  "required": [
    "jsonrpc",
    "method",
    "params",
    "id"
  ],
  "definitions": {
    "newProduct": {
      "$id": "/definitions/newProduct",
      "type": "object",
      "properties": {
        "id": {
          "$id": "/definitions/newProduct/id",
          "type": "integer"
        },
        "name": {
          "$id": "/definitions/newProduct/name",
          "type": "string"
        },
        "quantity": {
          "$id": "/definitions/newProduct/quantity",
          "type": "number",
          "minimum": 0,
          "maximum": 100
        },
        "active": {
          "$id": "/definitions/newProduct/active",
          "type": "boolean"
        },
        "createdAt": {
          "$id": "/definitions/newProduct/createdAt",
          "type": "string",
          "format": "date",
          "example": "2022-06-09"
        }
      },
      "required": [
        "id",
        "name",
        "quantity",
        "active",
        "createdAt"
      ]
    },
    "newOrder": {
      "$id": "/definitions/newOrder/id",
      "type": "object",
      "properties": {
        "cartId": {
          "$id": "/definitions/newOrder/cartId",
          "type": "string",
          "pattern": "^[0-9(a-f|A-F)]{8}-[0-9(a-f|A-F)]{4}-4[0-9(a-f|A-F)]{3}-[89ab][0-9(a-f|A-F)]{3}-[0-9(a-f|A-F)]{12}$"
        },
        "createdAt": {
          "$id": "/definitions/newOrder/createdAt",
          "type": "string",
          "format": "date",
          "example": "2022-06-09"
        }
      },
      "required": [
        "cartId",
        "createdAt"
      ]
    }
  }
}
```

Example: [JSON online validation example](https://www.jsonschemavalidator.net/s/fsuICVan)