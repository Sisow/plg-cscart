<html>
	<head>
		<script src="https://bankauswahl.giropay.de/eps/widget/v1/jquery-1.10.2.min.js"></script>
		<link rel="stylesheet" href="https://bankauswahl.giropay.de/widget/v1/style.css">
		<script src="https://www.sisow.nl/Sisow/scripts/giro-eps.js"></script>
	</head>
	<body>
		Mit giropay zahlen Sie einfach, schnell und sicher im Online-Banking Ihrer teilnehmenden Bank oder Sparkasse. Sie werden direkt zum Online-Banking Ihrer Bank weitergeleitet, wo Sie die &Uuml;berweisung durch Eingabe von PIN und TAN freigeben.
		
		<p>
		Bankauswahl
		<input id="giropay_widget" autocomplete="off" name="payment_info[bic_giropay]"/>
		</p>
		{literal}
			<script>
				( function($) {
					$(document).ready(function() {
						$('#giropay_widget').giropay_widget({'return': 'bic','kind': 1});
					});
				} ) ( jQuery );
			</script>
		{/literal}
	</body>
</html>