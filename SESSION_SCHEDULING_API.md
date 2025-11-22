# Session Scheduling API Documentation

This document describes the session scheduling system for the MindfulMe psychological consultation application.

## Overview

The session scheduling system allows:
- Psychologists to set their availability and manage time slots
- Users to book counseling sessions with psychologists
- Different types of consultation sessions (individual, couples, family, group)
- Session management (cancel, reschedule, status updates)

## Authentication

All endpoints require authentication using Laravel Sanctum tokens. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

## Session Types

### Get Available Session Types
```http
GET /api/session-types
```

**Response:**
```json
{
  "status": "success",
  "message": "Session types retrieved successfully",
  "data": [
    {
      "session_type_id": "uuid",
      "name": "Individual Counseling",
      "description": "One-on-one counseling session...",
      "duration_minutes": 60,
      "price": 250000.00,
      "consultation_type": "individual",
      "color": "#3B82F6",
      "max_participants": 1,
      "requirements": "No specific requirements.",
      "is_active": true
    }
  ]
}
```

### Get Session Types by Consultation Type
```http
GET /api/session-types/consultation-type/{type}
```

**Parameters:**
- `type`: `individual`, `couples`, `family`, or `group`

## Psychologist Availability

### Get Available Time Slots
```http
GET /api/psychologist-availability/available-slots?psychologist_id={uuid}&date={YYYY-MM-DD}&duration_minutes={minutes}
```

**Parameters:**
- `psychologist_id` (required): UUID of the psychologist
- `date` (required): Date to check availability
- `duration_minutes` (required): Session duration in minutes

**Response:**
```json
{
  "status": "success",
  "message": "Available time slots retrieved successfully",
  "data": [
    {
      "start_time": "09:00",
      "end_time": "10:00",
      "available": true
    },
    {
      "start_time": "10:30",
      "end_time": "11:30",
      "available": true
    }
  ]
}
```

### Check Time Slot Availability
```http
GET /api/psychologist-availability/check-availability?psychologist_id={uuid}&date={YYYY-MM-DD}&start_time={HH:mm}&end_time={HH:mm}
```

### Manage Psychologist Availability (Psychologists Only)

#### Create Availability
```http
POST /api/psychologist-availability
```

**Request Body:**
```json
{
  "day_of_week": "monday",
  "start_time": "09:00",
  "end_time": "17:00",
  "is_available": true,
  "break_periods": [
    {
      "start": "12:00",
      "end": "13:00"
    }
  ],
  "effective_from": "2024-01-01",
  "effective_to": "2024-12-31",
  "notes": "Regular working hours"
}
```

#### Get My Availability
```http
GET /api/psychologist-availability/my
```

#### Update Availability
```http
PUT /api/psychologist-availability/{availability_id}
```

#### Delete Availability
```http
DELETE /api/psychologist-availability/{availability_id}
```

## Session Management

### Book a Session
```http
POST /api/sessions/book
```

**Request Body:**
```json
{
  "psychologist_id": "psychologist_uuid",
  "session_type_id": "session_type_uuid",
  "start_time": "2024-01-15T14:00:00Z",
  "session_title": "Anxiety Counseling Session",
  "session_description": "Help with anxiety management",
  "user_notes": "I've been experiencing anxiety for 6 months",
  "complaint_id": "complaint_uuid", // Optional - link to existing complaint
  "participants": [ // Optional - for group sessions
    {
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890"
    }
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Session booked successfully",
  "data": {
    "appointment_id": "uuid",
    "psychologist_id": "uuid",
    "user_id": "uuid",
    "session_type_id": "uuid",
    "start_time": "2024-01-15T14:00:00Z",
    "end_time": "2024-01-15T15:00:00Z",
    "status": "scheduled",
    "session_title": "Anxiety Counseling Session",
    "price": 250000.00,
    "meeting_link": "https://meet.jit.si/MindfulMe-ABCDEFGHIJ",
    "psychologist": {
      "uuid": "uuid",
      "name": "Dr. Jane Smith",
      "email": "jane@example.com"
    },
    "sessionType": {
      "name": "Individual Counseling",
      "duration_minutes": 60
    }
  }
}
```

### Get My Sessions (Users)
```http
GET /api/sessions/my?status=scheduled&start_date=2024-01-01&end_date=2024-01-31&per_page=15
```

**Query Parameters:**
- `status` (optional): `scheduled`, `completed`, or `canceled`
- `start_date` (optional): Filter sessions from this date
- `end_date` (optional): Filter sessions until this date
- `per_page` (optional): Number of results per page (default: 15)

### Get Upcoming Sessions
```http
GET /api/sessions/upcoming
```

### Get Session Details
```http
GET /api/sessions/{session_id}
```

### Update Session Status
```http
PUT /api/sessions/{session_id}/status
```

**Request Body:**
```json
{
  "status": "completed",
  "psychologist_notes": "Session went well. Patient showed good progress.",
  "cancellation_reason": "Patient requested cancellation due to emergency"
}
```

**Status Options:**
- `scheduled` - Session is scheduled
- `completed` - Session was completed successfully
- `canceled` - Session was canceled

### Reschedule Session
```http
POST /api/sessions/{session_id}/reschedule
```

**Request Body:**
```json
{
  "new_start_time": "2024-01-16T14:00:00Z",
  "reason": "Patient requested reschedule due to conflict"
}
```

## Psychologist Session Management

### Get Psychologist Sessions
```http
GET /api/sessions/psychologist?status=scheduled&per_page=15
```

**Note:** This endpoint is available for psychologists and superadmins.

## Error Responses

All endpoints return consistent error responses:

```json
{
  "status": "error",
  "message": "Error description",
  "code": 400
}
```

**Common HTTP Status Codes:**
- `200` - Success
- `201` - Created successfully
- `400` - Bad request (validation error, business logic error)
- `403` - Forbidden (insufficient permissions)
- `404` - Not found
- `500` - Internal server error

## Business Rules

1. **Cancellation Policy**: Sessions can be canceled up to 24 hours before the start time
2. **Rescheduling**: Sessions can be rescheduled up to 24 hours before the start time
3. **Availability**: Psychologists can only book sessions during their defined available hours
4. **Session Completion**: Only psychologists can mark sessions as completed
5. **Meeting Links**: Automatic generation of unique video meeting links for each session

## Integration Points

- **User System**: Integrates with existing user roles (USER, PSYCHOLOGIST, SUPERADMIN)
- **Complaint System**: Sessions can be linked to existing psychological complaints
- **Payment System**: Session pricing and payment status tracking
- **Bundle Packages**: Can be integrated with existing credit/point system

## Example Workflow

1. **Psychologist sets availability**: psychologist creates weekly time slots
2. **User browses available time slots**: user checks psychologist availability for specific dates
3. **User books session**: user selects time slot and session type
4. **Automatic meeting link generation**: system generates unique video meeting link
5. **Session management**: users can view, cancel, or reschedule sessions
6. **Psychologist manages sessions**: psychologists can view, complete, or cancel sessions

## Security Features

- Role-based access control
- User ownership validation (users can only access their own sessions)
- Psychologist authorization (psychologists can only manage their assigned sessions)
- Administrative oversight for system management