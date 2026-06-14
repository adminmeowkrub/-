******Admin MeowKrub (admin.meowkrub@gmail.com)******
 14 มิถุนายน พ.ศ.2569
เปิดเป็น OpenSource
ใครจะทำต่อก็ได้

# Management System
ระบบจัดการห้องคอมพิวเตอร์

---
ไฟล์โหลดได้ที่
https://github.com/adminmeowkrub/-/releases
โหลดแล้ว แตกไฟล์ด้วยนะ
---

## โครงสร้างไฟล์

```
lab-system/
├── server/                  ← วางบน Server
│   ├── config.php
│   ├── database.sql
│   ├── admin/
│   │   ├── index.php        ← หน้า Login
│   │   ├── dashboard.php    ← หน้าหลัก
│   │   ├── logs.php         ← Logs การกระทำ
│   │   ├── stats.php        ← สถิติ + กราฟ
│   │   ├── admins.php       ← จัดการ Admin
│   │   └── logout.php 
│   └── api/
│       ├── unlock.php       ← API ปลดล็อค
│       ├── log.php          ← API รับ Log
│       └── control.php      ← API สั่งปิดเครื่อง
└── client/
    └── lab_client.py        ← รันบนคอมเครื่องส่วนรวม
```

---

## ติดตั้ง Server

### 1. ติดตั้ง XAMPP
- ดาวน์โหลด XAMPP จาก https://www.apachefriends.org
- ติดตั้งและเปิด Apache + MySQL

### 2. วางไฟล์
```
(ตำแหน่ง xampp)\htdocs\  ← วางโฟลเดอร์ server/ ทั้งหมดที่นี่
```
ให้โครงสร้างเป็น:
```
(ตำแหน่ง xampp)\htdocs\config.php
(ตำแหน่ง xampp)\htdocs\admin\index.php
(ตำแหน่ง xampp)\htdocs\api\unlock.php
...
```

### 3. สร้างฐานข้อมูล
1. เปิด http://localhost/phpmyadmin
2. คลิก "New" → ตั้งชื่อ `lab_system`
3. คลิก Import → เลือกไฟล์ `database.sql`
4. กด "Go"

### 4. แก้ config.php (ถ้าจำเป็น)
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // รหัส MySQL ของคุณ
define('DB_NAME', 'lab_system');
```

### 5. เพิ่ม IP คอมนักเรียน
ไปที่ phpMyAdmin → ตาราง `computers` → แก้ IP ให้ตรง:
- PC-01 → เช่น 10.0.0.3
- PC-02 → เช่น 10.0.0.4
- ... ตามจริง

---

## ติดตั้ง Client (คอมส่วนรวมทุกเครื่อง)

### 1. ติดตั้ง Python
- ดาวน์โหลด Python 3.11+ จาก https://python.org
- ✅ ติ๊ก "Add Python to PATH"
lab_client.py — ติดตั้งบนคอมนักเรียนทุกเครื่อง
ต้องการ: pip install requests pywin32 psutil tkinter

วิธีใช้: ใส่ใน Startup ของ Windows

### 2. ติดตั้ง Library
เปิด Command Prompt แล้วพิมพ์:
```
pip install requests psutil pywin32
```

### 3. วางไฟล์ Client
```
C:\LabClient\lab_client.py
```

### 4. ตั้งให้รันตอนเปิดเครื่อง
1. กด `Win + R` → พิมพ์ `shell:startup`
2. สร้างไฟล์ `lab.bat` ในโฟลเดอร์ที่เปิดมา:
```bat
@echo off
cd C:\LabClient
pythonw lab_client.py
```

---

## การใช้งาน

### เจ้าหน้าที่
1. เปิดเบราว์เซอร์ → `http://ที่ตั้งเว็บ เช่น 10.0.0.5 ประมาณนั้น/admin/`
2. Login ด้วย Username: `ตามที่ตั้ง` / Password: `ตามที่ตั้ง`
3. Dashboard จะแสดงคำขอปลดล็อคจากนักเรียน
4. คลิก **"✓ ปลดล็อค"** เพื่ออนุมัติ

### นักเรียน
1. เปิดเครื่อง → หน้าจอล็อคขึ้นอัตโนมัติ
2. รอเจ้าหน้าที่ปลดล็อคจาก Server
3. ถ้าไม่มีการใช้งาน 2 นาที → Shutdown อัตโนมัติ

---

## รหัสผ่านเริ่มต้น
- Username: `admin`
- Password: `password`

**⚠ เปลี่ยนรหัสผ่านทันทีหลังติดตั้งเสร็จ!**
ไปที่ Admin Panel → จัดการ Admin → เปลี่ยนรหัสผ่าน

---

## หมายเหตุ
- Web Log ต้องใช้ Browser Extension หรือ Proxy เพิ่มเติม
  สำหรับระบบนี้บันทึก URL ผ่าน API ที่ Client ส่งมา
- Idle Timeout ปรับได้ใน `lab_client.py` บรรทัด `IDLE_TIMEOUT = 30`
- Server IP ปรับได้ใน `lab_client.py` บรรทัด `SERVER_URL`
