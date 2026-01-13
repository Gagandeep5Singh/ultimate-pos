$(document).ready(function() {
    if ($('#dashboard_date_filter').length == 1) {
        dateRangeSettings.startDate = moment();
        dateRangeSettings.endDate = moment();
        $('#dashboard_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
            $('#dashboard_date_filter span').html(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            update_statistics(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            if ($('#quotation_table').length && $('#dashboard_location').length) {
                quotation_datatable.ajax.reload();
            }
        });

        update_statistics(moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));
    }

    $('#dashboard_location').change( function(e) {
        var start = $('#dashboard_date_filter')
                    .data('daterangepicker')
                    .startDate.format('YYYY-MM-DD');

        var end = $('#dashboard_date_filter')
                    .data('daterangepicker')
                    .endDate.format('YYYY-MM-DD');

        update_statistics(start, end);
    });

    //atock alert datatables
    var stock_alert_table = $('#stock_alert_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY:        "75vh",
        scrollX:        true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'Btirp',
        ajax: {
            "url": '/home/product-stock-alert',
            "data": function ( d ) {
                if ($('#stock_alert_location').length > 0) {
                    d.location_id = $('#stock_alert_location').val();
                }
            }
        },
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_alert_table'));
        },
    });

    $('#stock_alert_location').change( function(){
        stock_alert_table.ajax.reload();
    });
    //payment dues datatables
    purchase_payment_dues_table = $('#purchase_payment_dues_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY:        "75vh",
        scrollX:        true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'Btirp',
        ajax: {
            "url": '/home/purchase-payment-dues',
            "data": function ( d ) {
                if ($('#purchase_payment_dues_location').length > 0) {
                    d.location_id = $('#purchase_payment_dues_location').val();
                }
            }
        },
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#purchase_payment_dues_table'));
        },
    });

    $('#purchase_payment_dues_location').change( function(){
        purchase_payment_dues_table.ajax.reload();
    });

    //Sales dues datatables
    sales_payment_dues_table = $('#sales_payment_dues_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY:        "75vh",
        scrollX:        true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'Btirp',
        ajax: {
            "url": '/home/sales-payment-dues',
            "data": function ( d ) {
                if ($('#sales_payment_dues_location').length > 0) {
                    d.location_id = $('#sales_payment_dues_location').val();
                }
            }
        },
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#sales_payment_dues_table'));
        },
    });

    $('#sales_payment_dues_location').change( function(){
        sales_payment_dues_table.ajax.reload();
    });

    //Stock expiry report table
    stock_expiry_alert_table = $('#stock_expiry_alert_table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        scrollY:        "75vh",
        scrollX:        true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'Btirp',
        ajax: {
            url: '/reports/stock-expiry',
            data: function(d) {
                d.exp_date_filter = $('#stock_expiry_alert_days').val();
            },
        },
        order: [[3, 'asc']],
        columns: [
            { data: 'product', name: 'p.name' },
            { data: 'location', name: 'l.name' },
            { data: 'stock_left', name: 'stock_left' },
            { data: 'exp_date', name: 'exp_date' },
        ],
        fnDrawCallback: function(oSettings) {
            __show_date_diff_for_human($('#stock_expiry_alert_table'));
            __currency_convert_recursively($('#stock_expiry_alert_table'));
        },
    });

    if ($('#quotation_table').length) {
        quotation_datatable = $('#quotation_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader:false,
            aaSorting: [[0, 'desc']],
            "ajax": {
                "url": '/sells/draft-dt?is_quotation=1',
                "data": function ( d ) {
                    if ($('#dashboard_location').length > 0) {
                        d.location_id = $('#dashboard_location').val();
                    }
                }
            },
            columnDefs: [ {
                "targets": 4,
                "orderable": false,
                "searchable": false
            } ],
            columns: [
                { data: 'transaction_date', name: 'transaction_date'  },
                { data: 'invoice_no', name: 'invoice_no'},
                { data: 'name', name: 'contacts.name'},
                { data: 'business_location', name: 'bl.name'},
                { data: 'action', name: 'action'}
            ]            
        });
    }
});

function update_statistics(start, end) {
    var location_id = '';
    if ($('#dashboard_location').length > 0) {
        location_id = $('#dashboard_location').val();
    }
    var data = { start: start, end: end, location_id: location_id };
    //get purchase details
    var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
    $('.total_purchase').html(loader);
    $('.purchase_due').html(loader);
    $('.total_sell').html(loader);
    $('.invoice_due').html(loader);
    $('.total_expense').html(loader);
    $('.total_purchase_return').html(loader);
    $('.total_sell_return').html(loader);
    $('.net').html(loader);

    // loaders for NEW boxes
    $('#cash-sales-value').html(loader);
    $('#card-sales-value').html(loader);
    $('#klarna-sales-value').html(loader);
    $('#mbway-sales-value').html(loader);
    $('#bank-transfer-sales-value').html(loader);
    $('#gross-profit-value').html(loader);
    $('#net-after-klarna-value').html(loader);

    $.ajax({
        method: 'get',
        url: '/home/get-totals',
        dataType: 'json',
        data: data,
        success: function (result) {

            // ----------------- OLD FIELDS (existing dashboard) -----------------
            //purchase details
            $('.total_purchase').html(__currency_trans_from_en(result.total_purchase, true));
            $('.purchase_due').html(__currency_trans_from_en(result.purchase_due, true));

            //sell details
            $('.total_sell').html(__currency_trans_from_en(result.total_sell, true));
            $('.invoice_due').html(__currency_trans_from_en(result.invoice_due, true));

            //expense details
            $('.total_expense').html(__currency_trans_from_en(result.total_expense, true));
            var total_purchase_return = result.total_purchase_return - result.total_purchase_return_paid;
            $('.total_purchase_return').html(
                __currency_trans_from_en(total_purchase_return, true)
            );
            var total_sell_return_due = result.total_sell_return - result.total_sell_return_paid;
            $('.total_sell_return').html(__currency_trans_from_en(total_sell_return_due, true));
            // Update indexed versions for multiple dashboard views
            $('[class*="total_sell_return_"]').each(function() {
                $(this).html(__currency_trans_from_en(total_sell_return_due, true));
            });
            $('.total_sr').html(__currency_trans_from_en(result.total_sell_return, true));
            $('.total_srp').html(__currency_trans_from_en(result.total_sell_return_paid, true));
            $('.total_pr').html(__currency_trans_from_en(result.total_purchase_return, true));
            $('.total_prp').html(
                __currency_trans_from_en(result.total_purchase_return_paid, true)
            );
            $('.net').html(__currency_trans_from_en(result.net, true));

            // assign tooltip total_sell_return
            var lang = $('#total_srp').data('value');
            var splitlang = lang.split('-');

            var newContent =
                "<p class='mb-0 text-muted fs-10 mt-5'>" +
                splitlang[0] +
                ": <span class=''>" +
                __currency_trans_from_en(result.total_sell_return, true) +
                "</span><br>" +
                splitlang[1] +
                ": <span class=''>" +
                __currency_trans_from_en(result.total_sell_return_paid, true) +
                '</span></p>';
            $('#total_srp').attr('data-content', newContent);
            // assign tooltip total_purchase_return
            lang = $('#total_prp').data('value');
            splitlang = lang.split('-');

            newContent =
                "<p class='mb-0 text-muted fs-10 mt-5'>" +
                splitlang[0] +
                ": <span class=''>" +
                __currency_trans_from_en(result.total_purchase_return, true) +
                "</span><br>" +
                splitlang[1] +
                ": <span class=''>" +
                __currency_trans_from_en(result.total_purchase_return_paid, true) +
                '</span></p>';

            $('#total_prp').attr('data-content', newContent);

            // Payment methods (always show cards)
            if (result.payment_methods) {
                $('#cash-sales-value').html(
                    __currency_trans_from_en(result.payment_methods.cash || 0, true)
                );
                $('#card-sales-value').html(
                    __currency_trans_from_en(result.payment_methods.card || 0, true)
                );
                $('#klarna-sales-value').html(
                    __currency_trans_from_en(result.payment_methods.klarna || 0, true)
                );
                $('#mbway-sales-value').html(
                    __currency_trans_from_en(result.payment_methods.mbway || 0, true)
                );
                $('#bank-transfer-sales-value').html(
                    __currency_trans_from_en(result.payment_methods.bank_transfer || 0, true)
                );
                // ensure visible
                $('#payment-card-cash, #payment-card-card, #payment-card-klarna, #payment-card-mbway, #payment-card-bank_transfer').show();
            }

if (typeof result.gross_profit !== 'undefined') {
    $('#gross-profit-value').html(
        __currency_trans_from_en(result.gross_profit, true)
    );
}

if (typeof result.net_after_klarna !== 'undefined') {
    $('#net-after-klarna-value').html(
        __currency_trans_from_en(result.net_after_klarna, true)
    );
}
            // gross profit
            if (typeof result.gross_profit !== 'undefined') {
                $('#gross-profit-value').html(
                    __currency_trans_from_en(result.gross_profit, true)
                );
            }

            // net profit after Klarna fees
            if (typeof result.net_after_klarna !== 'undefined') {
                $('#net-after-klarna-value').html(
                    __currency_trans_from_en(result.net_after_klarna, true)
                );
            }

    // Average daily sale
    if (typeof result.avg_daily_sale !== 'undefined') {
        $('#avg-daily-sale-value').html(
            __currency_trans_from_en(result.avg_daily_sale, true)
        );
    }

    // Projected month sale chart with daily sales and projections
    if (typeof result.current_month_daily_sales !== 'undefined' && 
        typeof result.current_month_projected_sales !== 'undefined' &&
        typeof result.current_month_labels !== 'undefined') {
        
        // Update labels
        if (result.current_month_total_actual !== undefined) {
            $('#current-month-actual-label').html('Actual: ' + __currency_trans_from_en(result.current_month_total_actual, true));
        }
        if (result.current_month_total_projected !== undefined) {
            $('#current-month-projected-label').html('Projected: ' + __currency_trans_from_en(result.current_month_total_projected, true));
        }
        
        // Render chart
        renderProjectedSalesChart(
            result.current_month_labels,
            result.current_month_daily_sales,
            result.current_month_projected_sales
        );
    }
    
    // Keep backward compatibility
    if (typeof result.projected_month_sale !== 'undefined') {
        // Old projected month sale value (for other uses if needed)
        if ($('#projected-month-sale-value').length) {
            $('#projected-month-sale-value').html(
                __currency_trans_from_en(result.projected_month_sale, true)
            );
        }
    }

    // Yearly sales chart with last year comparison
    if (typeof result.yearly_actual_sales !== 'undefined' && 
        typeof result.yearly_projected_sales !== 'undefined' &&
        typeof result.yearly_labels !== 'undefined' &&
        typeof result.last_year_sales !== 'undefined') {
        
        // Update labels
        if (result.yearly_total_actual !== undefined) {
            $('#yearly-actual-label').html('Actual: ' + __currency_trans_from_en(result.yearly_total_actual, true));
        }
        if (result.yearly_total_projected !== undefined) {
            $('#yearly-projected-label').html('Projected: ' + __currency_trans_from_en(result.yearly_total_projected, true));
        }
        if (result.last_year_total !== undefined) {
            $('#last-year-label').html('Last Year: ' + __currency_trans_from_en(result.last_year_total, true));
        }
        
        // Render yearly chart
        renderYearlySalesChart(
            result.yearly_labels,
            result.yearly_actual_sales,
            result.yearly_projected_sales,
            result.last_year_sales
        );
    }

        }
    });
}

// Chart instance for projected sales
var projectedSalesChart = null;

// Function to render projected sales chart
function renderProjectedSalesChart(labels, actualSales, projectedSales) {
    var ctx = document.getElementById('projected-sales-chart');
    if (!ctx) {
        return;
    }
    
    // Destroy existing chart if it exists
    if (projectedSalesChart) {
        projectedSalesChart.destroy();
        projectedSalesChart = null;
    }
    
    // Prepare data arrays
    var allLabels = labels || [];
    var actualCount = actualSales ? actualSales.length : 0;
    var actualData = [];
    var projectedData = [];
    
    // Build data arrays - actual sales for past days, projected for future days
    for (var i = 0; i < allLabels.length; i++) {
        if (i < actualCount) {
            // Actual sales
            actualData.push(actualSales[i] || 0);
            projectedData.push(null); // null for actual days
        } else {
            // Projected sales
            actualData.push(null); // null for projected days
            projectedData.push(projectedSales[i - actualCount] || 0);
        }
    }
    
    // Use Chart.js if available
    if (typeof Chart !== 'undefined') {
        // Get container height for better sizing
        var container = document.getElementById('projected-sales-chart-container');
        var containerHeight = container ? container.offsetHeight : 400;
        
        projectedSalesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: allLabels,
                datasets: [
                    {
                        label: 'Actual Sales',
                        data: actualData,
                        borderColor: 'rgb(34, 197, 94)', // green
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        spanGaps: false
                    },
                    {
                        label: 'Projected Sales',
                        data: projectedData,
                        borderColor: 'rgb(168, 85, 247)', // purple
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        spanGaps: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20,
                        left: 15,
                        right: 15
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null && context.parsed.y !== undefined) {
                                    label += __currency_trans_from_en(context.parsed.y, true);
                                } else {
                                    label += 'N/A';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 0) {
                                    return __currency_trans_from_en(value, true);
                                }
                                return '';
                            },
                            font: {
                                size: 12
                            },
                            padding: 10
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            lineWidth: 1
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    } else {
        // Fallback: simple text display if Chart.js not available
        var ctx2d = ctx.getContext('2d');
        ctx2d.clearRect(0, 0, ctx.width, ctx.height);
        ctx2d.fillStyle = '#666';
        ctx2d.font = '14px Arial';
        ctx2d.fillText('Chart.js library required for graph display', 10, 20);
    }
}

// Handle window resize for charts
var resizeTimeout;
$(window).on('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        if (projectedSalesChart) {
            projectedSalesChart.resize();
        }
        if (yearlySalesChart) {
            yearlySalesChart.resize();
        }
    }, 250);
});

// Chart instance for yearly sales
var yearlySalesChart = null;

// Function to render yearly sales chart with last year comparison
function renderYearlySalesChart(labels, actualSales, projectedSales, lastYearSales) {
    var ctx = document.getElementById('yearly-sales-chart');
    if (!ctx) {
        return;
    }
    
    // Destroy existing chart if it exists
    if (yearlySalesChart) {
        yearlySalesChart.destroy();
        yearlySalesChart = null;
    }
    
    // Prepare data arrays
    var allLabels = labels || [];
    var actualData = [];
    var projectedData = [];
    var lastYearData = lastYearSales || [];
    
    // Build data arrays - actual sales for past months, projected for future months
    for (var i = 0; i < allLabels.length; i++) {
        if (actualSales[i] !== null && actualSales[i] !== undefined) {
            // Actual sales
            actualData.push(actualSales[i] || 0);
            projectedData.push(null); // null for actual months
        } else {
            // Projected sales
            actualData.push(null); // null for projected months
            projectedData.push(projectedSales[i] || 0);
        }
    }
    
    // Use Chart.js if available
    if (typeof Chart !== 'undefined') {
        // Get container height for better sizing
        var container = document.getElementById('yearly-sales-chart-container');
        var containerHeight = container ? container.offsetHeight : 400;
        
        yearlySalesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: allLabels,
                datasets: [
                    {
                        label: 'Actual Sales',
                        data: actualData,
                        borderColor: 'rgb(34, 197, 94)', // green
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        spanGaps: false
                    },
                    {
                        label: 'Projected Sales',
                        data: projectedData,
                        borderColor: 'rgb(245, 158, 11)', // amber
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        spanGaps: false
                    },
                    {
                        label: 'Last Year',
                        data: lastYearData,
                        borderColor: 'rgb(59, 130, 246)', // blue
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        spanGaps: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20,
                        left: 15,
                        right: 15
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null && context.parsed.y !== undefined) {
                                    label += __currency_trans_from_en(context.parsed.y, true);
                                } else {
                                    label += 'N/A';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 0) {
                                    // Format in thousands (k notation) for yearly chart
                                    if (value >= 1000) {
                                        var kValue = (value / 1000).toFixed(1);
                                        // Remove .0 if it's a whole number
                                        if (kValue % 1 === 0) {
                                            kValue = Math.floor(kValue);
                                        }
                                        return kValue + 'k €';
                                    }
                                    return __currency_trans_from_en(value, true);
                                }
                                return '';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    } else {
        // Fallback: simple text display if Chart.js not available
        var ctx2d = ctx.getContext('2d');
        ctx2d.clearRect(0, 0, ctx.width, ctx.height);
        ctx2d.fillStyle = '#666';
        ctx2d.font = '14px Arial';
        ctx2d.fillText('Chart.js library required for graph display', 10, 20);
    }
}
