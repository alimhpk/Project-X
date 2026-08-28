# API authentication

`GET /api/destinations` requires a Laravel Sanctum personal access token that carries the `destinations:read` ability. The browser explorer at `/` and the health check at `/up` stay public and need no token.

## Before issuing tokens

This release adds the `personal_access_tokens` table. Run the migrations before issuing or using any token; until the table exists, every request that presents a token fails.

```bash
php artisan migrate
```

## Issue a token

Tokens are issued by an operator on the server. The user must already exist; create one through your normal provisioning process before issuing a token.

```bash
php artisan destination-token:issue operator@example.com
php artisan destination-token:issue operator@example.com --name=reporting --expires=30
```

Every token expires. The default lifetime is 90 days; `--expires` accepts a whole number of days from 1 to 365. Values outside that range, fractions and non numeric input are rejected and no token is created.

The command prints the plain token exactly once, in the form `<id>|<secret>`:

```text
Token destination-api issued for operator@example.com with the destinations:read ability.
It expires on <date> <time> UTC (90 days from now).
Copy this token now and store it securely. It will not be shown again.
<id>|<plain-token>
```

Copy it immediately into a secrets manager. The database only stores a SHA-256 hash, so a lost token cannot be recovered; revoke it and issue a new one instead. Token names are exact labels chosen by the operator and are unique per user: use the same spelling when revoking, and revoke an existing token before reusing its name.

## Revoke a token

```bash
php artisan destination-token:revoke operator@example.com
php artisan destination-token:revoke operator@example.com --name=reporting
```

Revocation takes effect immediately. The next request that presents the revoked token receives `401`.

## Rotate a token

Clients must rotate tokens before they expire; an expired token receives `401` exactly like a revoked one. Rotate safely, without downtime, in this order:

1. Issue a new token under a new name, for example `--name=reporting-2027`.
2. Update the client with the new token.
3. Verify the client succeeds with the new token.
4. Revoke the old token by its exact name.

## Call the API

Send the token in the `Authorization` header on every request:

```bash
TOKEN='<plain-token>'

curl --header "Accept: application/json" \
     --header "Authorization: Bearer $TOKEN" \
     "https://example.com/api/destinations?region=Asia&sort=annualVisitors&direction=desc"
```

| Status | Meaning |
| --- | --- |
| `200` | Paginated destinations in the documented `data`, `links` and `meta` structure |
| `401` | Missing, malformed, expired or revoked token |
| `403` | Token is valid but lacks the `destinations:read` ability |
| `422` | Invalid query parameters; the body lists each error |
| `429` | More than 60 requests per minute from one IP; wait for the `Retry-After` header |

All error responses are JSON.

## Handling tokens

* Treat a token like a password. Store it only in a secrets manager or environment variable on the client.
* Never commit a token, paste it into tickets or chat, or write it to logs.
* Issue one token per client or integration so a single token can be revoked without affecting others.
* Record the expiry date shown at issue time and schedule rotation ahead of it.

## Production checklist

* Run `php artisan migrate` as part of the release so the `personal_access_tokens` table exists before the protected route goes live.
* Serve the API over HTTPS only. Bearer tokens travel in plain headers and are exposed on any unencrypted hop.
* Keep `APP_DEBUG=false` so error responses contain only a message and never a stack trace.
* Review CORS for the deployment. Publish the configuration with `php artisan config:publish cors` and allow only the browser origins that must call the API; server to server clients need no CORS entry.
* Review trusted proxies when the application runs behind a load balancer or CDN. Configure `$middleware->trustProxies(...)` in `bootstrap/app.php` so rate limiting sees the real client IP and HTTPS detection is correct.
* Monitor the `personal_access_tokens` table, including `expires_at` and `last_used_at`, and revoke tokens that are no longer used.
