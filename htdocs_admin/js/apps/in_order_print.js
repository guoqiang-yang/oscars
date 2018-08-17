(function () {
	function main() {
		$('#print-order').click(_onPrintOrder);
	}

	function _onPrintOrder() {
		window.print();
		console.log('print success');
	}

	main();

})();