## Configuration

### Environment Variables

| Name | Default | Description |
| - | - | - |
| `OPENAPI_VALIDATOR_PROVIDER` | `laravel-openapi` | OpenAPI Schema provider used by default. |
| `OPENAPI_VALIDATOR_ERROR_ON_NO_PATH` | According to `APP_DEBUG` | Whether to respond error when the path corresponding to the request is not defined. |
| `OPENAPI_VALIDATOR_INCLUDE_REQ_ERROR_IN_RESPONSE` | `true` | Whether to include a request validation error pointer in the response. |
| `OPENAPI_VALIDATOR_INCLUDE_RES_ERROR_IN_RESPONSE` | According to `APP_DEBUG` | Whether to include a response validation error pointer in the response. |
| `OPENAPI_VALIDATOR_INCLUDE_TRACE_IN_RESPONSE` | According to `APP_DEBUG` | Whether to include a stack trace in the response. |
| `OPENAPI_VALIDATOR_COLLECTION_NAME` | `'default'` | The name of the collection to be used. |



