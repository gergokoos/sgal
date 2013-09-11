<?php
	// sgal - Simple Galery
	// made by Gergo Koos
	// licensed under Creative Commons Attribution-NonCommercial 3.0 Unported License.
	
	
	//-------- SETTINGS ----------
	
	$filetypes = array('jpg', 'png');
	$thumbnailsize = '200'; // longer side in pixels, if you change this, you have to delete manually the already existing thumbnails (files starting with _)
	$margin = '50'; // in px;
	
	//----- END OF SETTINGS ------
	
	if ( !isset($_GET['t'])) $_GET['t'] = NULL;
	
	if ( $_GET['t'] != NULL ) {
		$pic = str_replace('/', '', $_GET['t']);
		$pic = str_replace('\'', '', $pic);
				
		if (file_exists('_'.$pic)) {
			header('Content-Type: image/jpeg');
			die(readfile('_'.$pic));
		}
		else if ( file_exists($pic)) {
			list($origx, $origy) = getimagesize($pic);
			
			$ratio_orig = $origx/$origy;
			
			if ($origx < $origy) {
				
				$height = $thumbnailsize;
				$width = $thumbnailsize*$ratio_orig;
			   
			} else {
				$height = $thumbnailsize/$ratio_orig;
				$width = $thumbnailsize;
			}
			
			// Resample
			$thumb = imagecreatetruecolor($width, $height);
			$image = imagecreatefromjpeg($pic);
			imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $origx, $origy);
			
			// Output
			if (is_writable(getcwd())) {
				imagejpeg($thumb,'_'.$pic,80);
				header('Content-Type: image/jpeg');
				readfile('_'.$pic);
			}
			else {
				header('Content-Type: image/jpeg');
				imagejpeg($thumb);
				
			}
			imagedestroy($image);
			imagedestroy($thumb);

		}
	}
	else {
		if ($handle = opendir(getcwd())) {
	        while (($file = readdir($handle)) !== false) { 
	            if ( in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $filetypes) and substr($file, 0,1) != '_' ) {
		           
		            list($width, $height) = getimagesize($file);
		            $pictures[] = array('file' => $file,
		            					'width' => $width,
		            					'height' => $height);
		            $piclist[] = $file;

	            }
	        }
	        closedir($handle);
	    }
	    sort($pictures);
	    sort($piclist);
	    $showhtml = true;
	}
	

?>
<?php if ($showhtml) : ?>
<!DOCTYPE html>
<html>
<head>
	<title>Gallery</title>
	<meta charset="utf-8" />
	<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		//<![CDATA[

			var images = JSON.parse('<?php echo json_encode($pictures); ?>'),
				imglist = JSON.parse('<?php echo json_encode($piclist); ?>');
			
			var margin = <?php echo $margin; ?>,
				vportWidth = 0,
				vportHeight = 0,
				imgWidth = 0,
				imgHeight = 0,
				imgRatio = 0;
			
			jQuery(document).ready(function($) { 
				
				vportWidth = $(window).width();
				vportHeight = $(window).height();
				
				$('#overlay').css('height',	vportHeight+'px'); // setting up the overlay height, width is static
				
			
				$('#overlay').click(function() {
					$("#overlay").hide();
					$("#wrapper").hide();
				});
			
				$('.thumbnail').click(function() { 
					//<img id="bigpic" src="DSCF9106.JPG" />
					//$("#boxcontent").html('');
					
					
					openThumb($(this).attr('id'));
					
					$("#overlay").show();
					$("#wrapper").show();
					$("#bigcontout").fadeIn();
					
				});		
				
				$("body").keydown(function(event) {
					if(event.keyCode == 39) { //right
						return false;						
					}	
					if(event.keyCode == 37) { //left					
						return false;
					}

				});
				
				$("body").keyup(function(event) {
					if(event.keyCode == 27) { //esc		
						$('#overlay').click(); 
					}
					
					if(event.keyCode == 37) { //left					
						
						//alert(jQuery.inArray('DSCF8530.JPG', images));
						var imgnum = jQuery.inArray($("#bigpic").attr('src'), imglist)-1;
						if ( imgnum >= 0) {
							//$("#boxcontent").html('<img id="bigpic" src="'+imglist[imgnum]+'" />');
							openThumb(imgnum);
						}
						
						
					}
					
					if(event.keyCode == 39) { //right
						var imgnum = jQuery.inArray($("#bigpic").attr('src'), imglist)+1;
						if ( imgnum < imglist.length) {
							//$("#boxcontent").html('<img id="bigpic" src="'+imglist[imgnum]+'" />');
							openThumb(imgnum);
						}
												
						
					}	
				});	
			});
			
			function setWrapperSize(width,height) {
				$("#wrapper").css('width',width); 
				$("#wrapper").css('height',height);

				$("#wrapper").css('left',(vportWidth/2)-(width/2));
				$("#wrapper").css('top',(vportHeight/2) - (height/2));
			}
			
			function openThumb(id) {
			
				$("#boxcontent").html('<img id="bigpic" src="'+$("#"+id).data('file')+'" />');
				
					imgWidth = $("#"+id).data('width'),
					imgHeight = $("#"+id).data('height'),
					imgRatio = imgWidth/imgHeight;
					
					if (imgRatio > 1 ) {
						//landscape
						if (imgHeight > vportHeight ) { //in case the image is taller than the screen
							setWrapperSize(imgRatio*(vportHeight-margin),(vportHeight-margin));
						}
						else setWrapperSize(imgWidth,imgHeight);
						
					}
					else {
						//portrait
						setWrapperSize(imgRatio*(vportHeight-margin),(vportHeight-margin));
					}
			}
			
		//]]>
	</script>
	<style>
		body {
			background-color: rgba(0,0,0,0.8);
			overflow-x: hidden;
		}
		#container {
			text-align: center;
		}
		img {
			display: block;
			margin-left: auto;
			margin-right: auto;
			
		}
		.thumbnail {
			display: inline-block;
			border: 1px solid grey;
			width: 200px;
			height: 200px;
			margin-left: 20px;
			margin-top: 20px;
		}
		#bigbck {
			display: none;
			position: fixed;
			top: 0px;
			left: 0px;
			width: 100%;
			height: 100%;
			background-color: black;
			
		
		}
		
		/*------*/
		
		#bigcontin {
			position: relative;
			left: -50%;
			border: 1px solid red;
			
		}
		#bigcontout {
			display: none;
			position: absolute;
			left: 50%;
			top: 10px;
			width: 90%;
			height: 90%;
			border: 1px solid white;
			 
		}
		
		
		/*------*/
		
		#overlay {
			
			display: none;
			cursor: pointer;
			position: absolute;
			top: 0px;
			left: 0px;
			width: 100%;
			opacity: 0.7;
			z-index: 1000;
			background-color: rgb(129,129,129);
			border: 0px solid red;
			
		}
		
		#wrapper {
			display: none;
			position: absolute;
			border: 1px solid red;
			background-color: white;
			z-index: 1001;
		}
		
		#boxcontent {
			width: 100%;
			height: 100%;
		}
		
		#bigpic {
			margin-left: auto;
			margin-right: auto;
			max-width: 100%;
			max-height: 100%;
		}
		
		
	</style>
	
</head>
<body>

<div id="container">
	<?php for ($i=0;$i<count($pictures);$i++) : ?>
		<span class="thumbnail" id="<?php echo $i; ?>" data-file="<?php echo $pictures[$i]['file']; ?>" data-width="<?php echo $pictures[$i]['width']; ?>" data-height="<?php echo $pictures[$i]['height']; ?>" ><img src="<?php echo $_SERVER['PHP_SELF'].'?t='.$pictures[$i]['file']; ?>" alt=""/></span>
	<?php endfor; ?>
	
</div>

<div id="overlay"></div>
<div id="wrapper">
	<div id="boxcontent">
		&nbsp;
	</div>
</div>

</body>
</html>
<?php endif; ?>