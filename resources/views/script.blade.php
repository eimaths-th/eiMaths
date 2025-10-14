<script language="javascript" type="text/javascript">
    $.datepicker.setDefaults($.datepicker.regional['en']);
    var token = ''
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let currentYear, currentMonth; // Declare variables globally

    function datesearch() {
        currentYear = new Date().getFullYear();
        currentMonth = new Date().getMonth();
    }

    function retrieveFieldValues() {
        var savedDateRange = localStorage.getItem('savedDateRange');
        var savedStartDate = localStorage.getItem('savedStartDate');
        var savedEndDate = localStorage.getItem('savedEndDate');
        var savedYears = localStorage.getItem('savedYears');
        var savedSearchCentre = localStorage.getItem('searchCentre');

        // Set field values from local storage
        if (savedDateRange && savedStartDate && savedEndDate) {
            $('#daterange').val(savedDateRange);
            $('#daterange').data('start-date', savedStartDate);
            $('#daterange').data('end-date', savedEndDate);
        }
        if (savedYears) {
            var years = JSON.parse(savedYears);
            $('#year_filter').val(years).trigger('change');
        }
        if (savedSearchCentre) {
            $('#dcentre').val(savedSearchCentre);
        }
    }

    function InitializeDateRangePicker() {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            showDropdowns: true,
            minYear: 2020,
            maxYear: parseInt(moment().format('YYYY'), 10) + 1,
            opens: 'left',
            drops: 'down',
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'ตกลง',
                cancelLabel: 'ล้าง',
                fromLabel: 'จาก',
                toLabel: 'ถึง',
                customRangeLabel: 'กำหนดเอง',
                weekLabel: 'W',
                daysOfWeek: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                monthNames: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ],
                firstDay: 0
            },
            ranges: {
                'วันนี้': [moment(), moment()],
                'เมื่อวาน': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 วันที่แล้ว': [moment().subtract(6, 'days'), moment()],
                '30 วันที่แล้ว': [moment().subtract(29, 'days'), moment()],
                'เดือนนี้': [moment().startOf('month'), moment().endOf('month')],
                'เดือนที่แล้ว': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'ปีนี้': [moment().startOf('year'), moment().endOf('year')],
                'ปีที่แล้ว': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        });

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            const displayFormat = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
            $(this).val(displayFormat);
            // Store in YYYY-MM-DD format for backend
            $(this).data('start-date', picker.startDate.format('YYYY-MM-DD'));
            $(this).data('end-date', picker.endDate.format('YYYY-MM-DD'));
            
            // Save to localStorage
            localStorage.setItem('savedDateRange', displayFormat);
            localStorage.setItem('savedStartDate', picker.startDate.format('YYYY-MM-DD'));
            localStorage.setItem('savedEndDate', picker.endDate.format('YYYY-MM-DD'));
            
            StoreFieldValues();
        });

        $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $(this).removeData('start-date');
            $(this).removeData('end-date');
            
            // Remove from localStorage
            localStorage.removeItem('savedDateRange');
            localStorage.removeItem('savedStartDate');
            localStorage.removeItem('savedEndDate');
            
            StoreFieldValues();
        });
    }

    function InitializeSelect2() {
        $('#year_filter').select2({
            placeholder: 'Select Years',
            allowClear: true,
            width: '100%'
        });

        $('#year_filter').on('change', function() {
            StoreFieldValues();
        });
    }

    // Attach event handler to a button or element to trigger the reset
    $('#resetSearchButton').on('click', async function() {
        localStorage.removeItem('savedDateRange');
        localStorage.removeItem('savedStartDate');
        localStorage.removeItem('savedEndDate');
        localStorage.removeItem('savedYears');
        localStorage.removeItem('searchCentre');

        // Set field values to current month
        $('#dcentre').val('{{ Auth::user()->department->id }}');
        localStorage.setItem('searchCentre', {{ Auth::user()->department->id }});
        
        // Set to current month (first day to last day)
        const startOfMonth = moment().startOf('month');
        const endOfMonth = moment().endOf('month');
        const displayFormat = startOfMonth.format('DD/MM/YYYY') + ' - ' + endOfMonth.format('DD/MM/YYYY');
        
        $('#daterange').val(displayFormat);
        $('#daterange').data('start-date', startOfMonth.format('YYYY-MM-DD'));
        $('#daterange').data('end-date', endOfMonth.format('YYYY-MM-DD'));
        
        // Save to localStorage
        localStorage.setItem('savedDateRange', displayFormat);
        localStorage.setItem('savedStartDate', startOfMonth.format('YYYY-MM-DD'));
        localStorage.setItem('savedEndDate', endOfMonth.format('YYYY-MM-DD'));
        
        $('#year_filter').val(null).trigger('change');

        // Show Monthly/Yearly sections since we have date filter
        $('#monthly_receipt_section').slideDown();
        $('#yearly_receipt_section').slideDown();

        // Refresh dashboard with all charts
        await Promise.all([
            handle_student_by_level(),
            handle_receipt_sum_by_date(),
            handle_student_stutus(),
            handle_daily_study(),
            handle_student_by_centre(),
            teacher_by_centre(),
            study_by_centre(),
            handle_receipt_sum_by_month(),
            handle_receipt_sum_by_year(),
        ]);
    });


    // Function to check if filters are applied
    function CheckFiltersApplied() {
        const $daterange = $('#daterange');
        const yearsValue = $('#year_filter').val();
        
        const hasDateRange = $daterange.data('start-date') && $daterange.data('end-date');
        const hasYears = yearsValue && yearsValue.length > 0;
        
        return hasDateRange || hasYears;
    }

    // Function to toggle Monthly/Yearly sections
    function ToggleReceiptSections() {
        const showAdvanced = CheckFiltersApplied();
        
        if (showAdvanced) {
            $('#monthly_receipt_section').slideDown();
            $('#yearly_receipt_section').slideDown();
        } else {
            $('#monthly_receipt_section').slideUp();
            $('#yearly_receipt_section').slideUp();
        }
    }

    $('#SearchButtons').on('click', async function() {
        ToggleReceiptSections();
        
        const showAdvanced = CheckFiltersApplied();
        
        const promises = [
            student_by_centre(),
            teacher_by_centre(),
            study_by_centre(),
            handle_student_by_level(),
            handle_receipt_sum_by_date(),
            handle_student_stutus(),
            handle_daily_study(),
        ];
        
        if (showAdvanced) {
            promises.push(handle_receipt_sum_by_month());
            promises.push(handle_receipt_sum_by_year());
        }
        
        await Promise.all(promises);
    });


    function StoreFieldValues() {
        var dateRange = $('#daterange').val();
        var years = $('#year_filter').val();
        var searchCentre = $('#dcentre').val();

        // Store values in local storage
        localStorage.setItem('savedDateRange', dateRange);
        localStorage.setItem('savedYears', JSON.stringify(years));
        localStorage.setItem('searchCentre', searchCentre);
    }

    $('#dcentre').on('change', StoreFieldValues);


    const student_by_level = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            // Get date range in YYYY-MM-DD format for backend
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.student_by_level') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });

            // Process the response as needed
            return response.level_data;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    };

    const student_by_level_chart_data = (data) => {
        const labels = Object.keys(data);
        const values = Object.values(data);

        const dataArray = labels.map(label => ({
            value: data[label],
            name: label,
            selected: true
        }));

        // Find the item with the maximum value
        const maxItem = dataArray.reduce((max, current) => (current.value > max.value ? current : max), dataArray[
            0]);

        // Update the 'selected' property for the maxItem
        dataArray.forEach(item => {
            item.selected = item === maxItem;
        });

        const option = {
            title: {
                show: false,
                text: 'Referer of a Website',
                subtext: 'Fake Data',
                left: 'center'
            },
            tooltip: {
                trigger: 'item'
            },
            toolbox: {
                show: true,
                feature: {
                    saveAsImage: {}
                }
            },
            legend: {
                top: '5%',
                left: 'center',
                data: labels.map(String)
            },
            series: [{
                name: 'Level',
                type: 'pie',
                selectedMode: 'single',
                radius: '60%',
                center: ['50%', '45%'],
                data: dataArray,
                color: ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452',
                    '#9a60b4', '#ea7ccc', '#91c7ae',
                    '#fb7293',
                    '#96BFFF',
                    '#bda29a',
                ],
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 20,
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: true
                },
            }]
        };


        /* const option = {
            tooltip: {
                trigger: 'item'
            },
            toolbox: {
                show: true,
                feature: {
                    saveAsImage: {}
                }
            },
            legend: {
                show: true,
                type: 'scroll',
                orient: 'vertical',
                right: 10,
                top: 30,
                bottom: 20,
                data: labels.map(String)
            },
            series: [{
                name: 'Level',
                type: 'pie',
                radius: ['45%', '80%'],
                center: ['40%', '40%'],
                avoidLabelOverlap: true,
                label: {
                    show: true,
                    position: 'inner',
                    fontSize: 10,
                    color: '#ffffff',
                    formatter(param) {
                        return  ' (' + param.percent * 2 + '%)';
                    }
                },
                color: ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452',
                    '#9a60b4', '#ea7ccc', '#91c7ae',
                    '#fb7293',
                    '#96BFFF',
                    '#bda29a',
                ],
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 20,
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: true
                },
                data: labels.map(label => ({
                    value: data[label],
                    name: label
                })),
            }]
        }; */

        return option;
    };

    const handle_student_by_level = async () => {
        try {
            const datas = await student_by_level();
            const options = student_by_level_chart_data(datas);
            var chart_data = echarts.init(document.getElementById("chart_student_by_level"));
            chart_data.setOption(options);
            window.addEventListener('resize', chart_data.resize);
        } catch (error) {
            console.error('Error:', error);
        }
    };

    const student_by_centre = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.student_by_centre') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });

            $('#tstudent').html(response.total_student)
            $('#tcentre').html(response.total_centre)
            // Process the response as needed
            return response.centre_data;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    };


    const teacher_by_centre = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.teacher_by_centre') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });

            $('#tteacher').html(response.total_teacher)
            // Process the response as needed
            return response.centre_data;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    };


    const study_by_centre = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.study_by_centre') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });

            $('#tstudy').html(response.total_study)
            // Process the response as needed
            return response.centre_data;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    };

    const student_by_centre_chart_data = (data) => {
        const labels = Object.keys(data);
        const values = Object.values(data);

        const dataArray = labels.map(label => ({
            value: data[label],
            name: label,
            selected: true
        }));


        //$('#tcentre').html(count(dataArray))

        // Find the item with the maximum value
        const maxItem = dataArray.reduce((max, current) => (current.value > max.value ? current : max), dataArray[
            0]);

        // Update the 'selected' property for the maxItem
        dataArray.forEach(item => {
            item.selected = item === maxItem;
        });

        const option = {
            title: {
                show: false,
                text: 'Referer of a Website',
                subtext: 'Fake Data',
                left: 'center'
            },
            tooltip: {
                trigger: 'item'
            },
            toolbox: {
                show: true,
                feature: {
                    saveAsImage: {}
                }
            },
            legend: {
                top: '5%',
                left: 'center',
                data: labels.map(String)
            },
            series: [{
                name: 'Centre',
                type: 'pie',
                selectedMode: 'single',
                radius: '60%',
                center: ['50%', '45%'],
                data: dataArray,
                color: ['#fc8452', '#96BFFF', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#5470c6',
                    '#9a60b4', '#ea7ccc', '#91c7ae',
                    '#fb7293',
                    '#91cc75',
                    '#bda29a',
                ],
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 20,
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: true
                },
            }]
        };

        return option;
    };

    const handle_student_by_centre = async () => {
        try {
            const datas = await student_by_centre();

            const options = student_by_centre_chart_data(datas);
            var chart_data = echarts.init(document.getElementById("student_by_centre"));
            chart_data.setOption(options);
            window.addEventListener('resize', chart_data.resize);
        } catch (error) {
            console.error('Error:', error);
        }
    };




    const receipt_sum_by_date = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.receipt_sum_by_date') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });

            $('#treceipt').html(response.total_receipt)
            $('#pareceipt').html(response.total_receipt_pay)
            $('#preceipt').html(response.total_receipt_pen)
            $('#tincome').html(response.total_income)
            // Process the response as needed
            return response.receipt_sum_by_date;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    };

    const receipt_sum_by_date_chart_data = (data) => {
        const labels = data.map(entry => entry.payment_date);
        const totalReceiptData = data.map(entry => entry.total_transactions);
        const totalPriceData = data.map(entry => entry.total_fee_sum);

        const option = {
            series: [{
                    name: 'Total Receipt',
                    type: 'line',
                    data: totalReceiptData,
                },
                {
                    name: 'Total Income',
                    type: 'column',
                    data: totalPriceData,
                },
            ],
            markers: {
                size: 5,
                colors: ["#FFFFFF"],
                strokeColor: "#A5978B",
                strokeWidth: 4
            },
            chart: {
                type: 'line',
                height: 380,
                zoom: {
                    enabled: false
                }, //
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -18,
                enabledOnSeries: [1]
            },
            stroke: {
                curve: 'straight',
                width: 4
            },
            colors: ['#E91E63', '#81D4FA', '#546E7A', '#E91E63', '#FF9800', '#66DA26', '#4ECDC4', '#91cc75',
                '#A5978B', '#FD6A6A'
            ],
            title: {
                //text: 'สถิติการเข้าชม รายวัน ประจำเดือน 2023-06',
                align: 'left'
            },
            subtitle: {
                //text: 'จำนวน',
                align: 'left'
            },
            xaxis: {
                labels: {
                    show: true,
                    rotate: -30,
                    rotateAlways: true,
                    maxHeight: 300,
                },
                categories: labels,
            },
            yaxis: [{
                /* title: {
                    text: 'Website Blog',
                }, */

            }, {
                opposite: true,
                /* title: {
                    text: 'Social Media'
                } */
            }],
            legend: {
                horizontalAlign: 'left'
            }
        };

        return option;
    };

    const handle_receipt_sum_by_date = async () => {
        try {
            const datas = await receipt_sum_by_date();

            const options = receipt_sum_by_date_chart_data(datas);
            var chart_data = new ApexCharts(document.querySelector("#receipt_sum_by_date"), options);
            chart_data.render();
            chart_data.updateOptions(options);
        } catch (error) {
            console.error('Error:', error);
        }
    };

    // Monthly Receipt Functions
    const receipt_sum_by_month = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.receipt_sum_by_month') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });

            return response.receipt_sum_by_month;
        } catch (error) {
            console.error('Error fetching monthly data:', error);
            throw error;
        }
    };

    const receipt_sum_by_month_chart_data = (data) => {
        const labels = data.map(entry => entry.month);
        const totalReceiptData = data.map(entry => entry.total_transactions);
        const totalPriceData = data.map(entry => entry.total_fee_sum);

        const option = {
            series: [{
                    name: 'Total Receipt',
                    type: 'line',
                    data: totalReceiptData,
                },
                {
                    name: 'Total Income',
                    type: 'column',
                    data: totalPriceData,
                },
            ],
            markers: {
                size: 5,
                colors: ["#FFFFFF"],
                strokeColor: "#28a745",
                strokeWidth: 4
            },
            chart: {
                type: 'line',
                height: 380,
                zoom: {
                    enabled: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -18,
                enabledOnSeries: [1]
            },
            stroke: {
                curve: 'smooth',
                width: 4
            },
            colors: ['#28a745', '#20c997'],
            title: {
                align: 'left'
            },
            xaxis: {
                labels: {
                    show: true,
                    rotate: -30,
                    rotateAlways: true,
                },
                categories: labels,
            },
            yaxis: [{
                title: {
                    text: 'Total Receipt',
                },
            }, {
                opposite: true,
                title: {
                    text: 'Total Income (฿)'
                }
            }],
        };
        return option;
    };

    const handle_receipt_sum_by_month = async () => {
        try {
            const datas = await receipt_sum_by_month();

            const options = receipt_sum_by_month_chart_data(datas);
            var chart_data = new ApexCharts(document.querySelector("#receipt_sum_by_month"), options);
            chart_data.render();
            chart_data.updateOptions(options);
        } catch (error) {
            console.error('Error:', error);
        }
    };

    // Yearly Receipt Functions
    const receipt_sum_by_year = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.receipt_sum_by_year') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });

            return response.receipt_sum_by_year;
        } catch (error) {
            console.error('Error fetching yearly data:', error);
            throw error;
        }
    };

    const receipt_sum_by_year_chart_data = (data) => {
        const labels = data.map(entry => entry.year);
        const totalReceiptData = data.map(entry => entry.total_transactions);
        const totalPriceData = data.map(entry => entry.total_fee_sum);

        const option = {
            series: [{
                    name: 'Total Receipt',
                    type: 'line',
                    data: totalReceiptData,
                },
                {
                    name: 'Total Income',
                    type: 'column',
                    data: totalPriceData,
                },
            ],
            markers: {
                size: 6,
                colors: ["#FFFFFF"],
                strokeColor: "#17a2b8",
                strokeWidth: 4
            },
            chart: {
                type: 'line',
                height: 380,
                zoom: {
                    enabled: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -18,
                enabledOnSeries: [1]
            },
            stroke: {
                curve: 'smooth',
                width: 4
            },
            colors: ['#17a2b8', '#5bc0de'],
            title: {
                align: 'left'
            },
            xaxis: {
                labels: {
                    show: true,
                },
                categories: labels,
            },
            yaxis: [{
                title: {
                    text: 'Total Receipt',
                },
            }, {
                opposite: true,
                title: {
                    text: 'Total Income (฿)'
                }
            }],
        };
        return option;
    };

    const handle_receipt_sum_by_year = async () => {
        try {
            const datas = await receipt_sum_by_year();

            const options = receipt_sum_by_year_chart_data(datas);
            var chart_data = new ApexCharts(document.querySelector("#receipt_sum_by_year"), options);
            chart_data.render();
            chart_data.updateOptions(options);
        } catch (error) {
            console.error('Error:', error);
        }
    };

    const daily_study_date = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.daily_study') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });

            // Process the response as needed
            return response.daily_study;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    };

    const daily_study_chart_data = (data) => {
        const labels = data.map(entry => entry.date);
        const totalData = data.map(entry => entry.total_transactions);


        const option = {
            series: [{
                name: 'Total Study',
                type: 'area',
                data: totalData,
            }, ],
            markers: {
                size: 5,
                colors: ["#FFFFFF"],
                strokeColor: "#A5978B",
                strokeWidth: 4
            },
            chart: {
                type: 'area',
                height: 380,
                zoom: {
                    enabled: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            dataLabels: {
                enabled: false,
                offsetY: -18,
                enabledOnSeries: [1]
            },
            stroke: {
                curve: 'straight',
                width: 4
            },
            colors: ['#FF9800', '#E91E63', '#91cc75', '#546E7A', '#E91E63', '#81D4FA', '#66DA26', '#4ECDC4',
                '#A5978B', '#FD6A6A'
            ],
            title: {
                //text: 'สถิติการเข้าชม รายวัน ประจำเดือน 2023-06',
                align: 'left'
            },
            subtitle: {
                //text: 'จำนวน',
                align: 'left'
            },
            xaxis: {
                labels: {
                    show: true,
                    rotate: -30,
                    rotateAlways: true,
                    maxHeight: 300,
                },
                categories: labels,
            },
            yaxis: [{
                /* title: {
                    text: 'Website Blog',
                }, */

            }, {
                opposite: true,
                /* title: {
                    text: 'Social Media'
                } */
            }],
            legend: {
                horizontalAlign: 'left'
            }
        };

        return option;
    };

    const handle_daily_study = async () => {
        try {
            const datas = await daily_study_date();
            const options = daily_study_chart_data(datas);
            var chart_data = new ApexCharts(document.querySelector("#daily_study"), options);
            chart_data.render();
            chart_data.updateOptions(options);
        } catch (error) {
            console.error('Error:', error);
        }
    };


    const student_status = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const $daterange = $('#daterange');
            let dateRangeValue = '';
            
            if ($daterange.data('start-date') && $daterange.data('end-date')) {
                dateRangeValue = $daterange.data('start-date') + ' - ' + $daterange.data('end-date');
            }
            
            const yearsValue = $('#year_filter').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.student_status') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    date_range: dateRangeValue,
                    years: yearsValue,
                },
            });


            // Process the response as needed
            return response.student_status;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    };

    const student_status_chart_data = (data) => {
        const labels = data.map(entry => entry.centre_name);
        const total = data.map(entry => entry.count);
        const totaldis = data.map(entry => entry.count_dis);
        const totalgra = data.map(entry => entry.count_gradute);

        const option = {
            height: 300,
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    // Use axis to trigger tooltip
                    type: 'shadow' // 'shadow' as default; can also be 'line' or 'shadow'
                }
            },
            legend: {},
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'value'
            },
            yAxis: {
                type: 'category',
                data: labels
            },
            color: ['#3ba272', '#fc8452', '#96BFFF', '#fac858', '#ee6666', '#73c0de', '#5470c6',
                '#9a60b4', '#ea7ccc', '#91c7ae',
                '#fb7293',
                '#91cc75',
                '#bda29a',
            ],
            series: [{
                    name: 'Active Student',
                    type: 'bar',
                    stack: 'total',
                    label: {
                        show: true
                    },
                    emphasis: {
                        focus: 'series'
                    },
                    data: total
                },
                {
                    name: 'Discontinue Student',
                    type: 'bar',
                    stack: 'total',
                    label: {
                        show: true
                    },
                    emphasis: {
                        focus: 'series'
                    },
                    data: totaldis
                },
                /* {
                    name: 'Total Graduated',
                    type: 'bar',
                    stack: 'total',
                    label: {
                        show: true
                    },
                    emphasis: {
                        focus: 'series'
                    },
                    data: totalgra
                }, */

            ]
        };

        return option;
    };

    const handle_student_stutus = async () => {
        try {
            const datas = await student_status();

            const options = student_status_chart_data(datas);
            var chart_data = echarts.init(document.getElementById("student_status"));
            chart_data.setOption(options);
            window.addEventListener('resize', chart_data.resize);
        } catch (error) {
            console.error('Error:', error);
        }
    };


    $(document).ready(() => {

        InitializeDateRangePicker();
        InitializeSelect2();
        retrieveFieldValues();

        // Check if filters are applied on page load
        const showAdvanced = CheckFiltersApplied();
        
        handle_student_by_level();
        handle_receipt_sum_by_date();
        handle_daily_study();
        handle_student_by_centre();
        handle_student_stutus();
        teacher_by_centre();
        study_by_centre();

        // Load Monthly/Yearly if filters are applied
        if (showAdvanced) {
            $('#monthly_receipt_section').show();
            $('#yearly_receipt_section').show();
            handle_receipt_sum_by_month();
            handle_receipt_sum_by_year();
        }

    });
</script>
<script>
    $(document).ready(function() {
        const dcentre = $('#dcentre');
        const daterange = $('#daterange');
        let centre = dcentre.val();
        let dateRangeValue = daterange.val();
        // Determine if current user is SystemAdmin (safe checks without assuming role system)
        const IS_SYSTEM_ADMIN = @json(auth()->check() && (function() {
            $u = Auth::user();
            if (!$u) return false;
            if (method_exists($u, 'hasRole')) {
                return $u->hasRole('SystemAdmin');
            }
            // Fallbacks if hasRole is unavailable
            if (isset($u->role) && is_string($u->role)) {
                return $u->role === 'SystemAdmin';
            }
            return false;
        })());

        // Define routes and element selectors in one place
        const routes = [
            {
                selector: '#contacts-link',
                route: "{{ route('contacts') }}",
                params: ['centre', 'date']
            },
            {
                selector: '#teacher-link',
                route: "{{ route('users.index') }}",
                params: ['role=Teacher', 'centre', 'date']
            },
            {
                selector: '.receipt-link',
                route: "{{ route('receipts') }}",
                params: ['centre', 'date']
            },
            {
                selector: '#receipts-pending-link',
                route: "{{ route('receipts_pending') }}",
                params: ['centre', 'date']
            },
        ];

        function updateLinks(centre, reservationValue) {
            routes.forEach(item => {
                let url = new URL(item.route);

                // Add parameters to URL
                item.params.forEach(param => {
                    if (param.includes('=')) {
                        // Handle parameters with static values (like role=Teacher)
                        const [key, value] = param.split('=');
                        url.searchParams.set(key, value);
                    } else if (param === 'centre' && centre) {
                        url.searchParams.set('centre', centre);
                    } else if (param === 'date' && dateRangeValue) {
                        url.searchParams.set('date', dateRangeValue);
                    }
                });

                // Update the href attribute with the properly encoded URL
                $(item.selector).attr('href', url.toString());
            });
        }

        updateLinks(centre, dateRangeValue);

        dcentre.on('change', function() {
            centre = $(this).val();
            updateLinks(centre, dateRangeValue);
        });

        daterange.on('change', function() {
            dateRangeValue = $(this).val();
            updateLinks(centre, dateRangeValue);
        });
    });
</script>
