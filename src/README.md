
## API Reference
##### current version is `v1`, start all the API routes with ``/api/v1/``
#####  All Requests except for GET should provide a valid csrf_token by passing parameter name `_token` and value `$token`
#### Register User

```http
  POST /register
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `name` | `string` | **Required**. |
| `email` | `email` | **Required**. unique email |
| `password` | `string` | **Required**. min:8 characters |
| `password_confirmation` | `string` | **Required**.  |
#### Login User

```http
  POST /login
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `email` | `email` | **Required**. unique email |
| `password` | `string` | **Required**. min:8 characters |

#### Logout User

```http
  POST /logout
```

| Header| Type     | Example |
| :-------- | :------- | :------------------------- |
| `Authorization` | `string` | \`Bearer $authToken\` |

#### Refresh JWT Token

```http
  POST /token/refresh
```
| Header| Type     | Example |
| :-------- | :------- | :------------------------- |
| `Authorization` | `string` | \`Bearer $authToken\` |



