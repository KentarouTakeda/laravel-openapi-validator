@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp

# OpenAPI Validator

`kentaroutakeda/laravel-openapi-validator` is installed. Provides middleware for automatic request/response validation based on OpenAPI schema.

## Core Concepts

- Adding `OpenApiValidator::class` middleware to a route enables automatic request/response validation against the OpenAPI schema. Returns 400 for request errors, 500 for response errors.
- With response validation enabled (default), normal-case tests alone are sufficient. The middleware guarantees schema conformance, so error-case tests for schema-covered validations are unnecessary. When `skipResponseValidation` is enabled, write separate response validation tests.
- Request validations defined in the OpenAPI schema (type, required, format, etc.) are handled by the middleware, so duplicating them in FormRequest is unnecessary. For validations not covered by the schema (DB checks, authorization, business logic, etc.), use minimal FormRequest or controller logic per project conventions.

See the `openapi-validator` skill for detailed usage and code examples.
