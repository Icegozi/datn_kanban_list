@extends('layouts.app')

@section('title', 'Chào mừng đến MyApp - Quản lý công việc hiệu quả')

@section('content')

    <header id="header-top" class="header-top">
        <ul>
            <li class="head-responsive-right pull-right">
                <div class="header-top-right">
                    <ul>

                    </ul>
                </div>
            </li>
        </ul>

    </header>

    <section class="top-area">
        <div class="header-area">
            <nav class="navbar navbar-default bootsnav navbar-sticky navbar-scrollspy" data-minus-value-desktop="70"
                data-minus-value-mobile="55" data-speed="1000">

                <div class="container">

                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                            <i class="fa fa-bars"></i>
                        </button>

                        <a class="navbar-brand" href="{{route('webcome')}}">My<span>App</span></a>

                    </div>

                    <div class="collapse navbar-collapse menu-ui-design" id="navbar-menu">
                        <ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">
                            <li class=" scroll active"><a href="#home">Trang chủ</a></li>
                            <li class="scroll"><a href="#features">Tính năng</a></li>
                            <li class="scroll"><a href="#howitworks">Cách hoạt động</a></li>
                            {{-- <li class="scroll"><a href="#reviews">Đánh giá</a></li> --}}
                            <li><a href="{{ route('login.form') }}">Đăng nhập</a></li>
                            <li><a href="{{ route('register.form') }}">Đăng ký</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

        </div>
        <div class="clearfix"></div>

    </section>



    <section id="home" class="welcome-hero">
        <div class="container">
            <div class="welcome-hero-txt">
                <h2>Trực quan hóa Luồng công việc & Tăng năng suất</h2>
                <p>
                    MyApp giúp bạn quản lý công việc, cộng tác với nhóm và hoàn thành nhiều việc hơn. Đơn giản, trực quan,
                    hiệu quả.
                </p>
            </div>
            <div class="welcome-hero-serch-box">

                <div class="welcome-hero-serch" style="width:100%; text-align:center;">
                    <button class="welcome-hero-btn" onclick="window.location.href='{{ route('register.form') }}'">

                        Bắt đầu Miễn phí <i data-feather="arrow-right-circle"></i>
                    </button>
                    <!-- Tùy chọn: Thêm nút/liên kết phụ -->
                    <!-- <p style="margin-top: 15px;"><a href="#howitworks" class="scroll">Tìm hiểu thêm</a></p> -->
                </div>
            </div>
        </div>

    </section>


    <section id="features" class="list-topics">
        <div class="container">
            <div class="section-header">
                <h2>Tính năng Nổi bật</h2>
                <p>Mọi thứ bạn cần để tối ưu hóa công việc</p>
            </div>
            <div class="list-topics-content">
                <ul>
                    <li>
                        <div class="single-list-topics-content">
                            <div class="single-list-topics-icon">
                                <i class="fa fa-th-large"></i>
                            </div>
                            <h2><a href="#">Bảng Trực quan</a></h2>

                        </div>
                    </li>
                    <li>
                        <div class="single-list-topics-content">
                            <div class="single-list-topics-icon">
                                <i class="fa fa-check-square"></i>
                            </div>
                            <h2><a href="#">Quản lý Công việc</a></h2>

                        </div>
                    </li>
                    <li>
                        <div class="single-list-topics-content">
                            <div class="single-list-topics-icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <h2><a href="#">Cộng tác Nhóm</a></h2>

                        </div>
                    </li>
                    <li>
                        <div class="single-list-topics-content">
                            <div class="single-list-topics-icon">
                                <i class="fa fa-line-chart"></i>
                            </div>
                            <h2><a href="#">Theo dõi Tiến độ</a></h2>

                        </div>
                    </li>
                    <li>
                        <div class="single-list-topics-content">
                            <div class="single-list-topics-icon">
                                <i class="fa fa-cogs"></i>
                            </div>
                            <h2><a href="#">Tùy chỉnh Linh hoạt</a></h2>

                        </div>
                    </li>
                </ul>
            </div>
        </div>

    </section>

    <section id="howitworks" class="works">
        <div class="container">
            <div class="section-header">
                <h2>Cách Hoạt động</h2>
                <p>Bắt đầu với MyApp chỉ trong vài phút</p>
            </div>
            <div class="works-content">
                <div class="row">

                    <div class="col-md-4 col-sm-6">
                        <div class="single-how-works">
                            <div class="single-how-works-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <h2><a href="#">1. Đăng ký & Tạo<span> Bảng</span> Đầu tiên</a></h2>
                            <p>
                                Nhanh chóng đăng ký và thiết lập một bảng cho dự án hoặc luồng công việc nhóm của bạn. Chọn
                                mẫu hoặc bắt đầu mới.
                            </p>
                            <button class="welcome-hero-btn how-work-btn" onclick="window.location.href='#'">
                                tìm hiểu thêm
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="single-how-works">
                            <div class="single-how-works-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h2><a href="#">2. Thêm Công việc &<span> Tổ chức</span> Công việc</a></h2>
                            <p>
                                Tạo thẻ công việc, gán chúng cho thành viên nhóm, đặt hạn chót và thêm chi tiết. Kéo và thả
                                để quản lý tiến độ.
                            </p>
                            <button class="welcome-hero-btn how-work-btn" onclick="window.location.href='#'">
                                tìm hiểu thêm
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="single-how-works">
                            <div class="single-how-works-icon">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <h2><a href="#">3. Cộng tác &<span> Theo dõi</span> Tiến độ</a></h2>
                            <p>
                                Bình luận trên công việc, đính kèm tệp và nhận thông báo. Giám sát hiệu quả luồng công việc
                                và xác định điểm nghẽn.
                            </p>
                            <button class="welcome-hero-btn how-work-btn" onclick="window.location.href='#'">
                                tìm hiểu thêm
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <section id="statistics" class="statistics">
        <div class="container">
            <div class="statistics-counter">
                <div class="col-md-3 col-sm-6">
                    <div class="single-ststistics-box">
                        <div class="statistics-content">
                            <div class="counter">500</div> <span>K+</span>
                        </div>
                        <h3>Công việc đã Quản lý</h3>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="single-ststistics-box">
                        <div class="statistics-content">
                            <div class="counter">10</div> <span>k+</span>
                        </div>
                        <h3>Nhóm đang Hoạt động</h3>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="single-ststistics-box">
                        <div class="statistics-content">
                            <div class="counter">98</div> <span>%</span>
                        </div>
                        <h3>Hài lòng Người dùng</h3>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="single-ststistics-box">
                        <div class="statistics-content">
                            <div class="counter">25</div> <span>%</span>
                        </div>
                        <h3>Tăng Năng suất</h3>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <section id="contact" class="subscription">
        <div class="container">
            <div class="subscribe-title text-center">
                <h2>
                    Sẵn sàng kiểm soát luồng công việc của bạn?
                </h2>
                <p>
                    Đăng ký MyApp ngay hôm nay và bắt đầu quản lý công việc hiệu quả hơn.
                </p>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="subscription-input-group">
                        <input type="email" class="subscription-input-form" id="email"
                            placeholder="Nhập email của bạn tại đây" required>

                        <a class="appsLand-btn subscribe-btn" href="#" onclick="submitEmail()">
                            Đăng ký Miễn phí
                        </a>

                        <script>
                            function submitEmail() {
                                var email = document.getElementById('email').value;
                                var url = '{{ route('register.form') }}';
                                if (email) {
                                    url += '?email=' + encodeURIComponent(email);
                                }
                                window.location.href = url;
                            }
                        </script>

                    </div>
                    <!-- Tùy chọn: Thêm liên kết đến chính sách bảo mật/điều khoản -->
                    <!-- <p class="text-center" style="margin-top:15px; font-size: 12px;"><a href="#">Điều khoản Dịch vụ</a> | <a href="#">Chính sách Bảo mật</a></p> -->
                </div>
            </div>
        </div>

    </section>

    <footer id="footer" class="footer">
        <div class="container">
            <div class="footer-menu">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="navbar-header">
                            <a class="navbar-brand" href="index.html">My<span>App</span></a>
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <ul class="footer-menu-item">
                            <li class="scroll"><a href="#features">Tính năng</a></li>
                            <li class="scroll"><a href="#howitworks">Cách hoạt động</a></li>
                            {{-- <li class="scroll"><a href="#reviews">Đánh giá</a></li> --}}
                            <li><a href="{{ route('login.form') }}">Đăng nhập</a></li>
                            <li><a href="{{ route('register.form') }}">Đăng ký</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="hm-footer-copyright">
                <div class="row">
                    <div class="col-sm-5">
                        <p>
                            © 2025 MyApp
                        </p>
                    </div>
                    <div class="col-sm-7">
                        <div class="footer-social">
                            <span><i class="fa fa-envelope"></i> support@myapp.com</span>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div id="scroll-Top">
            <div class="return-to-top">
                <i class="fa fa-angle-up " id="scroll-top" data-toggle="tooltip" data-placement="top" title=""
                    data-original-title="Lên đầu trang" aria-hidden="true"></i>
            </div>

        </div>

    </footer>

@endsection
