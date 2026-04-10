$(document).ready(function () {

    // Admin Charts
    if (document.getElementById('chartContainer')) {
        // Assuming your JSON data is embedded in a hidden div with id 'json-data_dough-nut-chart'
        var jsonDataElement = document.getElementById('json-data_dough-nut-chart');

        if (jsonDataElement) {
            var jsonDataString = jsonDataElement.textContent.trim();

            var dataPieChart = JSON.parse(jsonDataString);

            var titles = [];

            var count = [];

        } else {
            console.error('Element with id "json-data_dough-nut-chart" not found.');
        }

        var chart = new CanvasJS.Chart("chartContainer", {
            exportEnabled: true,
            animationEnabled: true,
            title: {
                text: "Assement Details Chart"
            },
            legend: {
                cursor: "pointer",
                itemclick: explodePie
            },
            data: [{
                type: "pie",
                showInLegend: true,
                toolTipContent: "{name}: <strong>{y}</strong>",
                indexLabel: "{name} - {y} Audits",
                dataPoints: dataPieChart
            }]
        });
        chart.render();

        function explodePie(e) {
            if (typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
                e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
            } else {
                e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
            }
            e.chart.render();

        }
    }

    // Auditor / Reviewer / Compliance  Charts
    if (document.getElementById('assesmentWiseScoreChart')) {

        var assesScoreHighJsonDataElement = document.getElementById('json-data-assesmentWiseScoreChart-high');
        var assesScoreMediumJsonDataElement = document.getElementById('json-data-assesmentWiseScoreChart-medium');
        var assesScoreLowJsonDataElement = document.getElementById('json-data-assesmentWiseScoreChart-low');

        if (assesScoreHighJsonDataElement || assesScoreMediumJsonDataElement || assesScoreLowJsonDataElement) {

            var jsonDataHigh = JSON.parse(assesScoreHighJsonDataElement.textContent.trim());
            var jsonDataMedium = JSON.parse(assesScoreMediumJsonDataElement.textContent.trim());
            var jsonDataLow = JSON.parse(assesScoreLowJsonDataElement.textContent.trim());

            var assesmentWiseScoreChart = new CanvasJS.Chart("assesmentWiseScoreChart", {
                animationEnabled: true,
                title: {
                    text: "Assessment Wise Risk Score",
                    fontFamily: "arial black",
                    fontColor: "#695A42",
                    fontSize: 20,
                },
                axisX: {
                    interval: 1,
                },
                axisY: {
                    valueFormatString: "#0",
                    gridColor: "#B6B1A8",
                    tickColor: "#B6B1A8",
                    stacked: true
                },
                toolTip: {
                    shared: true,
                    content: toolTipContent
                },
                data: [{
                    type: "stackedColumn",
                    showInLegend: true,
                    color: "rgba(220,20,60)",
                    name: "High Risk",
                    dataPoints: jsonDataHigh
                },
                {
                    type: "stackedColumn",
                    showInLegend: true,
                    name: "Medium Risk",
                    color: "rgba(255,165,0)",
                    dataPoints: jsonDataMedium
                },
                {
                    type: "stackedColumn",
                    showInLegend: true,
                    name: "Low Risk",
                    color: "rgba(34,139,34)",
                    dataPoints: jsonDataLow
                }]
            });

            assesmentWiseScoreChart.render();

            function toolTipContent(e) {
                var str = "";
                var total = 0;
                for (var i = 0; i < e.entries.length; i++) {

                    var str1 = "<span style='color:black; font-weight: bold;'>Period: " + e.entries[i].dataPoint.label + "</span><br/>"
                    var str2 = "<span style='color:" + e.entries[i].dataSeries.color + "'> " + e.entries[i].dataSeries.name + "</span>: <strong>" + e.entries[i].dataPoint.y + "</strong><br/>";
                    total += e.entries[i].dataPoint.y;
                    str = str.concat(str2);
                }
                total = Math.round(total * 100) / 100;
                var str3 = "<span style='color:Tomato'>Total:</span><strong>" + total + "</strong><br/>";
                str = str.concat(str3);
                str = str.concat(str1);

                return str;
            }
        }
    }
});


function chartDataAjax(assesId = 'all', auditId) {
    // Ensure auditId is provided
    if (!auditId) {
        console.error("Missing required parameter: auditId");
        return; // Early exit if auditId is missing
    }

    const assesIdUrl = $('#asses_period').attr('data-asses-id-url');

    $.ajax({
        url: assesIdUrl,
        type: 'POST',
        data: {
            asses_id: assesId,
            audit_id: auditId
        },
        success: function (res) {
            try {
                const jsonData = JSON.parse(res);

                riskCategoryChart(jsonData.data.riskCategoryScore);

                riskWiseScoreChart(jsonData.data.riskTypeWiseScore);

                //   riskCategoryChart(jsonData.data.riskCategoryScore);
            } catch (error) {
                console.error("Error parsing JSON response:", error);
            }
        },
        error: function (request, error) {
            console.error("Error making AJAX request:", request, error);
        }
    });
}

$(document).ready(function () {

    if (document.getElementById('asses_period')) {
        const auditId = $('#asses_period').attr('data-auditId'); // Fetch auditId on document ready
        chartDataAjax('all', auditId); // Initial call with default assesId ('all')
    }
});

$('#asses_period').on('change', function () {
    const auditId = $('#asses_period').attr('data-auditId'); // Fetch auditId again on change
    chartDataAjax($(this).val(), auditId); // Call with selected assesId and fetched auditId
});

function riskCategoryChart(jsonData) {
    if (document.getElementById('riskChart')) {
        var chart = new CanvasJS.Chart("riskChart", {
            animationEnabled: true,
            title: {
                text: "Risk Score Distribution"
            },
            data: [{
                type: "pie",
                startAngle: 240,
                yValueFormatString: "##0.00\"\" Score",
                indexLabel: "{label} {y}",
                dataPoints: jsonData
            }]
        });
        chart.render();
    }
}

function riskWiseScoreChart(jsonData) {
    if (document.getElementById('riskWiseScoreChart')) {
        var chart = new CanvasJS.Chart("riskWiseScoreChart", {
            animationEnabled: true,
            title: {
                text: "Risk Types Wise Risk Scores"
            },
            axisX: {
                interval: 1
            },
            axisY: {
                // title: "Expenses in Billion Dollars",
                includeZero: true,
            },
            data: [{
                type: "bar",
                toolTipContent: "<b>{label}</b><br>Weighted Score: {y}<br>",
                dataPoints: jsonData
            }]
        });
        chart.render();
    }
}

// Top Level Management

function assesDaysBarDataAjx(auditId) {
    // Ensure auditId is provided
    if (!auditId) {
        console.error("Missing required parameter: auditId");
        return; // Early exit if auditId is missing
    }

    const assesIdUrl = $('#audit_unit').attr('data-audit-unit-id-url');

    // Ensure assesIdUrl is provided
    if (!assesIdUrl) {
        console.error("Missing data-audit-unit-id-url attribute");
        return; // Early exit if assesIdUrl is missing
    }

    $.ajax({
        url: assesIdUrl,
        type: 'POST',
        data: {
            audit_id: auditId,
        },
        success: function (res) {
            try {
                const jsonData = JSON.parse(res);

                const auditDaysJson = jsonData.data.auditDays;

                const auditReviewDaysJson = jsonData.data.auditReviewDays;

                const complianceDaysJson = jsonData.data.complianceDays;

                const complianceReviewDaysJson = jsonData.data.complianceReviewDays;

                assesDaysChart(auditDaysJson, auditReviewDaysJson, complianceDaysJson, complianceReviewDaysJson);

                riskCategoryChartTop(jsonData.data.allRiskData);

                document.getElementById('totalRiskWeightedScoreSingle').innerHTML = jsonData.data.branchWisetotalWeightedScore;

                document.getElementById('avgRiskWeightedScoreSingle').innerHTML = jsonData.data.branchWiseAvgWeightedScore;

            } catch (error) {
                console.error("Error parsing JSON response:", error);
            }
        },
        error: function (request, error) {
            console.error("Error making AJAX request:", request, error);
        }
    });
}

$(document).ready(function () {
    if (document.getElementById('audit_unit')) {
        const initialAuditId = $('#audit_unit').val(); // Get the initial value of the select element
        assesDaysBarDataAjx(initialAuditId); // Pass the initial value to the function
    }
});

$('#audit_unit').on('change', function () {
    assesDaysBarDataAjx($(this).val());
});

function assesDaysChart(auditDaysJson, auditReviewDaysJson, complianceDaysJson, complianceReviewDaysJson) {
    var assesmentDaysChart = new CanvasJS.Chart("assesmentDaysChart", {
        animationEnabled: true,
        title: {
            text: "Number of Days Taken for Assesment",
            fontFamily: "arial black",
            fontColor: "#695A42",
            fontSize: 20,
        },
        axisX: {
            interval: 1,
        },
        axisY: {
            valueFormatString: "#0",
            gridColor: "#B6B1A8",
            tickColor: "#B6B1A8",
            stacked: true
        },
        toolTip: {
            shared: true,
            content: toolTipContent
        },
        data: [{
            type: "stackedColumn",
            showInLegend: true,
            color: "rgba(220,20,60)",
            name: "Audit Days",
            dataPoints: auditDaysJson
        },
        {
            type: "stackedColumn",
            showInLegend: true,
            name: "Audit Review Days",
            color: "rgba(255,165,0)",
            dataPoints: auditReviewDaysJson
        },
        {
            type: "stackedColumn",
            showInLegend: true,
            name: "Compliance Days",
            color: "rgba(34,139,34)",
            dataPoints: complianceDaysJson
        },
        {
            type: "stackedColumn",
            showInLegend: true,
            name: "Compliance Review Days",
            color: "rgba(44,300,65)",
            dataPoints: complianceReviewDaysJson
        }
        ]
    });

    assesmentDaysChart.render();

    function toolTipContent(e) {
        var str = "";
        var total = 0;
        for (var i = 0; i < e.entries.length; i++) {

            var str1 = "<span style='color:black; font-weight: bold;'>Period: " + e.entries[i].dataPoint.label + "</span><br/>"
            var str2 = "<span style='color:" + e.entries[i].dataSeries.color + "'> " + e.entries[i].dataSeries.name + "</span>: <strong>" + e.entries[i].dataPoint.y + "</strong><br/>";
            total += e.entries[i].dataPoint.y;
            str = str.concat(str2);
        }
        total = Math.round(total * 100) / 100;
        var str3 = "<span style='color:Tomato'>Total:</span><strong>" + total + "</strong><br/>";
        str = str.concat(str3);
        str = str.concat(str1);

        return str;
    }
}

function riskCategoryChartTop(jsonData) {
    if (document.getElementById('quesRiskPieChart')) {
        var chart = new CanvasJS.Chart("quesRiskPieChart", {
            animationEnabled: true,
            title: {
                text: "Questions Risk Category"
            },
            data: [{
                type: "pie",
                startAngle: 240,
                yValueFormatString: "##\"\" Scores",
                indexLabel: "{label} {y}",
                dataPoints: jsonData
            }]
        });
        chart.render();
    }
}

function riskBranchesBarChart(jsonData) {
    if (document.getElementById('riskBranchesBarChart')) {
        var riskBranchesBarChart = new CanvasJS.Chart("riskBranchesBarChart", {
            animationEnabled: true,
            theme: "light1", // "light1", "light2", "dark1", "dark2"
            title: {
                text: "Branches Risk"
            },
            axisY: {
                title: "Risk Weighted Score",
            },
            axisX: {
                title: "Risk Weighted Score",
                interval: 1,

            },
            toolTip: {
                contentFormatter: function (e) {
                    var content = "";
                    for (var i = 0; i < e.entries.length; i++) {
                        content += e.entries[i].dataPoint.label + ": " + e.entries[i].dataPoint.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '') + "<br/>";
                    }
                    return content;
                }
            },
            data: [{
                type: "column",
                legendMarkerColor: "grey",
                dataPoints: jsonData
            }]
        });
        riskBranchesBarChart.render();
    }
}

$(document).ready(function () {

    if (document.getElementById('json-data-riskBranchesBarChart')) {
        var jsonDataElement = document.getElementById('json-data-riskBranchesBarChart');

        if (jsonDataElement) {
            var jsonDataString = jsonDataElement.textContent.trim();

            var dataPieChart = JSON.parse(jsonDataString);

        } else {
            console.error('Element with id "json-data-riskBranchesBarChart" not found.');
        }

        riskBranchesBarChart(dataPieChart); // Pass the initial value to the function
    }
});