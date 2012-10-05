<!DOCTYPE html>
<head>
	<title>Click & Win - Das Milupa Easypack Spiel</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script src="<?php echo base_url('public/js/cufon.js');?>"></script>
	<script src="<?php echo base_url('public/js/poppl_400.font.js');?>"></script>
	<script src="<?php echo base_url('public/js/popplm_700.font.js');?>"></script>
	<script src="<?php echo base_url('public/js/easing.1.3.js');?>"></script>

	<script type="text/javascript">
		Cufon.replace('.poppl', { fontFamily: 'poppl' });
		Cufon.replace('.popplm', { fontFamily: 'popplm' });

		$(document).ready(function() {
			$('#plate').css('top','-77px').css('left', '35px');
			/*setTimeout(function(){
				$('body').css('display', 'block');
			}, 300);*/
		});
	</script>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('public/css/screen.css');?>" />
	<style>
		body {
			background: url('<?php echo base_url('public/img/milupa-likegate.jpg'); ?>') no-repeat right top;
		}
		.like-mask{
			width:50px;
			height:24px;
			overflow:hidden;
			margin: 275px 0 0;
		}
	</style>
</head>
<body>
	<div id="likegate">
		<div id="plate">
        	<div class="like-mask">
				<div class="fb-like" data-href="http://www.facebook.com/<?php echo $fb_app['fb_page'];?>" data-send="false" data-layout="standard" data-width="100" data-show-faces="false"></div>
			</div>
        </div>
	</div>
	<div id="fb-root"></div>
</body>
</html>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '<?=$fb_app['fb_appid']?>', // App ID
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true  // parse XFBML
    });
    FB.Canvas.setAutoGrow();
        // Subscribe to like event so we can refresh the page.
        FB.Event.subscribe('edge.create',
            function(response){
                top.location.href = 'http://www.facebook.com/<?=$fb_app['fb_page']?>/app_<?=$fb_app['fb_appid']?>';
            }
        );
    // Additional initialization code here
  };

  // Load the SDK Asynchronously
  (function(d){
     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/en_US/all.js";
     ref.parentNode.insertBefore(js, ref);
   }(document));
</script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-32794296-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>