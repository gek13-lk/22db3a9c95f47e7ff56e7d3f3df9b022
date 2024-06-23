var CustomColumn = {
	init: function (columnEvents) {
		var seriesData = columnEvents.series;
		var categories = columnEvents.categories;

		for (var key in seriesData) {
			if (seriesData.hasOwnProperty(key)) {
				var series = seriesData[key];

				if (typeof series[0].data === 'object' && !Array.isArray(series[0].data)) {
					series[0].data = Object.values(series[0].data);
				}

				if (typeof series[1].data === 'object' && !Array.isArray(series[1].data)) {
					series[1].data = Object.values(series[1].data);
				}

				var options = {
					chart: {
						height: 350,
						type: 'bar',
					},
					plotOptions: {
						bar: {
							horizontal: false,
							endingShape: 'rounded',
							columnWidth: '35%',
						},
					},
					dataLabels: {
						enabled: false
					},
					stroke: {
						show: true,
						width: 2,
						colors: ['transparent']
					},
					series: [
						series[0],
						series[1],
					],
					xaxis: {
						categories: categories,
					},
					yaxis: {
						title: {
							text: 'Количество исследований, шт'
						}
					},
					fill: {
						opacity: 1
					},
					tooltip: {
						y: {
							formatter: function (val) {
								return val + " шт";
							}
						}
					},
					grid: {
						row: {
							colors: ['#f4f5fb', '#ffffff'],
							opacity: 0.5
						},
					},
					colors: ['#0069ff', '#69d94e'],
				};

				var chartId = "#basic-column-graph-" + key;
				var chart = new ApexCharts(
					document.querySelector(chartId),
					options
				);
				chart.render();
			}
		}
	}
};
