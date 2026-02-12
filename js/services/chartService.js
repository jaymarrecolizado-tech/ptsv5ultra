/**
 * Chart Service
 * Handles Chart.js initialization, updates, and rendering with performance optimizations
 */

const ChartService = {
    charts: {},
    defaultOptions: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 750,
            easing: 'easeOutQuart'
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    font: {
                        family: "'Segoe UI', sans-serif",
                        size: 12
                    }
                }
            }
        }
    },

    init() {
        this.charts = {};
    },

    createStatusChart(canvasId) {
        const ctx = document.getElementById(canvasId)?.getContext('2d');
        if (!ctx) return null;

        this.charts.statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [0, 0],
                    backgroundColor: ['#27ae60', '#f39c12'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                ...this.defaultOptions,
                cutout: '65%',
                plugins: {
                    ...this.defaultOptions.plugins,
                    title: {
                        display: false
                    }
                }
            }
        });

        return this.charts.statusChart;
    },

    createTimelineChart(canvasId) {
        const ctx = document.getElementById(canvasId)?.getContext('2d');
        if (!ctx) return null;

        this.charts.timelineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Projects Completed',
                    data: [],
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                ...this.defaultOptions,
                plugins: {
                    ...this.defaultOptions.plugins,
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        return this.charts.timelineChart;
    },

    createProvinceChart(canvasId) {
        const ctx = document.getElementById(canvasId)?.getContext('2d');
        if (!ctx) return null;

        this.charts.provinceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Projects',
                    data: [],
                    backgroundColor: [
                        '#3498db', '#2ecc71', '#9b59b6', '#e74c3c',
                        '#f39c12', '#1abc9c', '#34495e', '#e67e22'
                    ],
                    borderRadius: 6,
                    barThickness: 40
                }]
            },
            options: {
                ...this.defaultOptions,
                plugins: {
                    ...this.defaultOptions.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        return this.charts.provinceChart;
    },

    createStatusDistributionChart(canvasId) {
        const ctx = document.getElementById(canvasId)?.getContext('2d');
        if (!ctx) return null;

        this.charts.statusDistributionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [0, 0],
                    backgroundColor: ['#27ae60', '#f39c12'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                ...this.defaultOptions
            }
        });

        return this.charts.statusDistributionChart;
    },

    createProvinceDetailedChart(canvasId) {
        const ctx = document.getElementById(canvasId)?.getContext('2d');
        if (!ctx) return null;

        this.charts.provinceDetailedChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Completed',
                        data: [],
                        backgroundColor: '#27ae60',
                        borderRadius: 4
                    },
                    {
                        label: 'Pending',
                        data: [],
                        backgroundColor: '#f39c12',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                ...this.defaultOptions,
                plugins: {
                    ...this.defaultOptions.plugins,
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });

        return this.charts.provinceDetailedChart;
    },

    createTimelineDetailedChart(canvasId) {
        const ctx = document.getElementById(canvasId)?.getContext('2d');
        if (!ctx) return null;

        this.charts.timelineDetailedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'New Projects',
                        data: [],
                        borderColor: '#3498db',
                        backgroundColor: 'transparent',
                        tension: 0.3,
                        pointRadius: 3
                    },
                    {
                        label: 'Completed',
                        data: [],
                        borderColor: '#27ae60',
                        backgroundColor: 'transparent',
                        tension: 0.3,
                        pointRadius: 3
                    },
                    {
                        label: 'Pending',
                        data: [],
                        borderColor: '#f39c12',
                        backgroundColor: 'transparent',
                        tension: 0.3,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                ...this.defaultOptions,
                plugins: {
                    ...this.defaultOptions.plugins,
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        return this.charts.timelineDetailedChart;
    },

    createPendingDurationChart(canvasId) {
        const ctx = document.getElementById(canvasId)?.getContext('2d');
        if (!ctx) return null;

        this.charts.pendingDurationChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Days Pending',
                    data: [],
                    backgroundColor: '#e74c3c',
                    borderRadius: 4
                }]
            },
            options: {
                ...this.defaultOptions,
                indexAxis: 'y',
                plugins: {
                    ...this.defaultOptions.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        return this.charts.pendingDurationChart;
    },

    createCompletionRateChart(canvasId) {
        const ctx = document.getElementById(canvasId)?.getContext('2d');
        if (!ctx) return null;

        this.charts.completionRateChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Completion Rate (%)',
                    data: [],
                    backgroundColor: '#3498db',
                    borderRadius: 4
                }]
            },
            options: {
                ...this.defaultOptions,
                plugins: {
                    ...this.defaultOptions.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        return this.charts.completionRateChart;
    },

    updateAllCharts() {
        const projects = DataService.getAllProjects();

        if (!projects || projects.length === 0) return;

        const stats = DataService.getStats();
        const provinceData = this.getProvinceData(projects);
        const timelineData = this.getTimelineData(projects);

        if (this.charts.statusChart) {
            this.charts.statusChart.data.datasets[0].data = [stats.completed, stats.pending];
            this.charts.statusChart.update('active');
        }

        if (this.charts.timelineChart) {
            this.charts.timelineChart.data.labels = timelineData.labels;
            this.charts.timelineChart.data.datasets[0].data = timelineData.cumulative;
            this.charts.timelineChart.update('active');
        }

        if (this.charts.provinceChart) {
            this.charts.provinceChart.data.labels = provinceData.labels;
            this.charts.provinceChart.data.datasets[0].data = provinceData.totals;
            this.charts.provinceChart.update('active');
        }

        if (this.charts.statusDistributionChart) {
            this.charts.statusDistributionChart.data.datasets[0].data = [stats.completed, stats.pending];
            this.charts.statusDistributionChart.update('active');
        }

        if (this.charts.provinceDetailedChart) {
            this.charts.provinceDetailedChart.data.labels = provinceData.labels;
            this.charts.provinceDetailedChart.data.datasets[0].data = provinceData.completed;
            this.charts.provinceDetailedChart.data.datasets[1].data = provinceData.pending;
            this.charts.provinceDetailedChart.update('active');
        }

        if (this.charts.timelineDetailedChart) {
            this.charts.timelineDetailedChart.data.labels = timelineData.labels;
            this.charts.timelineDetailedChart.data.datasets[0].data = timelineData.newProjects;
            this.charts.timelineDetailedChart.data.datasets[1].data = timelineData.completed;
            this.charts.timelineDetailedChart.data.datasets[2].data = timelineData.pending;
            this.charts.timelineDetailedChart.update('active');
        }

        if (this.charts.pendingDurationChart) {
            const pendingProjects = projects.filter(p => p.status === 'Pending');
            const pendingData = pendingProjects
                .slice(0, 10)
                .map(p => ({
                    name: p.siteCode,
                    days: Math.floor((new Date() - new Date(p.activationDate)) / (1000 * 60 * 60 * 24))
                }))
                .sort((a, b) => b.days - a.days);

            this.charts.pendingDurationChart.data.labels = pendingData.map(p => p.name);
            this.charts.pendingDurationChart.data.datasets[0].data = pendingData.map(p => p.days);
            this.charts.pendingDurationChart.update('active');
        }

        if (this.charts.completionRateChart) {
            this.charts.completionRateChart.data.labels = provinceData.labels;
            this.charts.completionRateChart.data.datasets[0].data = provinceData.completionRates;
            this.charts.completionRateChart.update('active');
        }

        this.updateReportStats(stats, projects);
    },

    getProvinceData(projects) {
        const provinceMap = {};

        projects.forEach(project => {
            if (!provinceMap[project.province]) {
                provinceMap[project.province] = { total: 0, completed: 0, pending: 0 };
            }
            provinceMap[project.province].total++;
            if (project.status === 'Done') {
                provinceMap[project.province].completed++;
            } else {
                provinceMap[project.province].pending++;
            }
        });

        const labels = Object.keys(provinceMap);
        const totals = labels.map(p => provinceMap[p].total);
        const completed = labels.map(p => provinceMap[p].completed);
        const pending = labels.map(p => provinceMap[p].pending);
        const completionRates = labels.map(p =>
            provinceMap[p].total > 0
                ? Math.round((provinceMap[p].completed / provinceMap[p].total) * 100)
                : 0
        );

        return { labels, totals, completed, pending, completionRates };
    },

    getTimelineData(projects) {
        const timelineMap = {};

        projects.forEach(project => {
            const date = new Date(project.activationDate);
            const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

            if (!timelineMap[key]) {
                timelineMap[key] = { newProjects: 0, completed: 0, pending: 0, date };
            }

            timelineMap[key].newProjects++;
            if (project.status === 'Done') {
                timelineMap[key].completed++;
            } else {
                timelineMap[key].pending++;
            }
        });

        const sortedKeys = Object.keys(timelineMap).sort();
        const labels = sortedKeys.map(k => {
            const [year, month] = k.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });

        const newProjects = sortedKeys.map(k => timelineMap[k].newProjects);
        const completed = sortedKeys.map(k => timelineMap[k].completed);
        const pending = sortedKeys.map(k => timelineMap[k].pending);

        let cumulative = [];
        let runningTotal = 0;
        newProjects.forEach(n => {
            runningTotal += n;
            cumulative.push(runningTotal);
        });

        return { labels, newProjects, completed, pending, cumulative };
    },

    updateReportStats(stats, projects) {
        const reportElements = {
            'report-total': stats.total,
            'report-completed': stats.completed,
            'report-pending': stats.pending,
            'report-provinces': stats.provinces
        };

        for (const [id, value] of Object.entries(reportElements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        }
    },

    destroyAll() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
    }
};
