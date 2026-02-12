/**
 * Chart Service - Chart.js Integration
 */

const ChartService = {
    charts: {},
    
    /**
     * Initialize a chart
     */
    init(canvasId, type, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        
        // Destroy existing chart
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }
        
        // Default options
        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        };
        
        // Create chart
        const chart = new Chart(canvas, {
            type,
            data,
            options: { ...defaultOptions, ...options }
        });
        
        this.charts[canvasId] = chart;
        return chart;
    },
    
    /**
     * Create status doughnut chart
     */
    createStatusChart(canvasId, data) {
        const chartData = {
            labels: data.map(d => d.status),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: ['#10B981', '#F97316'],
                borderWidth: 0
            }]
        };
        
        return this.init(canvasId, 'doughnut', chartData, {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        });
    },
    
    /**
     * Create timeline line chart
     */
    createTimelineChart(canvasId, data) {
        const chartData = {
            labels: data.map(d => d.month_formatted || d.month),
            datasets: [{
                label: 'Projects',
                data: data.map(d => d.new_projects),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };
        
        return this.init(canvasId, 'line', chartData, {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        });
    },
    
    /**
     * Create province bar chart
     */
    createProvinceChart(canvasId, data) {
        const chartData = {
            labels: data.map(d => d.province),
            datasets: [{
                label: 'Projects',
                data: data.map(d => d.count),
                backgroundColor: '#3B82F6',
                borderRadius: 4
            }]
        };
        
        return this.init(canvasId, 'bar', chartData, {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        });
    },
    
    /**
     * Create completion rate chart
     */
    createCompletionRateChart(canvasId, data) {
        const chartData = {
            labels: data.map(d => d.province),
            datasets: [{
                label: 'Completion Rate (%)',
                data: data.map(d => d.completion_rate),
                backgroundColor: data.map(d => d.completion_rate >= 50 ? '#10B981' : '#F97316'),
                borderRadius: 4
            }]
        };
        
        return this.init(canvasId, 'bar', chartData, {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        });
    },
    
    /**
     * Create pending duration chart
     */
    createPendingDurationChart(canvasId, data) {
        const chartData = {
            labels: data.map(d => d.duration_range),
            datasets: [{
                label: 'Projects',
                data: data.map(d => d.count),
                backgroundColor: ['#10B981', '#FBBF24', '#F97316', '#EF4444'],
                borderRadius: 4
            }]
        };
        
        return this.init(canvasId, 'bar', chartData, {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        });
    },
    
    /**
     * Create status distribution pie chart
     */
    createStatusDistributionChart(canvasId, data) {
        const chartData = {
            labels: data.map(d => d.status),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: ['#10B981', '#F97316'],
                borderWidth: 0
            }]
        };
        
        return this.init(canvasId, 'pie', chartData);
    },
    
    /**
     * Create multi-line timeline chart
     */
    createMultiLineChart(canvasId, data) {
        const chartData = {
            labels: data.map(d => d.month_formatted || d.month),
            datasets: [
                {
                    label: 'Completed',
                    data: data.map(d => d.completed),
                    borderColor: '#10B981',
                    backgroundColor: 'transparent',
                    tension: 0.4
                },
                {
                    label: 'Pending',
                    data: data.map(d => d.pending),
                    borderColor: '#F97316',
                    backgroundColor: 'transparent',
                    tension: 0.4
                }
            ]
        };
        
        return this.init(canvasId, 'line', chartData);
    },
    
    /**
     * Create stacked province chart
     */
    createStackedProvinceChart(canvasId, data) {
        const chartData = {
            labels: data.map(d => d.province),
            datasets: [
                {
                    label: 'Completed',
                    data: data.map(d => d.completed),
                    backgroundColor: '#10B981'
                },
                {
                    label: 'Pending',
                    data: data.map(d => d.pending),
                    backgroundColor: '#F97316'
                }
            ]
        };
        
        return this.init(canvasId, 'bar', chartData, {
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        });
    },
    
    /**
     * Destroy all charts
     */
    destroyAll() {
        Object.values(this.charts).forEach(chart => chart.destroy());
        this.charts = {};
    }
};
