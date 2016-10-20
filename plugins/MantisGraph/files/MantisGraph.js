$(document).ready( function() {
    $("canvas[id^='barchart']").each( function() {
        new Chart( $(this), {
            type: 'bar',
            data: {
                labels: $(this).data('labels'),
                datasets: [{
                    label: '# of issues',
                    data: $(this).data('values'),
                    backgroundColor: 'rgba(252, 189, 189, 0.2)',
                    borderColor: 'rgba(252, 189, 189, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
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
                    backgroundColor: $(this).data('background-colors'),
                    borderColor: $(this).data('border-colors'),
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
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
                        backgroundColor: 'rgba(255, 158, 158, 0.5)',
                        data: $(this).data('opened-values')
                    },
                    {
                        label: $(this).data('resolved-label'),
                        backgroundColor: 'rgba(49, 196, 110, 0.5)',
                        data: $(this).data('resolved-values')
                    },
                    {
                        label: $(this).data('still-open-label'),
                        backgroundColor: 'rgba(255, 0, 0, 1)',
                        data: $(this).data('still-open-values')
                    },]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    });
});
