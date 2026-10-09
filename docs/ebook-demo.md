# eiMaths E-book · Android Demo

## สิ่งที่เพิ่ม

- API แยกที่ `/api/ebook/v1` สำหรับบัญชีนักเรียนเดิมใน `contacts` ไม่ใช้ endpoint Login ของเจ้าหน้าที่
- หนังสืออ้างอิง `bookuses`, `order_details` และ snapshot ใน `order_detail_lists` ไม่มีตารางแจกหนังสือรายนักเรียนใหม่
- E-book เล่มเดียวเชื่อมได้หลายชุด เช่น ชุดเต็มเทอมและครึ่งเทอมที่มีเล่มนั้น
- Import PDF ส่วนตัว → Queue → Poppler แปลงภาพ → ดูตัวอย่าง → เผยแพร่
- ต้นฉบับและภาพอยู่ใน private storage ทุกคำขอภาพ/งานต้องผ่าน Session และสิทธิ์หนังสือ ไม่มีลายน้ำ
- งานเก็บแยกตามนักเรียน/หน้า/ฉบับ ใช้ revision และ mutation ID กันเขียนทับหรือบันทึกซ้ำ
- Log แยกเหตุการณ์จาก Server กับเหตุการณ์ที่ App รายงาน และมี event ID ป้องกันข้อมูลซ้ำเมื่อส่งใหม่
- หลังบ้านมีเมนู **จัดการคอร์สเรียน → E-book** ที่ `/ebooks` และแท็บภายในโมดูลสำหรับคลังหนังสือ / นำเข้าหนังสือ / ประวัติการนำเข้า / Activity Log
- Role Management มีหมวด **E-book**: `ebook-manage` สำหรับส่วนกลางจัดการคลังหนังสือ นำเข้า ดูตัวอย่าง และเผยแพร่; `ebook-activity-list` สำหรับอ่าน Log ตามสาขาที่เข้าถึง ทั้งสองสิทธิ์ตรวจที่เซิร์ฟเวอร์และแสดงเฉพาะแท็บที่อนุญาต สาขาต้องเปิดโมดูล `courses`
- Migration `2026_10_09_000002_register_ebook_permissions.php` เพิ่มสิทธิ์จากความสามารถเดิมเพียงครั้งเดียว (`course-manage` → ทั้งสองสิทธิ์, `student-list` → อ่าน Log) รวมสิทธิ์ที่ผูกกับ Role และผู้ใช้โดยตรง จากนั้นแก้แยกได้ใน Role Management โดยไม่ขึ้นกับสิทธิ์คอร์ส/นักเรียนอีก
- คลังหนังสือแสดงรหัสจากระบบลงทะเบียนเดิม ชุดหนังสือ ฉบับและสถานะ รวมเล่มที่ยังไม่มี PDF พร้อมดูตัวอย่างผ่านประวัติการนำเข้าก่อนเผยแพร่

## รัน Demo บน Windows

รันจากโฟลเดอร์ `eiMaths` คำสั่งนี้ใช้ SQLite เฉพาะ Demo และไม่แก้ `.env` หรือฐานข้อมูลเดิม

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 .scripts/ebook-demo-setup.php
powershell -File .scripts/start-ebook-demo.ps1
```

เปิดหน้าจัดการตัวอย่างที่ `http://127.0.0.1:8787/ebook-demo` มี Import PDF, รายการฉบับ, เผยแพร่ และ Activity Log ใช้ข้อมูลตัวอย่างล้วน Route นี้ทำงานเฉพาะ PHP built-in server, local environment และคำขอจาก loopback เท่านั้น

ข้อมูล Demo:

| บัญชี | รหัสผ่าน | หนังสือที่เห็น |
|---|---|---|
| DEMO001 | Demo@eiMaths26 | G11–G20 หลังนำเข้าชุด K1 |
| DEMO002 | Demo@eiMaths26 | G16–G20 หลังนำเข้าชุด K1 |

ชุด K1 ใช้ไฟล์ `../PDF/011.pdf` ถึง `020.pdf` โดยรหัสในระบบเป็น **G11–G20** รวม 308 หน้า (G11–G19 เล่มละ 32 หน้า; G20 จำนวน 20 หน้าตามต้นฉบับ) เก็บ PDF และภาพใน private storage โดยไม่เพิ่มลายน้ำ

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 .scripts/ebook-import-k1.php --demo
# ตรวจตัวอย่างก่อนเผยแพร่
php -d extension=pdo_sqlite -d extension=sqlite3 .scripts/ebook-import-k1.php --demo --publish
```

ตัวนำเข้าเชื่อมกับชุดเต็มเทอมและชุดครึ่งเทอมที่มีเล่มนั้น ไม่แก้ข้อมูลลงทะเบียนนักเรียน รันซ้ำด้วยไฟล์เดิมไม่สร้างฉบับซ้ำ แบบฝึกหัดสังเคราะห์จาก Demo รุ่นแรกถูกเก็บเป็น DEMO-G11 / DEMO-G16 และไม่เผยแพร่ โดยรักษางานเดิมไว้

## หลังบ้านหลักบนเครื่องพัฒนา

App ใช้ API พอร์ต 8000 เป็นค่าเริ่มต้นและใช้ MySQL เดียวกับหลังบ้านหลัก หนังสือ งานนักเรียน และ Activity Log จึงดูได้ที่ `/ebooks` โดยตรง ฐาน SQLite Demo พอร์ต 8787 ยังเลือกใช้ได้เฉพาะเมื่อกำหนด EBOOK_API_BASE เอง งานและ token ใน App แยกตามระบบปลายทางเพื่อไม่ให้ข้อมูลปะปน

โมดูลเปิดโดยค่าเริ่มต้นเฉพาะ `APP_ENV=local` เมื่อไม่ได้ตั้ง `EBOOK_ENABLED` ไว้ และติดตั้ง migration เฉพาะ E-book ในฐานพัฒนาแล้ว ไม่มีการแก้ `.env` จริง

ใช้คำสั่งนี้แทน `php artisan serve` เมื่อต้องการอัปโหลด PDF สูงสุด 20 MB (หยุดเซิร์ฟเวอร์เดิมบนพอร์ต 8000 ก่อน):

```powershell
powershell -File .scripts/start-ebook-local.ps1 -PopplerBin "<โฟลเดอร์ที่มี pdfinfo.exe และ pdftoppm.exe>"
```

คำสั่งปรับ PHP เฉพาะ process นี้: `upload_max_filesize=20M`, `post_max_size=24M` ไม่แก้ `php.ini` ของเครื่อง ถ้ามี Poppler ใน PATH อยู่แล้วสามารถละ `-PopplerBin` ได้

การนำเข้าชุด K1 ในฐานหลักทำได้เฉพาะ local MySQL บน loopback:

```powershell
php .scripts/ebook-import-k1.php --local
php .scripts/ebook-import-k1.php --local --publish
```

หากติดตั้ง APK ทดสอบใหม่จนข้อมูล Login ในเครื่องถูกล้าง แต่ Session ตัวอย่างยังค้างอยู่:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 .scripts/ebook-demo-release-session.php
```

คำสั่งนี้ปลดเฉพาะ DEMO001 ในฐาน Demo และเก็บงานเดิม ไม่ใช้กับฐานข้อมูลจริง

## เปิดใช้กับระบบจริงภายหลัง

1. ติดตั้ง Poppler (`pdfinfo`, `pdftoppm`) และ PHP GD / mbstring / fileinfo
2. ตั้ง `EBOOK_ENABLED=true` และ path ของ Poppler ผ่าน environment ของระบบที่จะใช้ทดสอบจริง ตรวจสำรองฐานข้อมูลก่อน migration
3. รัน migration ของ E-book ทั้ง `2026_10_08_000001_create_ebook_tables.php` และ `2026_10_09_000001_improve_ebook_import_and_activity.php` ตามลำดับ
4. ใช้ queue แบบ asynchronous และ worker `php artisan queue:work --queue=ebooks --timeout=1200 --tries=1` ตั้ง retry_after มากกว่า timeout
5. เปิดหลังบ้าน `/ebooks` เลือกระดับ → เทอม → รหัสรายเล่มจากรายการลงทะเบียนเดิม ระบบผูกชุดเต็มและครึ่งเทอมให้อัตโนมัติ ไม่ต้องเลือกนักเรียนหรือกรอกรหัส/ชื่อซ้ำ
6. Flutter รุ่นเชื่อมระบบจริงต้องกำหนด HTTPS URL ด้วย `EBOOK_API_BASE` และใช้บัญชีนักเรียนจริงที่มีรหัสผ่านในระบบเดิม

ยังไม่ได้ติดตั้งบนเซิร์ฟเวอร์ production การรับ Office/EPUB และการอ่านหนังสือ offline ไม่อยู่ใน Demo นี้ PHP บน production ต้องตั้งขนาดอัปโหลดให้รองรับ 20 MB และตั้ง web server / proxy ให้สอดคล้องกัน

## สัญญา API

| Method | Path ภายใต้ `/api/ebook/v1` | งาน |
|---|---|---|
| POST | `/login` | code, password, device_id (UUID), device_label → token, session_id, student |
| GET / POST | `/me` / `/logout` | ตรวจ / ปิด Session |
| GET | `/books` | หนังสือที่ตรงกับการลงทะเบียนและเผยแพร่แล้ว |
| POST | `/books/{id}/open` | รายการหน้าและฉบับที่งานเดิมอยู่ |
| GET | `/pages/{id}/image` | ภาพหน้าเดิม ไม่มีลายน้ำ, no-store |
| GET / PUT | `/pages/{id}/work` | งาน JSON; PUT ใช้ revision, mutation_id, document |
| POST | `/events` | สูงสุด 25 events ต่อ batch; ใช้ event_id และ session_id ต้นทางเดิมเมื่อลองส่งใหม่; ตอบ accepted/rejected แยกรายการ |

ทุก endpoint ยกเว้น Login ใช้ `Authorization: Bearer ...` และ `X-Device-Id` Session เป็น token อายุจำกัดที่ผูกกับ installation ID ไม่ใช่รหัสฮาร์ดแวร์ถาวร อายุไม่ใช้งาน 30 นาที / สูงสุด 24 ชั่วโมง บัญชีที่มี Session อยู่จะไม่ถูกแย่งสิทธิ์จากการ Login ใหม่

งาน JSON ใช้พิกัดสัดส่วนหน้า 0–1 วัตถุ `pen`, `highlight`, `text`, `rect`, `ellipse`, `clip` เก็บ layer ตามลำดับ array การครอปเก็บ source page ID กับกรอบที่เลือก แล้ววาดจากภาพในหน่วยความจำ ไม่ใช้ Screenshot หรือ clipboard ของ OS

สถานะบันทึกต้องสำเร็จจาก Server จึงแสดงว่าบันทึกแล้ว งานค้างเก็บในพื้นที่ส่วนตัวของ App ตามบัญชี และส่งใหม่เมื่อเชื่อมต่อหรือกลับคลังหนังสือ การชน revision จะเก็บสำเนาในเครื่องไว้โดยไม่เขียนทับอัตโนมัติ

## ตรวจสอบ

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Feature/EbookApiTest.php
```

ครอบคลุมสิทธิ์ชุดหนังสือ, การเข้าถึงข้ามบัญชี, Session ซ้ำ/หมดอายุ, token ใช้กับ installation อื่น, ไม่มีลายน้ำ, idempotent save/log, การครอปข้ามหนังสือ, ฉบับใหม่รักษางานเดิม, การจำกัด route Demo และแปลง PDF จริง

## ขอบเขตตรวจอุปกรณ์

Android ใช้ [FLAG_SECURE](https://developer.android.com/security/fraud-prevention/activities) กับหน้าหนังสือ และใช้ callback ตาม API ที่ OS รองรับเท่านั้น ไม่สร้าง Log ว่าถ่าย/อัดจอจากการคาดเดา Pixel 6 API 32 ใช้ทดสอบตัวอ่านและ FLAG_SECURE; callback อัดจอ API 35 ต้องตรวจบน Tablet API 35 หรืออุปกรณ์ที่รองรับแยก

Flutter วาง responsive layout ทั้งมือถือ/Tablet/iPad และมี native bridge ฝั่ง iOS เตรียมไว้ แต่ iOS ยังไม่ได้ build หรือทดสอบบน Mac/Xcode/iPad ในรอบ Windows นี้ ต้องตรวจ iOS, Apple Pencil และ Android stylus บนอุปกรณ์จริงก่อนใช้งานจริง

## ประวัติและคิว Log

- `ebook_versions` เก็บชื่อไฟล์ ขนาด ผู้ที่นำเข้า เวลานำเข้า สถานะแปลง จำนวนหน้า และผู้เผยแพร่ครั้งแรก ข้อมูลก่อนเริ่มเก็บประวัติแสดงว่าไม่ระบุ ไม่มีการสร้างผู้ดำเนินการย้อนหลัง
- `ebook_admin_events` แยกการนำเข้า/เผยแพร่ของเจ้าหน้าที่จาก Activity Log นักเรียน ไม่มีการลบฉบับเก่าหรืองานนักเรียนเมื่ออัปโหลด PDF ใหม่
- `ebook_session_history` เก็บบริบท Session เดิมโดยไม่เก็บ token ใช้ยืนยันว่า Log ที่ค้างส่งเป็นของนักเรียนและ installation เดิม
- `ebook_events.occurred_at` เป็น UTC ตามเวลาที่ App รายงาน (หรือเวลา Server สำหรับเหตุการณ์ฝั่ง Server) ส่วน `received_at` เก็บตาม timezone เดิมของระบบ UI แสดงเป็นเวลาไทยและกรองวันที่ตามเวลาเกิดเหตุการณ์
- API รับรายการที่ถูกต้องต่อแม้มีรายการผิดรูปแบบ และตอบ `rejected` พร้อม index/event_id/reason; App เก็บรายการที่ถูกปฏิเสธในไฟล์ส่วนตัวเพื่อให้ตรวจสอบ โดยไม่ขวางรายการถัดไป
- เหตุการณ์จาก App เป็นข้อมูลที่ client รายงาน ไม่ใช่หลักฐานที่รับรองว่า OS แจ้งทุกความพยายามจับภาพ

ทดสอบ local main: `.scripts/ebook-main-qa.php setup` สร้างเฉพาะบัญชี `[LOCAL QA]` และไฟล์ตั้งค่าทดสอบส่วนตัว ใช้ `.scripts/ebook-main-storage-qa.py` คู่กับ `storage_persistence_test.dart` เพื่อตรวจ offline/force-stop/retry พอร์ต 8791 เป็นตัวควบคุมทดสอบชั่วคราว ไม่ใช่ API ของ App และต้องปิดเมื่อทดสอบจบ

## ผลตรวจ Local วันที่ 9 ตุลาคม 2026

ทดสอบ App กับ MySQL หลักผ่านพอร์ต 8000 บน Pixel 6 API 32 และ Pixel Tablet API 35 ผ่านการอ่าน เขียน ครอปวางข้ามหน้า Undo/Redo บันทึก เปิดกลับ และหมุนจอ บัญชีทดสอบแยกเป็น `EBOOK-QA001` / `EBOOK-QA002` โดยมีป้าย `[LOCAL QA]`

ทดสอบหยุด API จริงขณะเขียน G11 หน้า 5: งานในเครื่องเพิ่มจาก 3 เป็น 5 ชิ้น ขณะที่ฐานข้อมูลยังมี 3 ชิ้น จากนั้น force-stop App และ Login ด้วย Session ใหม่ งานทั้ง 5 ชิ้นกลับเข้าฐานหลัก คิว Log ส่งหมด และ Log ออฟไลน์ยังอ้างอิง Session ต้นทางเดิม PDF/ภาพต้นฉบับมี SHA-256 เท่าเดิม

นำเข้า `020.pdf` ผ่านหลังบ้านด้วยรหัส G20 ได้ฉบับ 2 สถานะพร้อมตรวจสอบ 20 หน้า โดยฉบับ 1 ยังเผยแพร่ตามเดิม ไม่เปลี่ยนฉบับของงานนักเรียนจากการนำเข้า

Pixel Tablet API 35: ทดสอบ `adb screencap` และ `adb screenrecord` ขณะเปิดหนังสือพร้อม FLAG_SECURE ได้พื้นที่ App เป็นภาพดำ ทั้งสองวิธีไม่ได้ส่ง OS capture callback ในรอบนี้ จึงไม่มี Log การจับภาพที่สร้างขึ้นเอง ผลนี้ยืนยันการปิดภาพใน Emulator ที่ทดสอบ แต่ยังไม่ใช่ผลทดสอบการแจ้งเตือนจาก MediaProjection บนอุปกรณ์จริงหรือ iOS

หลักฐานและผลรายขั้นอยู่ใน `tmp/ebook-main-qa-20261009/report.html` (ไฟล์ทดสอบในเครื่อง ไม่รวมใน Git) ไม่เผยแพร่ไฟล์ `phone.json` / `tablet.json` เพราะมีรหัสผ่านเฉพาะบัญชีทดสอบ หลังทดสอบได้คืน APK ปกติและปิดตัวควบคุมชั่วคราวแล้ว

### การตรวจ Screenshot และวิดีโอบนมือถือ API 32 (9 ตุลาคม 2026)

หน้าปกป้องหนังสือแยกสถานะเสียโฟกัส, OS แจ้ง Screenshot และ OS แจ้งการอัด/แชร์จอ การเสียโฟกัสบันทึกเป็น `reader_hidden` / `reader_resumed` และไม่อนุมานว่าเป็นการจับภาพ ส่วน `app_background` / `app_foreground` ใช้เมื่อแอปเข้าสู่พื้นหลังจริง การแจ้งจับภาพที่ได้รับจะบันทึกลงคิวก่อนส่ง API ทันที และคง FLAG_SECURE จนหน้าหนังสือปิดพร้อมแอนิเมชันเรียบร้อย

ทดสอบแอปแยก `th.eimaths.eimaths_app.captureqa` กับบัญชี EBOOK-QA001 ผ่าน API พอร์ต 8000: `adb screenrecord --time-limit 8` ได้ภาพดำในพื้นที่แอปทั้ง 31 เฟรม; `adb shell input keyevent KEYCODE_SYSRQ` ทำให้เกิด Log เสียโฟกัสและกลับมาแสดงหนังสือ โดย API 32 ไม่มี native Screenshot/Recording callback ที่โค้ดนี้ใช้

`integration_test/capture_flow_test.dart` ทดสอบสัญญาณ EventChannel จำลองแยกจากการจับภาพจริง เพื่อยืนยัน UI → API → Activity Log สำหรับ Screenshot และเริ่ม/หยุดอัดจอ ได้รับการยืนยันครบ ไม่มีรายการค้างหรือถูกปฏิเสธ ผลนี้ไม่ใช่หลักฐานว่ามือถือ API 32 ตรวจพบการจับภาพได้ ชุดทดสอบนี้ต้องใช้บัญชี QA และ applicationId แยกจากแอปนักเรียน

หลักฐานรอบนี้อยู่ใน `tmp/ebook-main-qa-20261009/capture-evidence/` รวม UI, วิดีโอ, ผลตรวจทุกเฟรม, Log ก่อน/หลังช่วงจำลอง และภาพหน้าหลังบ้าน ส่วน unit/widget tests รันด้วย `flutter test test/reader_layout_test.dart test/reader_exit_protection_test.dart test/event_queue_test.dart`
