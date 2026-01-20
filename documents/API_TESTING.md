# API Testing Documentation

**Base URL:** `http://hqms.test/api/v1`
**Last Tested:** 2026-01-20
**Test Results:** 45 passed (223 assertions)

---

## How We Know the API Works

### 1. Automated Tests (Pest PHP)

The API is tested using **Pest PHP** feature tests located in:
- `tests/Feature/Api/AuthTest.php` - Authentication endpoints
- `tests/Feature/Api/AppointmentTest.php` - Appointment & consultation endpoints

**Run tests:**
```bash
# Run all API tests
php artisan test tests/Feature/Api

# Run with compact output
php artisan test tests/Feature/Api --compact

# Run specific test file
php artisan test tests/Feature/Api/AuthTest.php
```

### 2. What the Tests Verify

Each test simulates real HTTP requests and verifies:
- Correct HTTP status codes (200, 201, 401, 403, 404, 422)
- JSON response structure
- Database changes (user created, token stored, etc.)
- Authorization (users can't access other users' data)
- Validation rules (required fields, unique constraints)

---

## Manual Testing with cURL

### Prerequisites
1. Start the server: `php artisan serve` or use Laragon/Herd
2. Seed the database: `php artisan migrate:fresh --seed`

---

## Authentication Endpoints

### 1. Register a New Patient

```bash
curl -X POST http://hqms.test/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "first_name": "Juan",
    "last_name": "Dela Cruz",
    "phone": "09171234567"
  }'
```

**Expected Response (201 Created):**
```json
{
  "message": "Registration successful.",
  "user": {
    "id": 1,
    "email": "juan@example.com",
    "roles": ["patient"],
    "personal_information": {
      "first_name": "Juan",
      "last_name": "Dela Cruz",
      "phone": "09171234567"
    }
  },
  "token": "1|abc123...",
  "token_type": "Bearer"
}
```

### 2. Login

```bash
curl -X POST http://hqms.test/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "Password123!"
  }'
```

**Expected Response (200 OK):**
```json
{
  "message": "Login successful.",
  "user": { ... },
  "token": "2|xyz789...",
  "token_type": "Bearer"
}
```

**Save the token for subsequent requests!**

### 3. Get Current User

```bash
curl -X GET http://hqms.test/api/v1/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 4. Logout

```bash
curl -X POST http://hqms.test/api/v1/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 5. Logout from All Devices

```bash
curl -X POST http://hqms.test/api/v1/logout-all \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Consultation & Availability Endpoints

### 6. Get Consultation Types

```bash
curl -X GET http://hqms.test/api/v1/consultation-types \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**With specific date:**
```bash
curl -X GET "http://hqms.test/api/v1/consultation-types?date=2026-01-25" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 7. Get Doctor Availability

```bash
curl -X GET "http://hqms.test/api/v1/doctors/availability?consultation_type_id=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Appointment Endpoints

### 8. Create Appointment

```bash
curl -X POST http://hqms.test/api/v1/appointments \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "consultation_type_id": 1,
    "appointment_date": "2026-01-25",
    "chief_complaints": "Regular prenatal checkup"
  }'
```

**Expected Response (201 Created):**
```json
{
  "message": "Appointment request submitted successfully. Please wait for confirmation.",
  "appointment": {
    "id": 1,
    "appointment_date": "2026-01-25",
    "status": "pending",
    "source": "online",
    "consultation_type": {
      "id": 1,
      "name": "OB-GYNE",
      "code": "ob"
    }
  }
}
```

### 9. Get My Appointments

```bash
# All appointments
curl -X GET http://hqms.test/api/v1/appointments/my \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Filter by status
curl -X GET "http://hqms.test/api/v1/appointments/my?status=pending,approved" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Upcoming only
curl -X GET "http://hqms.test/api/v1/appointments/my?upcoming=true" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 10. Get Appointment Details

```bash
curl -X GET http://hqms.test/api/v1/appointments/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 11. Cancel Appointment

```bash
curl -X PUT http://hqms.test/api/v1/appointments/1/cancel \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "reason": "Cannot attend due to emergency"
  }'
```

---

## Testing with Postman/Insomnia

### Setup Collection

1. **Create Environment Variables:**
   - `base_url`: `http://hqms.test/api/v1`
   - `token`: (empty, will be set after login)

2. **Set Default Headers:**
   - `Accept`: `application/json`
   - `Content-Type`: `application/json`

3. **Authorization:**
   - Type: Bearer Token
   - Token: `{{token}}`

### Workflow

1. **Register** → Save token from response
2. **Login** → Save token from response
3. **Get Consultation Types** → Note the IDs
4. **Create Appointment** → Use consultation_type_id from step 3
5. **Get My Appointments** → Verify appointment appears
6. **Cancel Appointment** → Test cancellation flow

---

## Test Results Summary (2026-01-20)

### AuthTest.php (19 tests)
| Endpoint | Test | Status |
|----------|------|--------|
| POST /register | registers with valid data | ✅ |
| POST /register | registers with all optional fields | ✅ |
| POST /register | fails with missing required fields | ✅ |
| POST /register | fails with invalid email | ✅ |
| POST /register | fails with duplicate email | ✅ |
| POST /register | fails with duplicate phone | ✅ |
| POST /register | fails with password mismatch | ✅ |
| POST /login | logs in with valid credentials | ✅ |
| POST /login | logs in with custom device name | ✅ |
| POST /login | fails with invalid credentials | ✅ |
| POST /login | fails with non-existent email | ✅ |
| POST /login | fails for deactivated user | ✅ |
| POST /login | fails with missing fields | ✅ |
| POST /logout | logs out authenticated user | ✅ |
| POST /logout | fails without authentication | ✅ |
| POST /logout-all | logs out from all devices | ✅ |
| GET /user | returns authenticated user data | ✅ |
| GET /user | fails without authentication | ✅ |
| GET /user | fails with invalid token | ✅ |

### AppointmentTest.php (26 tests)
| Endpoint | Test | Status |
|----------|------|--------|
| GET /consultation-types | returns all active types | ✅ |
| GET /consultation-types | returns availability for date | ✅ |
| GET /consultation-types | shows reduced availability | ✅ |
| GET /consultation-types | requires authentication | ✅ |
| GET /doctors/availability | returns doctor availability | ✅ |
| GET /doctors/availability | requires consultation_type_id | ✅ |
| GET /doctors/availability | validates type exists | ✅ |
| POST /appointments | creates with valid data | ✅ |
| POST /appointments | creates for today | ✅ |
| POST /appointments | fails with missing fields | ✅ |
| POST /appointments | fails with invalid type | ✅ |
| POST /appointments | fails with past date | ✅ |
| POST /appointments | prevents duplicate | ✅ |
| POST /appointments | allows different types same date | ✅ |
| GET /appointments/my | returns user appointments | ✅ |
| GET /appointments/my | filters by status | ✅ |
| GET /appointments/my | filters upcoming only | ✅ |
| GET /appointments/my | doesn't return others' data | ✅ |
| GET /appointments/{id} | returns appointment details | ✅ |
| GET /appointments/{id} | returns 403 for other user | ✅ |
| GET /appointments/{id} | returns 404 for non-existent | ✅ |
| PUT /appointments/{id}/cancel | cancels pending | ✅ |
| PUT /appointments/{id}/cancel | cancels approved | ✅ |
| PUT /appointments/{id}/cancel | fails for completed | ✅ |
| PUT /appointments/{id}/cancel | fails for past | ✅ |
| PUT /appointments/{id}/cancel | returns 403 for other user | ✅ |

---

## Error Response Format

All validation errors return:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message here"]
  }
}
```

Business logic errors return:
```json
{
  "error": "error_code",
  "message": "Human readable message"
}
```

---

## Quick Test Script

Save as `test-api.sh` and run:

```bash
#!/bin/bash
BASE_URL="http://hqms.test/api/v1"

echo "=== Testing API ==="

# Register
echo -e "\n1. Register..."
RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test'$(date +%s)'@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "first_name": "Test",
    "last_name": "User",
    "phone": "0917'$(date +%s | tail -c 8)'"
  }')
TOKEN=$(echo $RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
echo "Token: ${TOKEN:0:20}..."

# Get User
echo -e "\n2. Get User..."
curl -s -X GET "$BASE_URL/user" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | head -c 100
echo "..."

# Get Consultation Types
echo -e "\n3. Get Consultation Types..."
curl -s -X GET "$BASE_URL/consultation-types" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | head -c 100
echo "..."

# Create Appointment
echo -e "\n4. Create Appointment..."
curl -s -X POST "$BASE_URL/appointments" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "consultation_type_id": 1,
    "appointment_date": "'$(date -d "+3 days" +%Y-%m-%d)'"
  }' | head -c 150
echo "..."

echo -e "\n\n=== All tests completed ==="
```

---

*Document generated from test run on 2026-01-20*
