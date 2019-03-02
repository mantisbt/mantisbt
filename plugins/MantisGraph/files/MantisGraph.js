/* jshint -W097, -W031 */
/* globals Chart */
"use strict";

$(document).ready( function() {
    // Default color scheme
    Chart.defaults.global.plugins.colorschemes.scheme = 'tableau.Classic20';

    $("canvas[id*='barchart']").each( function() {
        var type = this.id.substr(0,8) === 'barchart' ? 'bar' : 'horizontalBar';
        new Chart( $(this), {
            type: type,
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
                    xAxes: [{
                        position: type === 'bar' ? 'bottom' : 'top',
                        ticks: {
                            autoSkip: false,
                            maxRotation: 90
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    });

    $("canvas[id^='piechart']").each( function() {
        new Chart( $(this), {
            type: 'pie',
            data: {
                labels: $(this).data('labels'),
                datasets: [{
                    label: '# of issues',
                    data:  $(this).data('values'),
                    backgroundColor: $(this).data('colors'),
                    borderColor: $(this).data('colors'),
                    borderWidth: 1
                }]
            }
        });
    });

    $("canvas[id^='linebydate']").each( function() {
        var ctx = $(this).get(0).getContext("2d");
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: $(this).data('labels'),
                datasets: [
                    {
                        label: $(this).data('opened-label'),
                        data: $(this).data('opened-values')
                    },
                    {
                        label: $(this).data('resolved-label'),
                        data: $(this).data('resolved-values')
                    },
                    {
                        label: $(this).data('still-open-label'),
                        data: $(this).data('still-open-values')
                    }
                ]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                plugins: {
                    colorschemes: {
                        scheme: 'brewer.Set1-3',
                        reverse: true,
                        fillAlpha: 0.15
                    }
                }
            }
        });
    });
});
