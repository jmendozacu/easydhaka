$htz(document).ready(function(){	
	$htz('.user_store_list input').click(function(){
		var s = $htz(this).is(':checked');
		if(s==true){
			$htz(this).parent('p').addClass('active');
			$htz(this).parent('p').find('img').attr('src',$htz('#htz_file_path').val()+'active.png');
		} else {
			$htz(this).parent('p').removeClass('active');
			$htz(this).parent('p').find('img').attr('src',$htz('#htz_file_path').val()+'inactive.png');
		}
	});
	
	$htz('.user_store_list .str').click(function(){
		$htz(this).find('input').trigger('click');
		var f=true;
		$htz('.user_store_list input').each(function(){
			var s = $htz(this).is(':checked');
			if(s==true){f=false;}
		});
		if(f){
			$htz('#htz_store_flag').removeClass('required-entry');
			$htz('#htz_store_flag').addClass('required-entry');
		} else {
			$htz('#htz_store_flag').removeClass('required-entry');			
		}
	});
});
