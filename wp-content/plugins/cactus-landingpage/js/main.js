;(function($){
	function isDate(strDate){
		var scratch = new Date(strDate);
		if (scratch.toString()=='NaN' || scratch.toString()=='Invalid Date'){
			return false;
		}else{
			return true;
		}
	};
	var __countdown_timer = function(){
		$('.countdown-time').each(function(index, element) {
			var $this = $(this);
			var $years_text 	= $this.attr('data-years-text');
			var $months_text	= $this.attr('data-months-text');
			var $days_text 		= $this.attr('data-days-text');
			var $hours_text 	= $this.attr('data-hours-text');
			var $minutes_text 	= $this.attr('data-minutes-text');
			var $seconds_text 	= $this.attr('data-seconds-text');
			
			var $timer 			= $this.attr('data-countdown');

			if(!isDate($timer)){alert('You have entered an incorrect time value'); return false;};
			
			$this.countdown({
				until: new Date($timer),
				labels: [$years_text, $months_text, 'Weeks', $days_text, $hours_text, $minutes_text, $seconds_text], 
				labels1: [$years_text, $months_text, 'Week', $days_text, $hours_text, $minutes_text, $seconds_text],  //min
				format: 'yodHMS',	
			}); 
		});
	};
	
	$(document).ready(function(){	
		__countdown_timer();
	});
}(jQuery));