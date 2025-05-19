<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>


<h2>🚀 Hướng Dẫn Chạy Dự Án Laravel</h2>

<ol>
  <li>
    <strong>BƯỚC 1: Cấu hình kết nối CSDL</strong><br>
    Mở file <code>.env</code> và cập nhật thông tin:
    <pre>
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
    </pre>
  </li>

  <li>
    <strong>BƯỚC 2: Tạo bảng trong cơ sở dữ liệu</strong><br>
    <code>php artisan migrate</code>
  </li>

  <li>
    <strong>BƯỚC 3: Đổ dữ liệu mẫu</strong><br>
    <code>php artisan db:seed</code>
  </li>

  <li>
    <strong>BƯỚC 4: Làm mới autoload</strong><br>
    <code>composer dump-autoload</code>
  </li>

  <li>
    <strong>BƯỚC 5: Biên dịch các file front-end (nếu có)</strong><br>
    <code>npm install</code><br>
    <code>npm run dev</code>
  </li>

  <li>
    <strong>BƯỚC 6: Khởi chạy server ảo Laravel</strong><br>
    <code>php artisan serve</code>
  </li>
</ol>
