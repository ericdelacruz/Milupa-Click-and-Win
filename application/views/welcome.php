<!DOCTYPE html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<title>Click & Win - Das Milupa Easypack Spiel</title>
	<script src="<?php echo base_url('public/js/swfobject.js');?>" type="text/javascript"></script>
	<!--  jquery core -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>	
	<script type="text/javascript">
		var flashvars = {hasPermissions:<?php if(!is_array($fb_me)) : echo 'false'; else: echo 'true'; endif;?><?php if(is_array($fb_me)): echo ', fbid: \''.$fb_me['id'].'\', oauth_token: \''.$oauth_token.'\''; endif; if($country!=''): echo ', country: \''.$country.'\''; endif;?>};
		var params = { allowfullscreen: "false", allowscriptaccess:"always", quality: "high", menu: "false", wmode: "opaque", scale: "noscale" };
		var attributes = { id: "flash", bgcolor: "#FFFFFF" };
		swfobject.embedSWF("<?php echo base_url('public/swf/LidRace.swf');?>", "flash", "810", "810", "9.0.0", "<?php echo base_url('public/js/expressInstall.swf');?>",flashvars,params,attributes);
		
		//Get Permissions and Call Flash Click and Win
		function getPermissions(){
			FB.getLoginStatus(function(response) {
				if (response.status === 'connected') {
					//Flash FallBack
					var uid = response.authResponse.userID;
					var accessToken = response.authResponse.accessToken;
					document.getElementById('flash').clickAndWin(uid,accessToken, false);
				} else {
						FB.login(function(response) {
							if (response.authResponse) {
								//Flash FallBack
								var uid = response.authResponse.userID;
								var accessToken = response.authResponse.accessToken;
								document.getElementById('flash').clickAndWin(uid,accessToken, true);
								console.log(accessToken);
							}else{
								//history.go(0);
								parent.window.location.replace('http://www.facebook.com/<?=$fb_app['fb_page']?>/app_<?=$fb_app['fb_appid']?>');
							}
						});
				}
			});
		}

		//share to twitter
		function shareTwitter(){
			$.ajax({
				url: 'ajax/sharedApplication',
				context: document.body
			}).done(function(){
			});
			var msg = "<?php echo urlencode("Spiele jetzt Click & Win - Das Milupa Easypack Spiel mit der Chance auf viele tolle Sofortgewinne!"); ?>";
			var url = "<?php echo urlencode("http://tinyurl.com/9ndrk45"); ?>";
			window.open('https://twitter.com/intent/tweet?original_referer=&source=tweetbutton&text='+msg+'&url=&via='+url);
			
		}

		//Share to wall
		function shareFacebook(){
			FB.ui({
				method: 'feed',
				display: 'dialog',
				name: 'Bist du schnell genug, um <?php echo $prize; ?> zu gewinnen?',
				link: 'http://apps.facebook.com/milupaclickandwin/',
				picture: 'http://tbgapps.com/milupa/clickandwin/public/img/111X74.png',
				caption: 'Ich habe gerade Click & Win gespielt - Das Milupa Easypack Spiel',
				description: '<?php echo "Spiele jetzt und probiere aus, wie viele Abfolgen du innerhalb von 60 Sekunden richtig wiederholen kannst. Du hast die Chance auf t&auml;gliche Sofortgewinne!"; ?>',
				//source: 'http://vimeo.com/moogaloop.swf?clip_id='+vimeo,
				//type: 'video'
			},
			function(response) { // call function userHasShared() here
				if (response && response.post_id) { //shared application
					$.ajax({
						url: 'ajax/sharedApplication'
					});
				} else {
				}
			});
		}

	</script>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('public/css/screen.css');?>" />
	<style>
		body {
			text-align: center;
		}
		#welcome {
			margin: auto;
		}
	</style>
</head>
<body>
	<div id="welcome">
		<div id="flash">
			<p>Your Browser doesn't support Flash!</p>
			<p><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
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
			xfbml      : true, // parse XFBML
			frictionlessRequests : true
		});
		FB.Canvas.setAutoGrow();
        // Subscribe to like event so we can refresh the page.
        FB.Event.subscribe('edge.create',
            function(response){
                top.location.href = "http://apps.facebook.com/tetleyplucker/";
            }
        );
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