/* jshint esversion: 6, -W097, -W031 */
/* globals Chart */
"use strict";

$(function() {
    // Default color scheme
    Chart.defaults.plugins.colorschemes.scheme = 'tableau.Classic20';

    // Bar charts
    $("canvas[id*='barchart']").each( function() {
        // Is it a vertical or horizontal bar chart ?
        const vertical = this.id.substring(0, 8) === 'barchart';
        new Chart( $(this), {
            type: 'bar',
            data: {
                labels: $(this).data('labels'),
                datasets: [{
                    label: '# of issues',
                    data: $(this).data('values'),
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    x: {
                        position: vertical ? 'bottom' : 'top',
                        ticks: {
                            autoSkip: false,
                            maxRotation: 90
                        }
                    },
                    y: {
                        ticks: {
                            beginAtZero: true
                        }
                    },
                },
                indexAxis: vertical ? 'x' : 'y',
            }
        });
    });

    // Pie charts
    $("canvas[id^='piechart']").each( function() {
        new Chart( $(this), {
            type: 'pie',
            data: {
                labels: $(this).data('labels'),
                datasets: [{
                    label: '# of issues',
                    data:  $(this).data('values'),
                    borderWidth: 1
                }]
            },
            options: {
                // Graphs have a default size of 500*400
                aspectRatio: 1.25,
                plugins: {
                    colorschemes: {
                        scheme: $(this).data('colors'),
                    },
                },
            }
        });
    });

    // Issue trends
    $("canvas[id^='linebydate']").each( function() {
        const ctx = $(this).get(0).getContext("2d");
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: $(this).data('labels'),
                datasets: [
                    {
                        label: $(this).data('opened-label'),
                        data: $(this).data('opened-values'),
                        fill: false,
                        stack: 'total', // Display on a separate stack
                    },
                    {
                        label: $(this).data('resolved-label'),
                        data: $(this).data('resolved-values'),
                        fill: 'origin',
                    },
                    {
                        label: $(this).data('still-open-label'),
                        data: $(this).data('still-open-values'),
                        fill: '-1', // Fill since "resolved" dataset
                    },
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: true,
                        ticks: {
                            precision: 0,
                        }
                    }
                },
                datasets: {
                    line: {
                        tension: 0.3,
                    }
                },
                plugins: {
                    // legend: {
                    //     display: true,
                    //     fillStyle: "#cccccc",
                    // },
                    tooltip: {
                        mode: 'index',
                        position: 'nearest',
                    },
                    colorschemes: {
                        scheme: 'brewer.Set1-3',
                        reverse: true,
                        fillAlpha: 0.15,
                    }
                }
            }
        });
    });
});
