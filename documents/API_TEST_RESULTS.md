
  [30;42;1m PASS [39;49;22m[39m Tests\Feature\Api\AppointmentTest[39m
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/consultation-types[39m[90m → it returns all active consultation types with availability[39m[90m                 [39m [90m0.40s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/consultation-types[39m[90m → it returns availability for a specific date[39m[90m                                [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/consultation-types[39m[90m → it shows reduced availability when appointments exist[39m[90m                      [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/consultation-types[39m[90m → it requires authentication[39m[90m                                                 [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/doctors/availability[39m[90m → it returns doctor availability for a consultation type[39m[90m                   [39m [90m0.04s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/doctors/availability[39m[90m → it requires consultation_type_id parameter[39m[90m                               [39m [90m0.04s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/doctors/availability[39m[90m → it validates consultation_type_id exists[39m[90m                                 [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/appointments[39m[90m → it creates an appointment with valid data[39m[90m                                       [39m [90m0.04s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/appointments[39m[90m → it creates appointment for today[39m[90m                                                [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/appointments[39m[90m → it fails with missing required fields[39m[90m                                           [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/appointments[39m[90m → it fails with invalid consultation type[39m[90m                                         [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/appointments[39m[90m → it fails with past date[39m[90m                                                         [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/appointments[39m[90m → it prevents duplicate appointment for same type and date[39m[90m                        [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/appointments[39m[90m → it allows different consultation types on same date[39m[90m                             [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/appointments/my[39m[90m → it returns user appointments[39m[90m                                                  [39m [90m0.04s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/appointments/my[39m[90m → it filters by status[39m[90m                                                          [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/appointments/my[39m[90m → it filters upcoming only[39m[90m                                                      [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/appointments/my[39m[90m → it does not return other users appointments[39m[90m                                   [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/appointments/{id}[39m[90m → it returns appointment details[39m[90m                                              [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/appointments/{id}[39m[90m → it returns 403 for other users appointment[39m[90m                                  [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/appointments/{id}[39m[90m → it returns 404 for non-existent appointment[39m[90m                                 [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPUT /api/v1/appointments/{id}/cancel[39m[90m → it cancels a pending appointment[39m[90m                                     [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPUT /api/v1/appointments/{id}/cancel[39m[90m → it cancels an approved appointment[39m[90m                                   [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPUT /api/v1/appointments/{id}/cancel[39m[90m → it fails to cancel completed appointment[39m[90m                             [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPUT /api/v1/appointments/{id}/cancel[39m[90m → it fails to cancel past appointment[39m[90m                                  [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPUT /api/v1/appointments/{id}/cancel[39m[90m → it returns 403 for other users appointment[39m[90m                           [39m [90m0.03s[39m  

  [30;42;1m PASS [39;49;22m[39m Tests\Feature\Api\AuthTest[39m
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/register[39m[90m → it registers a new patient with valid data[39m[90m                                          [39m [90m0.05s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/register[39m[90m → it registers patient with all optional fields[39m[90m                                       [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/register[39m[90m → it fails registration with missing required fields[39m[90m                                  [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/register[39m[90m → it fails registration with invalid email[39m[90m                                            [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/register[39m[90m → it fails registration with duplicate email[39m[90m                                          [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/register[39m[90m → it fails registration with duplicate phone[39m[90m                                          [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/register[39m[90m → it fails registration with password mismatch[39m[90m                                        [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/login[39m[90m → it logs in with valid credentials[39m[90m                                                      [39m [90m0.04s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/login[39m[90m → it logs in with custom device name[39m[90m                                                     [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/login[39m[90m → it fails login with invalid credentials[39m[90m                                                [39m [90m0.23s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/login[39m[90m → it fails login with non-existent email[39m[90m                                                 [39m [90m0.22s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/login[39m[90m → it fails login for deactivated user[39m[90m                                                    [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/login[39m[90m → it fails login with missing fields[39m[90m                                                     [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/logout[39m[90m → it logs out authenticated user[39m[90m                                                        [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/logout[39m[90m → it fails logout without authentication[39m[90m                                                [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mPOST /api/v1/logout-all[39m[90m → it logs out from all devices[39m[90m                                                      [39m [90m0.02s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/user[39m[90m → it returns authenticated user data[39m[90m                                                       [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/user[39m[90m → it fails without authentication[39m[90m                                                          [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[37mGET /api/v1/user[39m[90m → it fails with invalid token[39m[90m                                                              [39m [90m0.02s[39m  

  [90mTests:[39m    [32;1m45 passed[39;22m[90m (223 assertions)[39m
  [90mDuration:[39m [39m2.23s[39m

