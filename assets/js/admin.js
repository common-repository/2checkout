jQuery(function($){
	$('.2checkout-help-heading').click(function(e){
		var $this = $(this);
		var target = $this.data('target');
		$('.2checkout-help-text:not('+target+')').slideUp();
		if($(target).is(':hidden')){
			$(target).slideDown();
		}
		else {
			$(target).slideUp();
		}
	});

	$('.twoco_help_tablinks .twoco_help_tablink').on( 'click', function(e){
        e.preventDefault();
        var tab_id = $(this).attr('id');
        $('.twoco_help_tablink').removeClass('active');
        $(this).addClass('active');

        $('.twoco_tabcontent').hide();
        $('#'+tab_id+'_content').show();
    } );

    $('#twoco-sc-copy-btn').on( 'click', function(e){
         $("#twoco-sc-generator").select(); 
         document.execCommand("copy");
         $('#twoco-sc-copied-notice').show()
         setTimeout(function(){
           $('#twoco-sc-copied-notice').fadeOut()
         }, 3000)
   } );
})