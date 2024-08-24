# My Laravel Project API Documentation

## Overview
This API allows users to interact with the system, retrieve data, and perform various operations.

## Base URL

[https://yourdomain.com/api]: https://yourdomain.com/api


## Authentication

All API endpoints require an API token. Include the token in the `Authorization` header:

Authorization: Bearer {your_api_token}


## Endpoints

### 1. **Get All Autobots**
Retrieve a list of all autobots.

**URL:** `/api/autobots`

**Method:** `GET`

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Optimus Prime",
      "type": "Leader",
      ...
    },
    {
      "id": 2,
      "name": "Bumblebee",
      "type": "Scout",
      ...
    }
  ]
}

