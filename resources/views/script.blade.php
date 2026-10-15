<script type="module">
    import { CreateDashboardLoader } from "{{ asset('js/dashboard-loader.js') }}?v={{ filemtime(public_path('js/dashboard-loader.js')) }}";

    $.datepicker.setDefaults($.datepicker.regional['en']);
    var token = ''
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const dashboardCalendar = @json(__('common.calendar'));
    const dashboardDateRanges = @json(__('dashboard.date_ranges'));
    const dashboardSeries = @json(__('dashboard.series'));
    const dashboardLoading = @json(__('dashboard.loading'));

    let currentYear, currentMonth; // Declare variables globally

    function datesearch() {
        currentYear = new Date().getFullYear();
        currentMonth = new Date().getMonth();
    }

    const dashboardChartRegistry = {};
    const dashboardObservedChartElements = new WeakSet();
    let dashboardChartResizeTimer;
    const dashboardChartResizeObserver = typeof ResizeObserver === 'function'
        ? new ResizeObserver(function(entries) {
            entries.forEach(function(entry) {
                const chartEntry = dashboardChartRegistry[entry.target.id];

                if (!chartEntry || chartEntry.type !== 'echarts' || !entry.contentRect.width || !entry.contentRect.height) {
                    return;
                }

                chartEntry.instance.resize({
                    width: Math.floor(entry.contentRect.width),
                    height: Math.floor(entry.contentRect.height),
                });
            });
        })
        : null;

    function RegisterDashboardChart(elementId, type, instance, defaultChartHeight) {
        const element = document.getElementById(elementId);
        const existingEntry = dashboardChartRegistry[elementId];

        dashboardChartRegistry[elementId] = {
            type: type,
            instance: instance,
            defaultChartHeight: existingEntry ? existingEntry.defaultChartHeight : defaultChartHeight,
            defaultContainerHeight: existingEntry ? existingEntry.defaultContainerHeight : element.style.height,
        };

        if (dashboardChartResizeObserver && !dashboardObservedChartElements.has(element)) {
            dashboardObservedChartElements.add(element);
            dashboardChartResizeObserver.observe(element);
        }
    }

    function RenderEChartsDashboardChart(elementId, options) {
        const element = document.getElementById(elementId);
        const existingEntry = dashboardChartRegistry[elementId];
        const chart = existingEntry && existingEntry.type === 'echarts'
            ? existingEntry.instance
            : (echarts.getInstanceByDom(element) || echarts.init(element));

        // The dashboard is revealed as one completed view, without replaying
        // individual chart entrance animations after the skeleton disappears.
        chart.setOption({ ...options, animation: false }, true);
        RegisterDashboardChart(elementId, 'echarts', chart, element.getBoundingClientRect().height || 380);

        const cardElement = element.closest('.card');

        if (cardElement && cardElement.classList.contains('maximized-card')) {
            ResizeDashboardCardCharts(cardElement, true);
        }
    }

    function RenderApexDashboardChart(elementId, options) {
        const element = document.getElementById(elementId);
        const existingEntry = dashboardChartRegistry[elementId];
        const defaultChartHeight = Number(options.chart && options.chart.height) || 380;

        options = {
            ...options,
            chart: { ...options.chart, animations: { enabled: false } },
        };
        let renderPromise;
        let chart;

        if (existingEntry && existingEntry.type === 'apex') {
            chart = existingEntry.instance;
            renderPromise = chart.updateOptions(options, false, false);
            RegisterDashboardChart(elementId, 'apex', existingEntry.instance, defaultChartHeight);
        } else {
            chart = new ApexCharts(element, options);
            renderPromise = chart.render();
            RegisterDashboardChart(elementId, 'apex', chart, defaultChartHeight);
        }

        const cardElement = element.closest('.card');

        return Promise.resolve(renderPromise).then(function() {
            if (cardElement && cardElement.classList.contains('maximized-card')) {
                ResizeDashboardCardCharts(cardElement, true);
            }
        });
    }

    function GetMaximizedChartHeight(cardElement) {
        const header = cardElement.querySelector('.card-header');
        const body = cardElement.querySelector('.card-body');
        const footer = cardElement.querySelector('.card-footer');
        const bodyStyle = window.getComputedStyle(body);
        const cardHeight = cardElement.getBoundingClientRect().height || window.innerHeight;
        const verticalPadding = parseFloat(bodyStyle.paddingTop || 0) + parseFloat(bodyStyle.paddingBottom || 0);

        return Math.max(
            320,
            Math.floor(
                cardHeight
                - (header ? header.getBoundingClientRect().height : 0)
                - (footer ? footer.getBoundingClientRect().height : 0)
                - verticalPadding
                - 16
            )
        );
    }

    function ResizeDashboardCardCharts(cardElement, isMaximized) {
        if (!cardElement) {
            return;
        }

        const maximizedHeight = isMaximized ? GetMaximizedChartHeight(cardElement) : null;

        Object.entries(dashboardChartRegistry).forEach(function([elementId, chartEntry]) {
            const element = document.getElementById(elementId);

            if (!element || element.closest('.card') !== cardElement) {
                return;
            }

            if (isMaximized) {
                element.style.height = maximizedHeight + 'px';
            } else if (chartEntry.defaultContainerHeight) {
                element.style.height = chartEntry.defaultContainerHeight;
            } else {
                element.style.removeProperty('height');
            }

            const chartHeight = isMaximized ? maximizedHeight : chartEntry.defaultChartHeight;

            if (chartEntry.type === 'echarts') {
                const chartWidth = Math.floor(element.getBoundingClientRect().width || element.clientWidth);

                chartEntry.instance.resize({
                    width: chartWidth,
                    height: chartHeight,
                });
            } else {
                chartEntry.instance.updateOptions({
                    chart: {
                        height: chartHeight,
                    },
                }, false, false);
            }
        });
    }

    function InitializeDashboardChartResize() {
        $(document).on('maximized.lte.cardwidget', '[data-card-widget="maximize"]', function(event, card) {
            const cardElement = card && card.length ? card[0] : $(this).closest('.card')[0];

            window.setTimeout(function() {
                ResizeDashboardCardCharts(cardElement, true);
            }, 350);
        });

        $(document).on('minimized.lte.cardwidget', '[data-card-widget="maximize"]', function(event, card) {
            const cardElement = card && card.length ? card[0] : $(this).closest('.card')[0];

            window.setTimeout(function() {
                ResizeDashboardCardCharts(cardElement, false);
            }, 350);
        });

        $(document).on('expanded.lte.cardwidget', '[data-card-widget="collapse"]', function(event, card) {
            const cardElement = card && card.length ? card[0] : $(this).closest('.card')[0];

            window.setTimeout(function() {
                ResizeDashboardCardCharts(cardElement, cardElement.classList.contains('maximized-card'));
            }, 350);
        });

        window.addEventListener('resize', function() {
            window.clearTimeout(dashboardChartResizeTimer);
            dashboardChartResizeTimer = window.setTimeout(function() {
                Object.entries(dashboardChartRegistry).forEach(function([elementId, chartEntry]) {
                    const element = document.getElementById(elementId);
                    const cardElement = element ? element.closest('.card') : null;

                    if (cardElement && cardElement.classList.contains('maximized-card')) {
                        ResizeDashboardCardCharts(cardElement, true);
                    } else if (chartEntry.type === 'echarts') {
                        chartEntry.instance.resize();
                    }
                });
            }, 100);
        });
    }

    function SetDashboardDateRange(startDate, endDate, persist = true) {
        const $daterange = $('#daterange');
        const displayFormat = startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY');
        const backendStartDate = startDate.format('YYYY-MM-DD');
        const backendEndDate = endDate.format('YYYY-MM-DD');
        const picker = $daterange.data('daterangepicker');

        $daterange.val(displayFormat);
        $daterange.data('start-date', backendStartDate);
        $daterange.data('end-date', backendEndDate);

        if (picker) {
            picker.setStartDate(startDate);
            picker.setEndDate(endDate);
        }

        if (persist) {
            localStorage.setItem('savedDateRange', displayFormat);
            localStorage.setItem('savedStartDate', backendStartDate);
            localStorage.setItem('savedEndDate', backendEndDate);
        }
    }

    function retrieveFieldValues() {
        var savedDateRange = localStorage.getItem('savedDateRange');
        var savedStartDate = localStorage.getItem('savedStartDate');
        var savedEndDate = localStorage.getItem('savedEndDate');
        var savedYears = localStorage.getItem('savedYears');
        var savedSearchCentre = localStorage.getItem('searchCentre');
        var parsedStartDate = moment(savedStartDate, 'YYYY-MM-DD', true);
        var parsedEndDate = moment(savedEndDate, 'YYYY-MM-DD', true);

        // Set field values from local storage
        if (savedDateRange && parsedStartDate.isValid() && parsedEndDate.isValid()) {
            SetDashboardDateRange(parsedStartDate, parsedEndDate);
        } else {
            // Keep the visible default and the values sent to the backend in sync.
            SetDashboardDateRange(moment().startOf('year'), moment().endOf('year'));
        }
        if (savedYears) {
            var years = JSON.parse(savedYears);
            $('#year_filter').val(years).trigger('change');
        }
        @if (Auth::user()->isHq())
            if (savedSearchCentre) {
                $('#dcentre').val(savedSearchCentre);
            }
        @else
            $('#dcentre').val('{{ Auth::user()->department_id }}');
            localStorage.setItem('searchCentre', '{{ Auth::user()->department_id }}');
        @endif
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
                format: dashboardCalendar.format,
                separator: dashboardCalendar.separator,
                applyLabel: dashboardCalendar.apply,
                cancelLabel: dashboardCalendar.cancel,
                fromLabel: dashboardCalendar.from,
                toLabel: dashboardCalendar.to,
                customRangeLabel: dashboardCalendar.custom_range,
                weekLabel: dashboardCalendar.week,
                daysOfWeek: dashboardCalendar.days_of_week,
                monthNames: dashboardCalendar.month_names,
                firstDay: dashboardCalendar.first_day
            },
            ranges: {
                [dashboardDateRanges.today]: [moment(), moment()],
                [dashboardDateRanges.yesterday]: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                [dashboardDateRanges.last_7_days]: [moment().subtract(6, 'days'), moment()],
                [dashboardDateRanges.last_30_days]: [moment().subtract(29, 'days'), moment()],
                [dashboardDateRanges.this_month]: [moment().startOf('month'), moment().endOf('month')],
                [dashboardDateRanges.last_month]: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                [dashboardDateRanges.this_year]: [moment().startOf('year'), moment().endOf('year')],
                [dashboardDateRanges.last_year]: [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        });

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            SetDashboardDateRange(picker.startDate, picker.endDate);
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
            placeholder: @json(__('dashboard.filters.select_years')),
            allowClear: true,
            width: '100%'
        });

        $('#year_filter').on('change', function() {
            StoreFieldValues();
        });
    }

    // Attach event handler to a button or element to trigger the reset
    $('#resetSearchButton').on('click', async function() {
        if (dashboardLoader.isLoading()) return;
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
        SetDashboardDateRange(startOfMonth, endOfMonth);
        
        $('#year_filter').val(null).trigger('change');

        await dashboardLoader.load();
    });


    // Function to check if filters are applied
    function CheckFiltersApplied() {
        const $daterange = $('#daterange');
        const yearsValue = $('#year_filter').val();
        
        const hasDateRange = $daterange.data('start-date') && $daterange.data('end-date');
        const hasYears = yearsValue && yearsValue.length > 0;
        
        return hasDateRange || hasYears;
    }

    const dashboardRoot = document.getElementById('dashboard-data');
    const dashboardContent = dashboardRoot.querySelector('[data-dashboard-content]');
    const dashboardStatus = dashboardRoot.querySelector('[role="status"]');
    const dashboardError = document.getElementById('dashboard-load-error');
    const dashboardControls = Array.from(document.querySelectorAll(
        '#dcentre, #daterange, #year_filter, #SearchButtons, #resetSearchButton'
    )).map(element => ({ element, originallyDisabled: element.disabled }));

    function SetDashboardLoadState(state) {
        const loading = state === 'loading';
        const ready = state === 'ready';
        dashboardRoot.dataset.loadState = state;
        dashboardContent.setAttribute('aria-busy', String(loading));
        dashboardContent.setAttribute('aria-hidden', String(!ready));
        dashboardContent.inert = !ready;
        dashboardError.hidden = state !== 'error';
        dashboardStatus.textContent = loading ? dashboardLoading.message : (ready ? dashboardLoading.complete : '');

        // Preserve restrictions such as the locked centre selector for branches.
        dashboardControls.forEach(({ element, originallyDisabled }) => {
            element.disabled = loading || originallyDisabled;
        });
        $('#year_filter').trigger('change.select2');
        document.getElementById('dashboard-retry').disabled = loading;

        if (ready) {
            $(document).trigger('dashboard:loaded');
        }
    }

    const dashboardLoader = CreateDashboardLoader({
        onStateChange: SetDashboardLoadState,
        onError: error => console.error('Dashboard could not finish loading:', error),
        getTasks: () => {
            const showAdvanced = Boolean(CheckFiltersApplied());
            // Keep chart containers measurable before rendering, without slide animations.
            $('#monthly_receipt_section, #yearly_receipt_section').toggle(showAdvanced);

            const tasks = [
                handle_student_by_level,
                handle_student_by_centre,
                handle_student_stutus,
                handle_receipt_sum_by_date,
                handle_daily_study,
                teacher_by_centre,
                study_by_centre,
            ];
            if (showAdvanced) {
                tasks.push(handle_receipt_sum_by_month, handle_receipt_sum_by_year);
            }
            return tasks;
        },
    });

    $('#SearchButtons, #dashboard-retry').on('click', () => dashboardLoader.load());


    function StoreFieldValues() {
        var dateRange = $('#daterange').val();
        var startDate = $('#daterange').data('start-date');
        var endDate = $('#daterange').data('end-date');
        var years = $('#year_filter').val();
        var searchCentre = $('#dcentre').val();

        // Store values in local storage
        localStorage.setItem('savedDateRange', dateRange);
        if (startDate && endDate) {
            localStorage.setItem('savedStartDate', startDate);
            localStorage.setItem('savedEndDate', endDate);
        } else {
            localStorage.removeItem('savedStartDate');
            localStorage.removeItem('savedEndDate');
        }
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
                dataType: 'json',
                timeout: 45000,
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
                name: dashboardSeries.level,
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
            }],
            media: [{
                query: {
                    minWidth: 800,
                    minHeight: 600
                },
                option: {
                    legend: {
                        top: '3%'
                    },
                    series: [{
                        radius: '75%',
                        center: ['50%', '52%']
                    }]
                }
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
                name: dashboardSeries.level,
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
            await RenderEChartsDashboardChart('chart_student_by_level', options);
        } catch (error) {
            throw error;
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
                dataType: 'json',
                timeout: 45000,
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
                dataType: 'json',
                timeout: 45000,
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
                dataType: 'json',
                timeout: 45000,
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
                name: dashboardSeries.centre,
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
            await RenderEChartsDashboardChart('student_by_centre', options);
        } catch (error) {
            throw error;
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
                dataType: 'json',
                timeout: 45000,
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
            $('#tincome').html(FormatDashboardAmount(response.total_income))
            $('#credit-note-gross').html(FormatDashboardAmount(response.total_income))
            $('#credit-note-issued').html(FormatDashboardAmount(response.issued_credit_note_total))
            $('#credit-note-cancelled').html(FormatDashboardAmount(response.cancelled_credit_note_total))
            $('#credit-note-net').html(FormatDashboardAmount(response.net_revenue))
            // Process the response as needed
            return response.receipt_sum_by_date;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    };

    function FormatDashboardAmount(value) {
        const amount = Number(value);

        if (!Number.isFinite(amount)) {
            return '0';
        }

        return amount.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });
    }

    const receipt_sum_by_date_chart_data = (data) => {
        const labels = data.map(entry => entry.payment_date);
        const totalReceiptData = data.map(entry => entry.total_transactions);
        const totalPriceData = data.map(entry => entry.total_fee_sum);
        const issuedCreditNoteData = data.map(entry => entry.issued_credit_note_total || '0.00');
        const cancelledCreditNoteData = data.map(entry => entry.cancelled_credit_note_total || '0.00');
        const netRevenueData = data.map(entry => entry.net_revenue || entry.total_fee_sum || '0.00');
        // Default English chart series retained for the dashboard presentation contract: name: 'Net Revenue'.

        const option = {
            series: [{
                    name: dashboardSeries.total_receipt,
                    type: 'line',
                    data: totalReceiptData,
                },
                {
                    name: dashboardSeries.total_income,
                    type: 'column',
                    data: totalPriceData,
                },
                { name: dashboardSeries.issued_credit_notes, type: 'line', data: issuedCreditNoteData },
                { name: dashboardSeries.cancelled_credit_notes, type: 'line', data: cancelledCreditNoteData },
                { name: dashboardSeries.net_revenue, type: 'line', data: netRevenueData },
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
            await RenderApexDashboardChart('receipt_sum_by_date', options);
        } catch (error) {
            throw error;
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
                dataType: 'json',
                timeout: 45000,
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
        const issuedCreditNoteData = data.map(entry => entry.issued_credit_note_total || '0.00');
        const cancelledCreditNoteData = data.map(entry => entry.cancelled_credit_note_total || '0.00');
        const netRevenueData = data.map(entry => entry.net_revenue || entry.total_fee_sum || '0.00');

        const option = {
            series: [{
                    name: dashboardSeries.total_receipt,
                    type: 'line',
                    data: totalReceiptData,
                },
                {
                    name: dashboardSeries.total_income,
                    type: 'column',
                    data: totalPriceData,
                },
                { name: dashboardSeries.issued_credit_notes, type: 'line', data: issuedCreditNoteData },
                { name: dashboardSeries.cancelled_credit_notes, type: 'line', data: cancelledCreditNoteData },
                { name: dashboardSeries.net_revenue, type: 'line', data: netRevenueData },
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
                    text: dashboardSeries.total_receipt,
                },
            }, {
                opposite: true,
                title: {
                    text: dashboardSeries.total_income + ' (฿)'
                }
            }],
        };
        return option;
    };

    const handle_receipt_sum_by_month = async () => {
        try {
            const datas = await receipt_sum_by_month();

            const options = receipt_sum_by_month_chart_data(datas);
            await RenderApexDashboardChart('receipt_sum_by_month', options);
        } catch (error) {
            throw error;
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
                dataType: 'json',
                timeout: 45000,
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
        const issuedCreditNoteData = data.map(entry => entry.issued_credit_note_total || '0.00');
        const cancelledCreditNoteData = data.map(entry => entry.cancelled_credit_note_total || '0.00');
        const netRevenueData = data.map(entry => entry.net_revenue || entry.total_fee_sum || '0.00');

        const option = {
            series: [{
                    name: dashboardSeries.total_receipt,
                    type: 'line',
                    data: totalReceiptData,
                },
                {
                    name: dashboardSeries.total_income,
                    type: 'column',
                    data: totalPriceData,
                },
                { name: dashboardSeries.issued_credit_notes, type: 'line', data: issuedCreditNoteData },
                { name: dashboardSeries.cancelled_credit_notes, type: 'line', data: cancelledCreditNoteData },
                { name: dashboardSeries.net_revenue, type: 'line', data: netRevenueData },
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
                    text: dashboardSeries.total_receipt,
                },
            }, {
                opposite: true,
                title: {
                    text: dashboardSeries.total_income + ' (฿)'
                }
            }],
        };
        return option;
    };

    const handle_receipt_sum_by_year = async () => {
        try {
            const datas = await receipt_sum_by_year();

            const options = receipt_sum_by_year_chart_data(datas);
            await RenderApexDashboardChart('receipt_sum_by_year', options);
        } catch (error) {
            throw error;
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
                dataType: 'json',
                timeout: 45000,
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
                name: dashboardSeries.total_study,
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
            await RenderApexDashboardChart('daily_study', options);
        } catch (error) {
            throw error;
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
                dataType: 'json',
                timeout: 45000,
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
                    name: dashboardSeries.active_student,
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
                    name: dashboardSeries.discontinued_student,
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
                    name: dashboardSeries.graduated_student,
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
            await RenderEChartsDashboardChart('student_status', options);
        } catch (error) {
            throw error;
        }
    };


    $(document).ready(() => {

        InitializeDashboardChartResize();
        InitializeDateRangePicker();
        InitializeSelect2();
        retrieveFieldValues();

        dashboardLoader.load();

    });

    $(document).ready(function() {
        const dashboard_card_routes = [
            {
                selector: '#contacts-link',
                route: "{{ route('contacts') }}",
            },
            {
                selector: '#teacher-link',
                route: "{{ route('users.index') }}",
                static_params: {
                    role: 'Teacher',
                },
            },
            {
                selector: '.receipt-link',
                route: "{{ route('receipts') }}",
                receipt_status: true,
            },
            {
                selector: '#receipts-pending-link',
                route: "{{ route('receipts_pending') }}",
            },
        ];

        function GetDashboardDateRange() {
            const $date_range = $('#daterange');
            const start_date = $date_range.data('start-date');
            const end_date = $date_range.data('end-date');

            if (!start_date || !end_date) {
                return '';
            }

            return start_date + ' - ' + end_date;
        }

        function UpdateDashboardCardLinks() {
            const centre = $('#dcentre').val();
            const date_range = GetDashboardDateRange();
            const years = $('#year_filter').val() || [];

            dashboard_card_routes.forEach(function(route_config) {
                $(route_config.selector).each(function() {
                    const url = new URL(route_config.route, window.location.origin);

                    // Centre 1 is the dashboard's "all centres" sentinel.
                    if (centre && String(centre) !== '1') {
                        url.searchParams.set('centre', centre);
                    }

                    if (date_range) {
                        url.searchParams.set('date_range', date_range);
                    }

                    years.forEach(function(year) {
                        url.searchParams.append('years[]', year);
                    });

                    Object.entries(route_config.static_params || {}).forEach(function([key, value]) {
                        url.searchParams.set(key, value);
                    });

                    if (route_config.receipt_status) {
                        const receipt_status = $(this).data('receipt-status');

                        if (receipt_status) {
                            url.searchParams.set('receipt_status', receipt_status);
                        }
                    }

                    $(this).attr('href', url.toString());
                });
            });
        }

        UpdateDashboardCardLinks();

        $(document).on('dashboard:loaded', UpdateDashboardCardLinks);
        $('#dcentre, #year_filter').on('change', UpdateDashboardCardLinks);
        $('#daterange').on('apply.daterangepicker cancel.daterangepicker', UpdateDashboardCardLinks);
    });
</script>
