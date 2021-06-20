<style>
	table.table-hover tbody tr:hover {
		background-color: inherit !important;
	}
</style>
<div class="w-100">
	<canvas id="ct-{{ $name }}" class="block"></canvas>
</div>
<div class="text-center">{{ $xLegend }}</div>

<script src="{{url('/')}}/vendor/md0/regenerator/chart.js/chart.min.js"></script>
<!--
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.3.2/chart.min.js" integrity="sha512-VCHVc5miKoln972iJPvkQrUYYq7XpxXzvqNfiul1H4aZDwGBGC0lq373KNleaB2LpnC2a/iNfE5zoRYmB4TRDQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
-->
<script>
	<?php echo $datasets; ?>;
	var ctx = document.getElementById("ct-{{ $name }}");

	var myChart = new Chart(ctx, {
		type: "<?php echo $chartType ?>",
		options: {
			responsive: true,
			layout: {
				padding: 10
			},
			animation: {
				animateScale: true,
				animateRotate: true
			},
		},
		data: {
			labels: <?php echo $labels ?>,
			datasets: datasets
		}
	});
</script>