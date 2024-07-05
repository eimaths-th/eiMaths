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
        var savedYear = localStorage.getItem('Yearsave');
        var savedMonth = localStorage.getItem('Monthsave');
        var savedSearchCentre = localStorage.getItem('searchCentre');

        // Set field values from local storage
        if (savedYear && savedMonth) {
            currentYear = savedYear;
            currentMonth = savedMonth;
            daterange();
        } else {
            datesearch();
        }
        if (savedSearchCentre) {
            $('#dcentre').val(savedSearchCentre);
        }
    }

    function daterange() {
        if (!currentYear || !currentMonth) {
            datesearch();
        }
        $('#reservation').datepicker({
            dateFormat: 'yy-mm',
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            yearRange: '2023:' + currentYear,
            onClose: function(dateText, inst) {
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                storeFieldValues();
            }
        }).datepicker('setDate', new Date(currentYear, currentMonth, 1));
    }

    // Attach event handler to a button or element to trigger the reset
    $('#resetSearchButton').on('click', async function() {
        localStorage.removeItem('Yearsave');
        localStorage.removeItem('Monthsave');
        localStorage.removeItem('searchCentre');

        // Set field values to empty
        $('#dcentre').val('{{ Auth::user()->department->id }}');
        localStorage.setItem('searchCentre', {{ Auth::user()->department->id }});

        // Set the date range back to its default
        datesearch();
        daterange();
        handle_student_by_level();
        handle_receipt_sum_by_date();
        handle_student_stutus();
        handle_daily_study();
    });


    $('#SearchButtons').on('click', async function() {
        handle_student_by_centre();
        teacher_by_centre();
        study_by_centre();
        handle_student_by_level();
        handle_receipt_sum_by_date();
        handle_student_stutus();
        handle_daily_study();
    });


    function storeFieldValues() {
        var Yearstart = $('#reservation').val();
        var searchCentre = $('#dcentre').val();

        // Split Yearstart into Yearsave and Monthsave
        //var [Yearsave, Monthsave] = Yearstart.split('-');

        // Store values in local storage
        //localStorage.setItem('Yearsave', Yearsave);
        //localStorage.setItem('Monthsave', Monthsave - 1);
        localStorage.setItem('searchCentre', searchCentre);
    }

    $('#dcentre').on('change', storeFieldValues);


    const student_by_level = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const reservationValue = $('#reservation').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.student_by_level') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    reservation: reservationValue,
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
            const reservationValue = $('#reservation').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.student_by_centre') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    reservation: reservationValue,
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
            const reservationValue = $('#reservation').val();

            // console.log(dcentreValue);

            const response = await $.ajax({
                url: '{{ route('dashboard.teacher_by_centre') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    reservation: reservationValue,
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
            const reservationValue = $('#reservation').val();

            // console.log(dcentreValue);

            const response = await $.ajax({
                url: '{{ route('dashboard.study_by_centre') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    reservation: reservationValue,
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
            const reservationValue = $('#reservation').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.receipt_sum_by_date') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    reservation: reservationValue,
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

    const daily_study_date = async () => {
        try {
            const dcentreValue = $('#dcentre').val();
            const reservationValue = $('#reservation').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.daily_study') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    reservation: reservationValue,
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
            const reservationValue = $('#reservation').val();

            const response = await $.ajax({
                url: '{{ route('dashboard.student_status') }}',
                method: 'POST',
                data: {
                    _token: token,
                    dcentre: dcentreValue,
                    reservation: reservationValue,
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
            color: ['#3ba272','#fc8452', '#96BFFF', '#fac858', '#ee6666', '#73c0de', '#5470c6',
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
            // console.log(datas);

            const options = student_status_chart_data(datas);
            var chart_data = echarts.init(document.getElementById("student_status"));
            chart_data.setOption(options);
            window.addEventListener('resize', chart_data.resize);
        } catch (error) {
            console.error('Error:', error);
        }
    };


    $(document).ready(() => {

        retrieveFieldValues();
        daterange();

        handle_student_by_level();
        handle_receipt_sum_by_date();
        handle_daily_study();
        handle_student_by_centre();
        handle_student_stutus();
        teacher_by_centre();
        study_by_centre();


    });
</script>
