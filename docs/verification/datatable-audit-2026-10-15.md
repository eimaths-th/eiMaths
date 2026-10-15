# รายงานทดสอบ DataTables หลังบ้าน eiMaths

วันที่ 15 ตุลาคม 2026 ตรวจตารางที่ใช้งานจริง 31 ตาราง และแก้ปัญหาการเรียง Permission ที่ผู้ใช้พบ พร้อมข้อบกพร่องที่เกี่ยวข้องกับการเรียง ค้นหา และแสดงตารางว่าง ผลรอบสุดท้ายผ่าน 734 กรณีของ AJAX 29 ตาราง โดยไม่พบ SQL/AJAX error หรือคอลัมน์ที่ขาดจาก response ส่วนอีก 2 ตารางทดสอบผ่านหน้าจอโดยตรง

## สภาพแวดล้อมและขอบเขต

- Backend `eiMaths` บน branch `demo`, baseline `80ace3f6` พร้อมการแก้ไขใน working tree
- URL ที่ทดสอบหน้าจอคือ `http://127.0.0.1:8000` โดยใช้บัญชี SystemAdmin ที่ผู้ใช้ล็อกอินไว้
- ทดสอบ endpoint กับ MySQL บนเครื่องแบบอ่านข้อมูล โดยครอบ request ด้วย transaction และ rollback; ข้อมูลจำลองอยู่ใน SQLite `:memory:` เท่านั้น
- ครอบคลุมโหลดตาราง เรียงขึ้น/ลงทุกคอลัมน์ที่เปิดให้เรียง ค้นหารวม ค้นหารายคอลัมน์ ไม่พบข้อมูล แบ่งหน้า เปลี่ยนจำนวนแถว และตรวจคีย์ข้อมูลเทียบกับคอลัมน์ UI
- ตรวจ UI ทุกตารางด้วยการโหลดและใช้ตัวควบคุมที่มีในหน้านั้น เช่น sort, search, reset และ modal ประวัติสต็อก การเรียงทุกคอลัมน์ตรวจผ่าน HTTP เพิ่มเติม เพราะบางคอลัมน์ถูกซ่อนเมื่อหน้าจอแคบ
- ขอบเขตรอบนี้คือ DataTables ไม่รวมการกดบันทึก/ลบข้อมูลธุรกิจ หรือทดสอบ Export/Print ทุกแบบ
- ผลนี้เป็นผลบนเครื่อง ยังไม่ได้ deploy หรือยืนยันกับ `test-web.eimaths-th.com`

## สิ่งที่แก้ไข

| ลำดับ | ปัญหา | การแก้ไข |
|---|---|---|
| 1 | Role Management สั่ง SQL `ORDER BY role` ทั้งที่ไม่มีคอลัมน์นี้ | คง query จำกัดสิทธิ์เดิม แล้วเรียงและค้นหาชื่อ Role กับรายการ Permission ที่แสดงจริง รวมถึงชื่อแสดงและขอบเขต Role |
| 2 | Learning History และ Course Applications เรียงข้อมูลจากความสัมพันธ์ด้วยชื่อคอลัมน์ที่ไม่มีจริง | กำหนด query เรียงรหัส/ชื่อนักเรียน สาขา หนังสือ เวลาเรียน และรหัสสาขาของใบสมัครโดยตรง |
| 3 | หลายหน้าบังคับเรียง ID ก่อน ทำให้กดหัวคอลัมน์แล้วไม่เปลี่ยนตามที่เลือก | ใช้ ID เป็นค่าเริ่มต้นเมื่อไม่มีคำสั่งเรียงที่ใช้งานได้ แก้ Centres, Departments, Guest Report, Course Applications, LINE, Telegram และ Blog |
| 4 | ค้นหาชื่อหรือเบอร์พ่อแม่ใน Students/Graduated ชี้คอลัมน์ผิด | เปลี่ยนจากเลขลำดับคอลัมน์เป็นชื่อคอลัมน์ เช่น `father_name`, `mother_mobile` และแก้ Parents ให้ค้นหารหัสนักเรียนผ่านความสัมพันธ์ |
| 5 | เปลี่ยนประเภทค้นหาแล้วตัวกรองเก่ายังค้าง | ล้าง global search และ column search ก่อนใช้เงื่อนไขใหม่ในหน้าที่มี Search Type |
| 6 | Reset ล้าง HTML ทั้งตาราง จนเสี่ยงหัวตารางหายหรือไม่ตรงข้อมูล | เก็บโครงสร้างหัวตารางไว้ระหว่าง destroy และสร้าง DataTable ใหม่ |
| 7 | คอลัมน์ปุ่ม Action และ More เปิดให้เรียง/ค้นหาโดยไม่จำเป็น | ปิด sort/search สำหรับคอลัมน์ควบคุม; Course Categories/Levels ปิดสำหรับเลขลำดับ สวิตช์ และปุ่ม |
| 8 | Question Bank วางข้อความในช่องค้นหาแล้วไม่ค้นหา | นำ keyup handler ที่ทับพฤติกรรมเดิมออก ให้ DataTables รองรับการพิมพ์ วางข้อความ และล้างคำค้นตามปกติ |
| 9 | Blog ซ่อนช่องค้นหา และ endpoint เรียง Author แล้ว `id` กำกวมเมื่อ join Users | แสดงช่องค้นหา และระบุชื่อคอลัมน์พร้อมตาราง `blogs.*` ให้ชัดเจน |
| 10 | Graduated Students ล้มเมื่อยังไม่มี master ระดับ/หนังสือ | รองรับค่า null และคืนตารางว่างได้ตามปกติ |
| 11 | LINE/Telegram เลขลำดับว่าง และการเรียง/ค้นหาผู้สร้างไม่ตรงชื่อ | ใช้ `DT_RowIndex` และกำหนดการเรียง/ค้นหาชื่อ creator/editor โดยตรง |
| 12 | คอลัมน์ชนิดคอร์ส/ใบสมัคร และสถานะ Free ของ Quiz ไม่เข้า sort/search ตามคอลัมน์จริง | ใช้ `editColumn` กับคอลัมน์ที่มีในฐานข้อมูล และแก้ filter จำนวนคำถามใน Quiz ให้กรองจริง |
| 13 | ประวัติเรียนมีทั้งรหัสหนังสือและชื่อหนังสือแบบเดิม | รองรับการเรียงและค้นหาทั้งสองรูปแบบ และแสดงชื่อเล่นจากข้อมูลนักเรียนที่สัมพันธ์กัน |
| 14 | Pending Invoice/Term Receipt อ้าง select alias ภายใน subquery ทำให้ทดสอบข้ามฐานข้อมูลไม่ได้ | เปลี่ยนเป็น correlated subquery ที่ระบุตารางชัดเจน คงการเลือกหนังสือถัดไป/ราคา และตรวจทั้ง MySQL กับข้อมูลจำลอง SQLite |

## เช็กลิสต์ทุกตาราง

สถานะ “ผ่าน” หมายถึงกรณี DataTables ที่ระบุในขอบเขตนี้ ผ่านทั้งการตรวจ response และการตรวจหน้าจอที่เกี่ยวข้อง ไม่ใช่การรับรองทุก workflow ของโมดูล

| # | ตาราง | Route | กรณี HTTP | ผล | ข้อมูลที่ใช้ตรวจ |
|---|---|---|---:|---|---|
| 1 | Role Management | `/roles` | 11 | ผ่าน | มีข้อมูล |
| 2 | Permission Management | `/permission` | 8 | ผ่าน | มีข้อมูล |
| 3 | Users | `/users` | 23 | ผ่าน | มีข้อมูล |
| 4 | Centres | `/departments` | 14 | ผ่าน | มีข้อมูล |
| 5 | Departments | `/positions` | 14 | ผ่าน | มีข้อมูล |
| 6 | Students | `/contacts` | 56 | ผ่าน | มีข้อมูล |
| 7 | Graduated Students | `/graduated` | 53 | ผ่าน | ข้อมูลว่าง; มีข้อมูลจำลองใน regression test |
| 8 | Discontinued Students | `/discontinued` | 26 | ผ่าน | มีข้อมูล |
| 9 | Parents | `/parent` | 23 | ผ่าน | มีข้อมูล |
| 10 | Learning History | `/histories` | 35 | ผ่าน | มีข้อมูล |
| 11 | Orders | `/orders` | 32 | ผ่าน | มีข้อมูล |
| 12 | Payment Receipts | `/receipts` | 32 | ผ่าน | มีข้อมูล |
| 13 | Pending Receipts | `/receipts_pending` | 29 | ผ่าน | มีข้อมูล |
| 14 | Pending Term Receipts | `/receipts_pending_term` | 26 | ผ่าน | ข้อมูลว่าง; มีข้อมูลจำลองใน regression test |
| 15 | Invoices | `/invoices` | 29 | ผ่าน | ข้อมูลว่าง; มีข้อมูลจำลองใน regression test |
| 16 | Pending Invoices | `/invoices_pending` | 26 | ผ่าน | ข้อมูลว่าง; มีข้อมูลจำลองใน regression test |
| 17 | Products | `/products` | 20 | ผ่าน | มีข้อมูล |
| 18 | Stock Ledger ในหน้าต่าง Log | `/products/stock` | 32 | ผ่าน | ทดสอบทั้ง endpoint และ modal Log |
| 19 | Courses | `/course/list` | 18 | ผ่าน | มีข้อมูล |
| 20 | Course Applications | `/courses/pending/index` | 32 | ผ่าน | ข้อมูลว่าง; มีข้อมูลจำลองใน regression test |
| 21 | Question Bank | `/questions` | 22 | ผ่าน | มีข้อมูล |
| 22 | Quizzes | `/quizzes` | 35 | ผ่าน | มีข้อมูล |
| 23 | Guest Report | `/guest-report` | 29 | ผ่าน | มีข้อมูล |
| 24 | eiForm Applications | `/ei_form` | 20 | ผ่าน | มีข้อมูล |
| 25 | Parameters | `/parameter` | 11 | ผ่าน | มีข้อมูล |
| 26 | Term Fees | `/termfee` | 20 | ผ่าน | มีข้อมูล |
| 27 | LINE Settings | `/line-api` | 26 | ผ่าน | มีข้อมูล |
| 28 | Telegram Settings | `/telegram-setting` | 14 | ผ่าน | มีข้อมูล |
| 29 | Blogs | `/blogs` | 18 | ผ่าน | UI เรียง/ค้นหาใน browser; ทดสอบ endpoint เพิ่มด้วย |
| 30 | Course Categories | `/course/categories` | ไม่ใช้ AJAX | ผ่าน | เรียงชื่อ ค้นหาไม่พบ และล้างคำค้น |
| 31 | Course Levels | `/course/course-level/list` | ไม่ใช้ AJAX | ผ่าน | เรียงชื่อ ค้นหาไม่พบ และล้างคำค้น |

ตารางที่ข้อมูลใน MySQL ว่าง ได้แก่ Graduated, Course Applications, Invoices, Pending Invoices และ Pending Term Receipts มี regression test สร้างข้อมูลจำลองเพื่อทดสอบการเรียงจริงเพิ่มเติม จึงไม่ได้ตรวจเฉพาะ response ของตารางว่าง

คอลัมน์ที่เรียงขึ้น/ลงแล้วลำดับเหมือนกันในฐานข้อมูลตัวอย่าง เช่น Status ของสาขา จำนวนสินค้าใน Orders และประเภท Courses ได้ตรวจจำนวนค่าที่แตกต่างแล้ว พบว่าคอลัมน์นั้นมีค่าเดียวกันทุกแถว จึงไม่ใช่การเรียงเสีย

## ผลทดสอบอัตโนมัติ

| ชุดทดสอบ | ผล |
|---|---|
| MySQL ผ่าน HTTP kernel ของแอปและ payload ตามคอลัมน์หน้าเว็บ | 29 ตาราง, 734 กรณี, 0 errors, 0 missing columns |
| `tests/Feature/DataTables` | 40 tests ผ่าน, 11,419 assertions |
| `BranchIsolationTest` และ `GuestReportAccessTest` | 22 tests ผ่าน, 94 assertions |
| PHP syntax ของ controller และไฟล์ทดสอบที่แก้ | 18 ไฟล์ผ่าน |
| Compile Blade ที่แก้และตรวจ PHP syntax ของผลลัพธ์ | 25 ไฟล์ผ่าน |
| `git diff --check` | ผ่าน |

รวม regression tests 62 tests ผ่าน โดยมีกรณีตรวจ Role ของ SystemAdmin ไม่รั่วไปยังผู้ใช้สาขา การซ่อน Role ต่างสาขา การค้นหาผู้ปกครอง/ประวัติเรียนข้ามสาขา และตารางว่างของสาขาใหม่ 12 ตาราง สิทธิ์เดิมยังถูกบังคับใช้บน backend

ทดสอบความหมายของการเรียงด้วยข้อมูลที่มีลำดับต่างกันจริง เช่น ชื่อสาขา/ชื่อพนักงานไม่ตรงลำดับ ID, Permission หลายรายการ, รหัสนักเรียนไม่ตรงลำดับชื่อ, เวลาเรียน, ค่าเอกสารเป็นตัวเลข และชื่อผู้สร้างรายการ

## ไฟล์และการรันทดสอบซ้ำ

การแก้ product code อยู่ใน **Backend `eiMaths`** ทั้ง controller และ Blade ไม่มีการแก้ product code ใน Frontend `eimaths_th_new` และไม่มี migration ใหม่

- Controller หลัก: `RoleController`, `HistoriesController`, `CourseController`, `DepartmentController`, `PositionController`, `ParentController`, `ContactController`, `GuestReportController`, `QuizzesController`, `LineApiController`, `TelegramSettingController`, `BlogController`, `InvoiceController`, `ReceiptController`
- Blade: scripts ของหน้าตารางที่อยู่ในเช็กลิสต์ รวมถึง Question Bank, Course Categories/Levels และ style ของ Blog
- Regression tests: `tests/Feature/DataTables/DataTableContractTest.php`, `DataTableOrderingTest.php`, `DataTablePopulatedRecordsTest.php`; ใช้ `DataTableTestCase.php` และคอลัมน์ใน `tests/Fixtures/datatables.json`

รันจากโฟลเดอร์ Backend โดยใช้ `phpunit.xml` ของโปรเจกต์ ซึ่งบังคับ SQLite `:memory:`:

```powershell
& 'C:/xampp/php-8.4.8/php.exe' -d extension=pdo_sqlite -d extension=sqlite3 -d memory_limit=512M vendor/bin/phpunit tests/Feature/DataTables --testdox
& 'C:/xampp/php-8.4.8/php.exe' -d extension=pdo_sqlite -d extension=sqlite3 -d memory_limit=512M vendor/bin/phpunit tests/Feature/Branch --filter 'BranchIsolationTest|GuestReportAccessTest' --testdox
```

เครื่องที่เปิด SQLite extensions ใน php.ini แล้วไม่ต้องระบุ `-d extension` ซ้ำ เมื่อเพิ่ม/เปลี่ยนคอลัมน์ DataTables ให้ปรับ fixture และชุดทดสอบให้ตรงกับ Blade ด้วย

## สิ่งที่ควรทำต่อ

1. นำโค้ดชุดนี้ขึ้น test แล้วตรวจ Role/Permission, Students search/reset, Pending documents และสาขาใหม่อีกครั้งกับข้อมูลของ server จริง
2. ลดการโหลดสคริปต์จาก CDN ในหน้า back office: ระหว่างทดสอบหน้า Course Categories มีช่วงที่ DOM โหลดแล้วแต่ยังรอสคริปต์ภายนอก ทำให้ DataTable ยังไม่เริ่มทำงาน จนสคริปต์โหลดเสร็จแล้วจึงใช้งานได้ ควรใช้ asset ภายในระบบให้สอดคล้องกับเวอร์ชันที่ใช้อยู่
3. วางแผนลด query และการโหลดทั้งชุดในหน้าที่ใช้ Collection หรือโหลดข้อมูลทั้งหมดก่อนแบ่งหน้า รวมถึง Role catalogue หากมีจำนวน Role มากขึ้น รอบนี้แก้ความถูกต้องของตาราง ยังไม่ได้ทำ load test สำหรับจำนวนข้อมูลขนาดใหญ่
4. เพิ่มชุดทดสอบ DataTables เข้า CI เพื่อจับคอลัมน์ที่เปลี่ยนชื่อแต่ controller หรือหน้าเว็บยังใช้ชื่อเดิมก่อนนำขึ้น test

หน้า Demo ที่ถูกปิดและปุ่ม PDF ที่ผู้ใช้เคยสั่งปิดยังคงปิดอยู่ ตารางธรรมดาที่ไม่ได้ใช้ DataTables และ endpoint เก่าที่ไม่มีหน้าจอเรียกใช้งานไม่ถูกนับซ้ำใน 31 ตารางนี้
