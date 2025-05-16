@extends('layouts.admin')

@section('title', 'Thống kê người dùng')

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr locale tiếng Việt -->

<style>
    #userRegistrationChartContainer {
        position: relative;
        height: 60vh;
        width: 100%;
    }

    canvas {
        display: block;
        width: 100% !important;
        height: auto !important;
    }
</style>

@section('content')
    <h3>Thống kê người dùng đã đăng ký</h3>

    <div class="mb-3">
        <label for="dateRange">Chọn khoảng thời gian</label>
        <input type="text" id="dateRange" class="form-control" placeholder="Chọn khoảng thời gian">
    </div>

    <div id="userRegistrationChartContainer">
        <canvas id="userRegistrationChart"></canvas>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            // console.error("Chart.js chưa được load!");
            return;
        }

        const dateRangeInput = document.getElementById('dateRange');
        const chartCanvas = document.getElementById('userRegistrationChart');
        let userChart;

        function fetchChartData(dateRangeStr) {
            if (!dateRangeStr) {
                if (userChart) {
                    userChart.destroy();
                    userChart = null;
                }
                chartCanvas.style.display = 'none';
                return;
            }
            chartCanvas.style.display = 'block';

            const url = `{{ route('admin.dashboard.user-registrations') }}?date_range=${encodeURIComponent(dateRangeStr)}`;
            fetch(url)
                .then(response => response.ok ? response.json() : Promise.reject(response))
                .then(data => {
                    if (userChart) userChart.destroy();
                    if (data.labels && data.datasets) {
                        userChart = new Chart(chartCanvas.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: data.datasets.map(dataset => ({
                                    label: dataset.label || 'Đăng ký',
                                    data: dataset.data,
                                    borderColor: dataset.borderColor || `rgb(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)})`,
                                    tension: 0.1,
                                    fill: false
                                }))
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        type: 'time',
                                        time: {
                                            unit: 'day',
                                            tooltipFormat: 'PPP',
                                            displayFormats: { day: 'P' }
                                        },
                                        title: { display: true, text: 'Ngày' }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        title: { display: true, text: 'Số lượng đăng ký' }
                                    }
                                },
                                plugins: {
                                    tooltip: { mode: 'index', intersect: false },
                                    legend: { position: 'top' }
                                }
                            }
                        });
                    } else {
                        chartCanvas.style.display = 'none';
                        alert("Không có dữ liệu để hiển thị.");
                    }
                })
                .catch(error => {
                    chartCanvas.style.display = 'none';
                    // console.error("Lỗi khi lấy dữ liệu biểu đồ:", error);
                    // alert("Lỗi khi tải dữ liệu biểu đồ. Kiểm tra console.");
                });
        }

        const today = new Date();
        const thirtyDaysAgo = new Date(new Date().setDate(today.getDate() - 30));

        flatpickr(dateRangeInput, {
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: [thirtyDaysAgo, today],
            maxDate: "today",
            locale: "vi",
            onReady: (selectedDates, dateStr) => {
                if (dateStr) fetchChartData(dateStr);
                else chartCanvas.style.display = 'none';
            },
            onClose: (selectedDates, dateStr) => {
                if (dateStr) fetchChartData(dateStr);
                else {
                    if (userChart) userChart.destroy();
                    chartCanvas.style.display = 'none';
                }
            }
        });
    });
</script>