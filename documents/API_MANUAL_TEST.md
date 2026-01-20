# API Manual Test Results

**Date:** 2026-01-20 09:48 AM
**Base URL:** http://hqms.test/api/v1
**Test Account:** patient@hqms.test / password

---

## Test Summary

| # | Endpoint | Method | Status |
|---|----------|--------|--------|
| 1 | /api/v1/login | POST | ✅ 200 |
| 2 | /api/v1/user | GET | ✅ 200 |
| 3 | /api/v1/consultation-types | GET | ✅ 200 |
| 4 | /api/v1/doctors/availability | GET | ✅ 200 |
| 5 | /api/v1/appointments | POST | ✅ 201 |
| 6 | /api/v1/appointments/my | GET | ✅ 200 |
| 7 | /api/v1/appointments/{id} | GET | ✅ 200 |
| 8 | /api/v1/appointments/{id}/cancel | PUT | ✅ 200 |
| 9 | /api/v1/medical-records/my | GET | ✅ 200 |
| 10 | /api/v1/medical-records/{id} | GET | ✅ 200 |
| 11 | /api/v1/prescriptions/my | GET | ✅ 200 |
| 12 | /api/v1/logout | POST | ✅ 200 |
| 13 | Token invalidated after logout | GET | ✅ 401 |

**All 12 API endpoints tested and working!**

---

## 1. POST /api/v1/login ✅

**Request:**
```bash
curl -X POST http://hqms.test/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"patient@hqms.test","password":"password"}'
```

**Response (200 OK):**
```json
{
  "message": "Login successful.",
  "user": {
    "id": 9,
    "email": "patient@hqms.test",
    "is_active": 1,
    "roles": ["patient"],
    "personal_information": {
      "first_name": "Maria",
      "middle_name": "Isabel",
      "last_name": "Gonzales",
      "full_name": "Maria Isabel Gonzales",
      "phone": "09171111111",
      "gender": "female"
    }
  },
  "token": "8|kote13YNSxkMP1fwwejVkeKQkMK0almXEajankoEd95558d0",
  "token_type": "Bearer"
}
```

---

## 2. GET /api/v1/user ✅

**Request:**
```bash
curl -X GET http://hqms.test/api/v1/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "user": {
    "id": 9,
    "email": "patient@hqms.test",
    "is_active": 1,
    "roles": ["patient"],
    "personal_information": {
      "first_name": "Maria",
      "middle_name": "Isabel",
      "last_name": "Gonzales",
      "full_name": "Maria Isabel Gonzales",
      "phone": "09171111111",
      "date_of_birth": "1995-06-15",
      "gender": "female",
      "marital_status": "married",
      "province": "Sultan Kudarat",
      "municipality": "Tacurong City",
      "barangay": "Poblacion",
      "street": "123 Bonifacio St.",
      "full_address": "123 Bonifacio St., Poblacion, Tacurong City, Sultan Kudarat",
      "occupation": "Teacher",
      "emergency_contact_name": "Jose Gonzales",
      "emergency_contact_phone": "09171111112"
    }
  }
}
```

---

## 3. GET /api/v1/consultation-types ✅

**Request:**
```bash
curl -X GET http://hqms.test/api/v1/consultation-types \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "consultation_types": [
    {
      "id": 1,
      "name": "Obstetrics",
      "code": "ob",
      "description": "Pregnancy and maternal care",
      "operating_hours": {"start": "08:00", "end": "17:00"},
      "average_duration_minutes": 30,
      "max_daily_patients": 40,
      "booked_count": 0,
      "available_slots": 40,
      "is_available": true,
      "query_date": "2026-01-20",
      "is_active": true
    },
    {
      "id": 2,
      "name": "Pediatrics",
      "code": "pedia",
      "description": "Children healthcare",
      "operating_hours": {"start": "08:00", "end": "15:00"},
      "average_duration_minutes": 25,
      "max_daily_patients": 35,
      "booked_count": 0,
      "available_slots": 35,
      "is_available": true,
      "query_date": "2026-01-20",
      "is_active": true
    },
    {
      "id": 3,
      "name": "General Medicine",
      "code": "general",
      "description": "General medical consultation",
      "operating_hours": {"start": "09:00", "end": "18:00"},
      "average_duration_minutes": 20,
      "max_daily_patients": 50,
      "booked_count": 0,
      "available_slots": 50,
      "is_available": true,
      "query_date": "2026-01-20",
      "is_active": true
    }
  ]
}
```

---

## 4. GET /api/v1/doctors/availability ✅

**Request:**
```bash
curl -X GET "http://hqms.test/api/v1/doctors/availability?consultation_type_id=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "consultation_type": {
    "id": 1,
    "name": "Obstetrics",
    "code": "ob"
  },
  "date": "2026-01-20",
  "operating_hours": {"start": "08:00", "end": "17:00"},
  "doctors": [
    {"id": 2, "name": "Maria Cruz Santos"},
    {"id": 5, "name": "Carlos Antonio Mendoza"}
  ],
  "schedules": [],
  "message": "No specific schedules found. General operating hours apply."
}
```

---

## 5. POST /api/v1/appointments ✅

**Request:**
```bash
curl -X POST http://hqms.test/api/v1/appointments \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "consultation_type_id": 2,
    "appointment_date": "2026-01-25",
    "chief_complaints": "Child fever and cough"
  }'
```

**Response (201 Created):**
```json
{
  "message": "Appointment request submitted successfully. Please wait for confirmation.",
  "appointment": {
    "id": 2,
    "appointment_date": "2026-01-25",
    "chief_complaints": "Child fever and cough",
    "status": "pending",
    "source": "online",
    "consultation_type": {
      "id": 2,
      "name": "Pediatrics",
      "code": "pedia"
    }
  }
}
```

---

## 6. GET /api/v1/appointments/my ✅

**Request:**
```bash
curl -X GET http://hqms.test/api/v1/appointments/my \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "appointments": [
    {
      "id": 2,
      "appointment_date": "2026-01-25",
      "chief_complaints": "Child fever and cough",
      "status": "pending",
      "source": "online",
      "consultation_type": {"id": 2, "name": "Pediatrics", "code": "pedia"}
    },
    {
      "id": 1,
      "appointment_date": "2026-01-23",
      "chief_complaints": "Regular prenatal checkup",
      "status": "cancelled",
      "source": "online",
      "consultation_type": {"id": 1, "name": "Obstetrics", "code": "ob"},
      "cancellation_reason": "Testing API cancellation"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2
  }
}
```

---

## 7. GET /api/v1/appointments/{id} ✅

**Request:**
```bash
curl -X GET http://hqms.test/api/v1/appointments/2 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "appointment": {
    "id": 2,
    "appointment_date": "2026-01-25",
    "chief_complaints": "Child fever and cough",
    "status": "pending",
    "source": "online",
    "consultation_type": {
      "id": 2,
      "name": "Pediatrics",
      "code": "pedia"
    },
    "has_medical_record": false
  }
}
```

---

## 8. PUT /api/v1/appointments/{id}/cancel ✅

**Request:**
```bash
curl -X PUT http://hqms.test/api/v1/appointments/2/cancel \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"reason": "Schedule conflict - need to reschedule"}'
```

**Response (200 OK):**
```json
{
  "message": "Appointment cancelled successfully.",
  "appointment": {
    "id": 2,
    "appointment_date": "2026-01-25",
    "chief_complaints": "Child fever and cough",
    "status": "cancelled",
    "source": "online",
    "cancellation_reason": "Schedule conflict - need to reschedule",
    "consultation_type": {
      "id": 2,
      "name": "Pediatrics",
      "code": "pedia"
    }
  }
}
```

---

## 9. GET /api/v1/medical-records/my ✅

**Request:**
```bash
curl -X GET http://hqms.test/api/v1/medical-records/my \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "medical_records": [
    {
      "id": 1,
      "patient": {
        "first_name": "Maria",
        "last_name": "Gonzales",
        "full_name": "Maria Isabel Gonzales",
        "date_of_birth": "1995-06-15",
        "gender": "female"
      },
      "visit": {
        "date": "2026-01-15",
        "type": "Follow-up"
      },
      "vital_signs": {
        "temperature": "36.5",
        "blood_pressure": "110/70",
        "cardiac_rate": "80",
        "respiratory_rate": "18",
        "weight": "65"
      },
      "examination": {
        "diagnosis": "Pregnancy Uterine 28 weeks AOG",
        "plan": "Continue prenatal vitamins, follow-up in 2 weeks"
      },
      "status": "completed",
      "consultation_type": {
        "id": 1,
        "name": "Obstetrics",
        "code": "ob"
      },
      "prescriptions": [
        {
          "id": 1,
          "medication_name": "Ferrous Sulfate",
          "dosage": "325mg",
          "frequency": "Once daily",
          "duration": "30 days",
          "instructions": "Take with meals"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2
  }
}
```

**Query Parameters:**
- `consultation_type_id` - Filter by consultation type
- `from_date` - Filter from date (Y-m-d)
- `to_date` - Filter to date (Y-m-d)
- `per_page` - Results per page (default: 15)

---

## 10. GET /api/v1/medical-records/{id} ✅

**Request:**
```bash
curl -X GET http://hqms.test/api/v1/medical-records/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "medical_record": {
    "id": 1,
    "patient": {
      "first_name": "Maria",
      "middle_name": "Isabel",
      "last_name": "Gonzales",
      "full_name": "Maria Isabel Gonzales",
      "date_of_birth": "1995-06-15",
      "age": 30,
      "gender": "female",
      "marital_status": "married",
      "blood_type": "O+",
      "allergies": null,
      "chronic_conditions": null,
      "contact_number": "09171111111",
      "occupation": "Teacher",
      "address": {
        "street": "123 Bonifacio St.",
        "barangay": "Poblacion",
        "municipality": "Tacurong City",
        "province": "Sultan Kudarat",
        "full_address": "123 Bonifacio St., Poblacion, Tacurong City, Sultan Kudarat"
      },
      "emergency_contact": {
        "name": "Jose Gonzales",
        "phone": "09171111112"
      }
    },
    "visit": {
      "date": "2026-01-15",
      "type": "Follow-up",
      "service_type": "outpatient"
    },
    "chief_complaints": {
      "initial": "Regular prenatal checkup",
      "updated": null,
      "effective": "Regular prenatal checkup"
    },
    "vital_signs": {
      "temperature": "36.5",
      "blood_pressure": "110/70",
      "cardiac_rate": "80",
      "respiratory_rate": "18",
      "weight": "65",
      "height": "160",
      "fetal_heart_tone": "140",
      "fundal_height": "28",
      "last_menstrual_period": "2025-06-20"
    },
    "examination": {
      "pertinent_hpi_pe": "G2P1 at 28 weeks AOG, no bleeding, good fetal movement",
      "diagnosis": "Pregnancy Uterine 28 weeks AOG",
      "plan": "Continue prenatal vitamins, follow-up in 2 weeks",
      "procedures_done": null,
      "prescription_notes": "Vitamins as prescribed"
    },
    "status": "completed",
    "consultation_type": {
      "id": 1,
      "name": "Obstetrics",
      "code": "ob"
    },
    "doctor": {
      "id": 2,
      "name": "Maria Cruz Santos"
    },
    "prescriptions": [
      {
        "id": 1,
        "medication_name": "Ferrous Sulfate",
        "dosage": "325mg",
        "frequency": "Once daily",
        "duration": "30 days",
        "instructions": "Take with meals",
        "quantity": 30,
        "is_hospital_drug": false,
        "prescribed_by": {
          "id": 2,
          "name": "Maria Cruz Santos"
        }
      },
      {
        "id": 2,
        "medication_name": "Folic Acid",
        "dosage": "5mg",
        "frequency": "Once daily",
        "duration": "30 days",
        "instructions": "Take in the morning",
        "quantity": 30,
        "is_hospital_drug": false,
        "prescribed_by": {
          "id": 2,
          "name": "Maria Cruz Santos"
        }
      }
    ]
  }
}
```

**Authorization:** Returns 403 if trying to access another user's record.

---

## 11. GET /api/v1/prescriptions/my ✅

**Request:**
```bash
curl -X GET http://hqms.test/api/v1/prescriptions/my \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "prescriptions": [
    {
      "id": 1,
      "medication_name": "Ferrous Sulfate",
      "dosage": "325mg",
      "frequency": "Once daily",
      "duration": "30 days",
      "instructions": "Take with meals",
      "quantity": 30,
      "is_hospital_drug": false,
      "prescribed_by": {
        "id": 2,
        "name": "Maria Cruz Santos"
      },
      "visit_date": "2026-01-15",
      "consultation_type": "Obstetrics"
    },
    {
      "id": 2,
      "medication_name": "Folic Acid",
      "dosage": "5mg",
      "frequency": "Once daily",
      "duration": "30 days",
      "instructions": "Take in the morning",
      "quantity": 30,
      "is_hospital_drug": false,
      "prescribed_by": {
        "id": 2,
        "name": "Maria Cruz Santos"
      },
      "visit_date": "2026-01-15",
      "consultation_type": "Obstetrics"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2
  }
}
```

**Query Parameters:**
- `from_date` - Filter from date (Y-m-d)
- `to_date` - Filter to date (Y-m-d)
- `per_page` - Results per page (default: 15)

---

## 12. POST /api/v1/logout ✅

**Request:**
```bash
curl -X POST http://hqms.test/api/v1/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response (200 OK):**
```json
{
  "message": "Logged out successfully."
}
```

---

## 13. Verify Token Invalidated ✅

**Request (using old token after logout):**
```bash
curl -X GET http://hqms.test/api/v1/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $OLD_TOKEN"
```

**Response (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
}
```

---

## Available Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@hqms.test | password |
| Doctor (OB) | dr.santos@hqms.test | password |
| Doctor (Pedia) | dr.reyes@hqms.test | password |
| Doctor (General) | dr.garcia@hqms.test | password |
| Doctor (All) | doctor@hqms.test | password |
| Nurse | nurse@hqms.test | password |
| Nurse | nurse.lopez@hqms.test | password |
| Cashier | cashier@hqms.test | password |
| Patient | patient@hqms.test | password |
| Patient (Parent) | ana.parent@hqms.test | password |
| Patient | juan.patient@hqms.test | password |

---

## How to Reproduce

1. Start server with Laragon/Herd (domain: hqms.test)
2. Seed database: `php artisan db:seed`
3. Run curl commands above in order
4. Or run automated tests: `php artisan test tests/Feature/Api`

---

## Automated Test Results

```
Tests:    59 passed (357 assertions)
Duration: 2.62s

- AuthTest.php: 19 tests ✅
- AppointmentTest.php: 26 tests ✅
- MedicalRecordTest.php: 14 tests ✅
```

Run automated tests:
```bash
php artisan test tests/Feature/Api --compact
```
