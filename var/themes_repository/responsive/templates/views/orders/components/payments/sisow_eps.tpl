<html>
	<head>
		<script src="https://bankauswahl.giropay.de/eps/widget/v1/jquery-1.10.2.min.js"></script>
		<link rel="stylesheet" href="http://bankauswahl.giropay.de/eps/widget/v1/style.css">
		<script src="https://www.sisow.nl/Sisow/scripts/giro-eps.js"></script>
	</head>
	<body>
		Mit eps Online-&Uuml;berweisung zahlen Sie einfach, schnell und sicher im Online-Banking Ihrer Bank. Im n&auml;chsten Schritt werden Sie direkt zum Online-Banking Ihrer Bank weitergeleitet, wo Sie die Zahlung durch Eingabe von PIN und TAN freigeben.
		
		<p>
		Bankauswahl
		<input id="eps_widget" autocomplete="off" name="payment_info[bic_eps]"/>
		</p>
		{literal}
			<script>
				( function($) {
					$(document).ready(function() {
						$('#eps_widget').eps_widget({'return': 'bic'});
					});
				})( jQuery );
			</script>
		{/literal}
	</body>
</html>