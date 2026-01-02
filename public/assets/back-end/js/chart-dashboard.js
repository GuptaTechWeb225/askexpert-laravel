  // Line chart data update
        function updateChartData(filter) {
            let newData, newCategories;

            switch (filter) {
                case 'month':
                    newData = [30, 40, 35, 50];
                    newCategories = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                    break;
                case 'week':
                    newData = [10, 20, 30, 25, 15, 35, 40];
                    newCategories = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    break;
                default: // year
                    newData = [300, 400, 350, 500, 490, 600, 700, 910, 1250];
                    newCategories = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'];
                    break;
            }

    
            lineChart.updateOptions({
                series: [{
                    name: 'Top Restaurant Performance',
                    data: newData
                }],
                xaxis: {
                    categories: newCategories
                }
            });
        }

        // Chart options
        var lineChartOptions = {
            chart: {
                type: 'line',
                height: 250,
                toolbar: { show: false }
            },
            series: [{
                name: 'Top Restaurant Performance',
                data: [300, 400, 350, 500, 490, 600, 700, 910, 1250],
            }],
            colors: ['#900000'],
            stroke: {
                curve: 'smooth',
                width: 3
            },
            markers: {
                size: 4,
                colors: ['#ffffff'],
                strokeColors: '#900000',
                strokeWidth: 2,
                hover: { size: 7 }
            },
            tooltip: {
                enabled: true,
                y: {
                    formatter: (val) => "$" + val
                }
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            },
            yaxis: {
                min: 0,
                max: 2200,
                tickAmount: 5
            }
        };

        // Render chart
        var lineChart = new ApexCharts(document.querySelector("#dashboard-chart"), lineChartOptions);
        lineChart.render();

        // Filter buttons click
        document.querySelectorAll('input[name="statistics4"]').forEach((input) => {
            input.addEventListener('change', function () {
                const filter = this.value;
                updateChartData(filter);
                updateActiveButton(filter);
            });
        });

        // Highlight active button
        function updateActiveButton(activeValue) {
            document.querySelectorAll('.basic-box-shadow').forEach((label) => {
                label.classList.remove('active');
                const input = label.querySelector('input');
                if (input.value === activeValue) {
                    label.classList.add('active');
                }
            });
        }
        updateActiveButton('year');