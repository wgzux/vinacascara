# Vina Cascara – PHP E-Commerce

Website bán trà Cascara xây dựng bằng PHP thuần + MySQL, deploy trên Railway.

## 🚀 Deploy lên Railway

### Bước 1: Tạo project Railway
1. Truy cập [railway.app](https://railway.app) → New Project
2. Deploy from GitHub repo (push code lên GitHub trước)
3. Thêm **MySQL** plugin: Add Service → Database → MySQL

### Bước 2: Cấu hình Environment Variables
Vào Settings → Variables, thêm:
```
GOOGLE_CLIENT_ID=xxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxxx
VNPAY_TMN_CODE=xxxxx
VNPAY_HASH_SECRET=xxxxx
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
APP_DEBUG=false
```
> Railway tự động điền MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD

### Bước 3: Import Database
Vào Railway MySQL → Connect → chạy nội dung `database/schema.sql`

### Bước 4: Cấu hình Google OAuth
1. Truy cập [console.cloud.google.com](https://console.cloud.google.com)
2. APIs & Services → Credentials → Create OAuth 2.0 Client ID
3. Authorized redirect URIs: `https://your-app.railway.app/auth/google/callback`

### Bước 5: Cấu hình VNPay
- Sandbox: Đăng ký tại [sandbox.vnpayment.vn](https://sandbox.vnpayment.vn/devreg/)
- Production: Đăng ký merchant chính thức



## 📁 Cấu trúc thư mục
```
/
├── admin/          Admin dashboard
├── api/            AJAX endpoints  
├── assets/         CSS, JS, Images
├── config/         Cấu hình
├── core/           Classes (DB, Auth, Cart, VNPay)
├── database/       SQL schema
├── includes/       Header, Footer, Admin Header
├── pages/          Các trang khách hàng
├── index.php       Front controller
└── .htaccess       URL rewriting
```

## 🌐 Chạy local (XAMPP/Laragon)
```
1. Copy project vào htdocs/www
2. Tạo database và import schema.sql
3. Sửa config/config.php (nếu cần)
4. Truy cập http://localhost/WebsiteTea
```
