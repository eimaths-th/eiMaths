# View Details Button สำหรับ Course Pending

## ฟีเจอร์ที่เพิ่มใหม่

เพิ่มปุ่ม **View Details** ในหน้า Course Pending Index (`/courses/pending/index`) เพื่อดูข้อมูลรายละเอียดทั้งหมดของ course pending แต่ละรายการ

## ไฟล์ที่แก้ไข

### 1. **app/Http/Controllers/CourseController.php**

#### เพิ่มปุ่ม View Details ใน DataTable Actions
```php
$dropdown .= '<li><button class="dropdown-item view-detail-btn" data-id="' . $query->id . '">View Details</button></li>';
```

#### เพิ่ม Method ใหม่: `getCoursePendingDetails($id)`
```php
public function getCoursePendingDetails($id)
{
    $coursePending = CoursePending::with('department')->find($id);
    
    if (!$coursePending) {
        return response()->json([
            'success' => false,
            'message' => 'Course pending not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $coursePending
    ]);
}
```

### 2. **routes/web2.php**
เพิ่ม route ใหม่:
```php
Route::get('/courses/pending/details/{id}', [CourseController::class, 'getCoursePendingDetails'])
    ->name('courses.pending.details');
```

### 3. **resources/views/course/courses_pending_index.blade.php**

#### เพิ่ม Modal สำหรับแสดงข้อมูล
Modal แสดงข้อมูลแบ่งเป็น 4 หมวดหมู่:
- **Parent Information**: ข้อมูลผู้ปกครอง
- **Student Information**: ข้อมูลนักเรียน (ใหม่)
- **Course Information**: ข้อมูลคอร์ส
- **Schedule Information**: ข้อมูลตารางเรียน
- **System Information**: ข้อมูลระบบ

#### เพิ่ม JavaScript Handler
```javascript
$(document).on('click', '.view-detail-btn', function() {
    var id = $(this).data('id');
    
    $.ajax({
        url: "{{ route('courses.pending.details', ':id') }}".replace(':id', id),
        method: 'GET',
        // ... handle response
    });
});
```

## การใช้งาน

### ในหน้า Course Pending Index
1. ไปที่ `http://127.0.0.1:8000/courses/pending/index`
2. คลิกปุ่ม **Actions** ในแถวใดๆ
3. เลือก **View Details** จาก dropdown menu
4. Modal จะแสดงข้อมูลรายละเอียดทั้งหมด

### ข้อมูลที่แสดงใน Modal

#### Parent Information
- Name (ชื่อผู้ปกครอง)
- Email (อีเมล)
- Phone (เบอร์โทร)
- Type Parent (ประเภทผู้ปกครอง: father/mother)
- Ref Code (รหัสอ้างอิง)

#### Student Information ⭐ **ข้อมูลใหม่**
- Student Name (ชื่อนักเรียน)
- Nickname (ชื่อเล่น)
- Grade (ระดับชั้น: K1, K2, P1-P6)
- Appointment Date (วันเวลานัดหมาย)

#### Course Information
- Course Name (ชื่อคอร์ส)
- Price (ราคา)

#### Schedule Information
- Department (สาขา)
- Day (วัน)
- Period (ช่วงเวลา)

#### System Information
- ID (รหัสระบบ)
- Status (สถานะ พร้อม badge สี)
- Created At (วันที่สร้าง)
- Updated At (วันที่แก้ไขล่าสุด)

### Status Badge Colors
- 🟡 **Pending** (status = 1) - Warning (สีเหลือง)
- 🟢 **Accepted** (status = 2) - Success (สีเขียว)
- 🔵 **Waiting for Payment** (status = 3) - Info (สีน้ำเงิน)
- 🟢 **Success** (status = 4) - Success (สีเขียว)
- 🔴 **Denied** (status = 0) - Danger (สีแดง)

## API Endpoint

### GET `/courses/pending/details/{id}`
**Description**: ดึงข้อมูลรายละเอียดของ course pending

**Parameters**:
- `id` (required): ID ของ course pending

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "telp": "0812345678",
        "appointment_date": "2025-11-14 14:30:00",
        "student_name": "สมชาย ใจดี",
        "student_nickname": "ชาย",
        "grade": "P1",
        "type_parent": "father",
        "course_name": "Math Course",
        "day": "Saturday",
        "period": "10:00-11:00",
        "price": "5000",
        "status": 1,
        "created_at": "2025-11-14 10:00:00",
        "updated_at": "2025-11-14 10:00:00",
        "department": {
            "id": 2,
            "name": "Bangkok Branch"
        }
    }
}
```

**Error Response**:
```json
{
    "success": false,
    "message": "Course pending not found"
}
```

## Features

### ✅ สิ่งที่ทำได้
- ดูข้อมูลรายละเอียดทั้งหมดของ course pending
- แสดงข้อมูลนักเรียนใหม่ (student_name, student_nickname, grade, appointment_date)
- แสดงสถานะด้วย badge สี
- แสดงชื่อสาขาจริงแทนที่จะเป็นแค่ ID
- Loading state ขณะโหลดข้อมูล
- Error handling สำหรับกรณีเกิดข้อผิดพลาด
- Responsive design

### ⚠️ หมายเหตุ
- ต้องมี relationship `department()` ใน CoursePending model
- ต้องมี Laravel Excel package สำหรับฟีเจอร์อื่นๆ
- ใช้ Bootstrap 5 สำหรับ modal และ styling

## ตัวอย่างการใช้งาน

```javascript
// เรียกใช้แบบ Manual
$('.view-detail-btn').trigger('click');

// ดึงข้อมูลด้วย AJAX โดยตรง
$.get('/courses/pending/details/123', function(response) {
    console.log(response.data);
});
```
