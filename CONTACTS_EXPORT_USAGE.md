# การใช้งาน Contact Export Functions

## ฟังก์ชันที่เพิ่มใหม่

### 1. Contact Model Methods

#### `getContactsByCentre()`
Query ข้อมูล contacts จาก centre 2, 3 และจัดกลุ่มตาม centre เรียงจาก id มากไปน้อย

```php
use App\Models\Contact;

$contacts = Contact::getContactsByCentre();
// ผลลัพธ์: Collection ที่ group by centre โดยข้อมูลเรียงจาก id มากไปน้อย
```

#### `exportToExcel($filename)`
Export ข้อมูล contacts จาก centre 2, 3 เป็น Excel file

```php
use App\Models\Contact;

// Export โดยใช้ชื่อไฟล์ default
$response = Contact::exportToExcel();

// หรือกำหนดชื่อไฟล์เอง
$response = Contact::exportToExcel('my_contacts_export.xlsx');
```

### 2. Controller Methods

#### `exportContactsByCentre()`
URL: `GET /contacts/export-by-centre`
Route name: `contacts.exportByCentre`

ดาวน์โหลดไฟล์ Excel โดยตรง

```php
// เรียกผ่าน URL
https://yourdomain.com/contacts/export-by-centre

// หรือใช้ route name
{{ route('contacts.exportByCentre') }}
```

#### `getContactsByCentre()`
URL: `GET /contacts/get-by-centre`
Route name: `contacts.getByCentre`

ดูข้อมูลก่อน export (JSON response)

```php
// Response format:
{
    "success": true,
    "data": {
        "2": [/* contacts from centre 2 */],
        "3": [/* contacts from centre 3 */]
    },
    "total_centre_2": 15,
    "total_centre_3": 22,
    "total": 37
}
```

## ตัวอย่างการใช้งานใน View

### สร้างปุ่ม Export
```html
<!-- ปุ่มสำหรับ export ทันที -->
<a href="{{ route('contacts.exportByCentre') }}" class="btn btn-success">
    <i class="fa fa-download"></i> Export Centre 2,3 to Excel
</a>

<!-- ปุ่มสำหรับดูข้อมูลก่อน -->
<button type="button" class="btn btn-info" onclick="previewData()">
    <i class="fa fa-eye"></i> Preview Data
</button>
```

### JavaScript สำหรับดูข้อมูลก่อน Export
```javascript
function previewData() {
    fetch('{{ route('contacts.getByCentre') }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`จำนวนข้อมูล Centre 2: ${data.total_centre_2} คน\n` +
                      `จำนวนข้อมูล Centre 3: ${data.total_centre_3} คน\n` +
                      `รวมทั้งหมด: ${data.total} คน`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
```

## ข้อมูลที่จะถูก Export

ไฟล์ Excel จะมีคอลัมน์ดังนี้:
- ID
- Centre  
- Code
- Name
- Nickname
- Type
- School
- Gender (แปลงเป็น Male/Female)
- Birth Date
- Telephone
- Father Name
- Father Email
- Father Mobile
- Mother Name
- Mother Email
- Mother Mobile
- Level
- Term
- Start Date
- Created At
- Updated At

## การจัดรูปแบบ Excel

- Row แรกเป็น header (ตัวหนา)
- ข้อมูลจะถูกจัดกลุ่มตาม centre
- เรียงลำดับจาก ID มากไปน้อย

## Requirements

- Laravel Excel package (`maatwebsite/excel`) - ติดตั้งแล้วใน composer.json
- ContactsExport class ใน `app/Exports/ContactsExport.php`
- Contact model methods ใน `app/Models/Contact.php`
- Controller methods ใน `app/Http/Controllers/ContactController.php`
- Routes ใน `routes/web.php`

## Security

Functions เหล่านี้ควรมี middleware authentication และ permission checking ตามระบบที่มีอยู่ เช่น:
- `permission:student-list`
- `permission:student-export`

สามารถเพิ่ม middleware ใน routes หรือ constructor ของ controller ได้
