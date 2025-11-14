# การใช้งาน Course Pending ฟิลด์ใหม่

## ฟิลด์ที่เพิ่มใหม่

ได้เพิ่ม 4 ฟิลด์ใหม่ลงในตาราง `courses_pennding` เพื่อเก็บข้อมูลนักเรียนและการนัดหมาย:

### 1. **appointment_date** (DateTime)
- ประเภท: `dateTime` (nullable)
- ใช้สำหรับ: เก็บวันเวลานัดหมายเข้าเรียน
- ตัวอย่าง: `2025-11-14 14:30`

### 2. **student_name** (String)
- ประเภท: `string` (nullable, max 255)
- ใช้สำหรับ: ชื่อนักเรียน
- ตัวอย่าง: `สมชาย ใจดี`

### 3. **student_nickname** (String)
- ประเภท: `string` (nullable, max 255)
- ใช้สำหรับ: ชื่อเล่นของนักเรียน
- ตัวอย่าง: `ชาย`

### 4. **grade** (String)
- ประเภท: `string` (nullable, max 10)
- ใช้สำหรับ: ระดับชั้นของนักเรียน
- ตัวอย่าง: `K1`, `K2`, `P1`, `P2`, ..., `P6`

## ไฟล์ที่แก้ไข

### 1. **app/Http/Controllers/CourseController.php**
- เพิ่ม validation สำหรับฟิลด์ใหม่ใน `coursesPendingStore()` method
- ฟิลด์ใหม่ทั้งหมดเป็น required

```php
$validatedData = $request->validate([
    'appointment_date' => 'required|date_format:Y-m-d\TH:i',
    'student_name' => 'required|string|max:255',
    'student_nickname' => 'required|string|max:255',
    'grade' => 'required|string|max:10',
    // ... other fields
]);
```

### 2. **app/Models/CoursePending.php**
- เพิ่มฟิลด์ใหม่ใน `$fillable` array

```php
protected $fillable = [
    'ref',
    'name',
    'email',
    'telp',
    'appointment_date',
    'student_name',
    'student_nickname',
    'grade',
    // ... other fields
];
```

### 3. **database/migrations/2025_11_14_103437_add_new_fields_to_courses_pennding_table.php**
- Migration ใหม่ที่เพิ่มคอลัมน์ 4 คอลัมน์ลงในตาราง `courses_pennding`

## การใช้งาน

### ในฟอร์ม (form_modal.blade.php)
```html
<!-- Appointment Date -->
<div class="form-group">
    <label for="appointment_date">Appointment date</label>
    <input type="datetime-local" class="form-control" id="appointment_date" 
           name="appointment_date" required>
</div>

<!-- Student Name -->
<div class="form-group">
    <label for="student_name">Student Name</label>
    <input type="text" class="form-control" id="student_name" 
           name="student_name" required>
</div>

<!-- Student Nickname -->
<div class="form-group">
    <label for="student_nickname">Student Nick Name</label>
    <input type="text" class="form-control" id="student_nickname" 
           name="student_nickname" required>
</div>

<!-- Grade -->
<div class="form-group">
    <label for="grade">Grade</label>
    <select name="grade" id="grade" class="form-control" required>
        <option value="">Select grade</option>
        <option value="K1">K1</option>
        <option value="K2">K2</option>
        @for ($i = 1; $i <= 6; $i++)
            <option value="P{{ $i }}">P{{ $i }}</option>
        @endfor
    </select>
</div>
```

### ในฐานข้อมูล
```sql
-- ดูข้อมูลที่เก็บ
SELECT id, name, student_name, student_nickname, grade, appointment_date 
FROM courses_pennding 
WHERE student_name IS NOT NULL;
```

### ใน PHP/Laravel
```php
use App\Models\CoursePending;

// สร้างข้อมูลใหม่
$coursePending = CoursePending::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'telp' => '0812345678',
    'appointment_date' => '2025-11-14 14:30',
    'student_name' => 'สมชาย ใจดี',
    'student_nickname' => 'ชาย',
    'grade' => 'P1',
    'course_id' => 1,
    'department_id' => 2,
    'day' => 'Saturday',
    'period' => '10:00-11:00',
    'price' => 5000,
    'status' => 1
]);

// ดึงข้อมูล
$pending = CoursePending::find(1);
echo $pending->student_name;        // สมชาย ใจดี
echo $pending->appointment_date;    // 2025-11-14 14:30:00
echo $pending->grade;               // P1
```

## Migration Status

✅ Migration ได้รัน successfully
- ตาราง `courses_pennding` ได้อัปเดตแล้ว
- คอลัมน์ใหม่ 4 คอลัมน์ถูกเพิ่มเข้าไปแล้ว

## หมายเหตุ

- ฟิลด์ทั้งหมดเป็น nullable ในฐานข้อมูล แต่ required ในการ validate ของ form
- `appointment_date` ใช้ format `datetime-local` จาก HTML5 input
- `grade` มีตัวเลือก K1, K2, P1-P6 ตามที่กำหนดในฟอร์ม
