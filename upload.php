<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<?php
$client_id = 'REDACTED';
$client_secret = 'REDACTED';
error_reporting(0);
//the first thing we have to do is determine if this request is coming from the image upload function, 
//or the finalization (gallery creation) function. galleryval is a field only in the gallery upload form,
//so we can use that to determine which function is calling us.
if ($_REQUEST['galleryval']) {
	//ok, we've got a gallery creation request, let's determine what kind of user it is and what they want.
	switch($_REQUEST['galleryval']){
		case 'imgs':
			//this indicates that the user has chosen to finish uploading an image or series of images, not 
			//an album. lets check and see if theyre anon or not so we know how to handle their data.
			$imgjson = json_decode($_REQUEST['imgjson']);
			$updatejson = json_decode($_REQUEST['updatejson']);
			if ($_COOKIE['username'] == 'Anonymous') {
				//we already have the individual images uploaded, so all we need to do here is add the
				//description, if one is specified.
				if($_REQUEST['description']){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image/'.$updatejson->images->{1});
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
					//curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer '.$_COOKIE['accesstoken']));
					curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'title'=>$_REQUEST['description']));
					$reply = curl_exec($ch);
					curl_close($ch);
					//echo $reply;
				}
				//now, we generate the javascript for our hidden iframe. all the js is targeting window.parent.document,
				//so it will affect our main file, not the iframe. Add the success text, and change the UI.
				echo "<script type='text/javascript'>";
				echo "$('#bottomscreen', window.parent.document).removeClass('bottomnormal').addClass('bottomsuccess');";
		        echo "$('#file_container', window.parent.document).css('text-align','center');";
		        echo "$('#file_container', window.parent.document).html('<span class=\'viewlabel\'>View your screenshot at:</span><span class=\'imglink\'><a href=\'http://imgur.com/".$imgjson->images->{1}."\'>http://imgur.com/".$imgjson->images->{1}."</a></span>');";
		        echo "$('#status', window.parent.document).html('Upload Successful');";
		        echo "$('#finishchoices', window.parent.document).html('');";
		        echo "$('#finishchoices', window.parent.document).attr('id','finishbtn');";
		        echo "$('#finishbtn', window.parent.document).addClass('upload uploadagain');";
		        echo "$('#description', window.parent.document).css('visibility','hidden');";
		        echo "</script>";
			} else {
				//this time we've got a user, but pretty much the same process as before.  we've got the possibility
				//of multiple individual images here, so we've gotta use a for loop this time.
				$imgjson = json_decode($_REQUEST['imgjson']);
				if($_REQUEST['description']){
					foreach($imgjson->images as $k => $v){
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image/'.$v);
						curl_setopt($ch, CURLOPT_POST, TRUE);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
						//curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
						curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer '.$_COOKIE['accesstoken']));
						curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'title'=>$_REQUEST['description']));
						$reply = curl_exec($ch);
						curl_close($ch);
						//echo $reply;
					}
				}
				$containerhtml = '<span class="viewlabel">View your images at:<BR>';
				foreach($imgjson->images as $k => $v) {
					$containerhtml .= '<a href="http://imgur.com/'.$v.'">http://imgur.com/'.$v.'</a><BR>';
				}
				$containerhtml .= "</span>";
				echo "<script type='text/javascript'>";
				echo "$('#bottomscreen', window.parent.document).removeClass('bottomnormal').addClass('bottomsuccess');";
		        echo "$('#file_container', window.parent.document).css('text-align','center');";
		        echo "$('#file_container', window.parent.document).html('".$containerhtml."');";
		        echo "$('#status', window.parent.document).html('Upload Successful');";
		        echo "$('#finishchoices', window.parent.document).html('');";
		        echo "$('#finishchoices', window.parent.document).attr('id','finishbtn');";
		        echo "$('#finishbtn', window.parent.document).addClass('upload uploadagain');";
		        echo "$('#description', window.parent.document).css('visibility','hidden');";
		        echo "</script>";
			}
		break;
		case 'create':
			//this is asking us to create an album.  pretty much the same as before, except this time we're adding
			//our title to the album instead of the images. one thing to note: with the anonymous user, we have to
			//create the album when the first file is uploaded, since files need to be attached at upload for anon
			//users. So the anon user is modifying an existing album, whereas the logged in user is creating a new
			//album and attaching the existing images to it.
			if ($_COOKIE['username'] == 'Anonymous') {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/album/'.$_REQUEST['galleryhash']);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
				//curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer '.$_COOKIE['accesstoken']));
				curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'title'=>$_REQUEST['description']));
				$reply = curl_exec($ch);
				curl_close($ch);
				//echo $reply;
				$reply = json_decode($reply);
				echo "<script type='text/javascript'>";
				echo "$('#bottomscreen', window.parent.document).removeClass('bottomnormal').addClass('bottomsuccess');";
		        echo "$('#file_container', window.parent.document).css('text-align','center');";
		        echo "$('#file_container', window.parent.document).html('<span class=\'viewlabel\'>View your album at:</span><span class=\'imglink\'><a href=\'http://imgur.com/a/".$_REQUEST['galleryid']."\'>http://imgur.com/a/".$_REQUEST['galleryid']."</a></span>');";
		        echo "$('#status', window.parent.document).html('Upload Successful');";
		        echo "$('#finishchoices', window.parent.document).html('');";
		        echo "$('#finishchoices', window.parent.document).attr('id','finishbtn');";
		        echo "$('#finishbtn', window.parent.document).addClass('upload uploadagain');";
		        echo "$('#description', window.parent.document).css('visibility','hidden');";
		        echo "</script>";
			} else {
				$images = json_decode($_REQUEST['imgjson']);
				foreach ($images->images as $k => $v){
					$imgarray[] = $v;
				}
				$imgarray = implode(",",$imgarray);
				$postdata['ids'] = $imgarray;
				if ($_REQUEST['description']){
					$postdata['title'] = $_REQUEST['description'];
				}
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/album');
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				//curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer '.$_COOKIE['accesstoken']));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
				$reply = curl_exec($ch);
				curl_close($ch);
				//echo $reply;
				$reply = json_decode($reply);
				echo "<script type='text/javascript'>";
				echo "$('#bottomscreen', window.parent.document).removeClass('bottomnormal').addClass('bottomsuccess');";
		        echo "$('#file_container', window.parent.document).css('text-align','center');";
		        echo "$('#file_container', window.parent.document).html('<span class=\'viewlabel\'>View your album at:</span><span class=\'imglink\'><a href=\'http://imgur.com/a/".$reply->data->id."\'>http://imgur.com/a/".$reply->data->id."</a></span>');";
		        echo "$('#status', window.parent.document).html('Upload Successful');";
		        echo "$('#finishchoices', window.parent.document).html('');";
		        echo "$('#finishchoices', window.parent.document).attr('id','finishbtn');";
		        echo "$('#finishbtn', window.parent.document).addClass('upload uploadagain');";
		        echo "$('#description', window.parent.document).css('visibility','hidden');";
		        echo "</script>";
			}
		break;
		case 'add':
			//this time, instead of creating a new gallery, we're adding to an existing one.  this only works
			//for logged in users, so we don't need to worry about anons this time.
			$images = json_decode($_REQUEST['imgjson']);
			foreach ($images->images as $k => $v){
				$imgarray[] = $v;
			}
			$imgarray = implode(",",$imgarray);
			$postdata['ids'] = $imgarray;
			if ($_REQUEST['description']){
				$postdata['title'] = $_REQUEST['description'];
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/album/'.$_REQUEST['gallerylist'].'/add');
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			//curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer '.$_COOKIE['accesstoken']));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			$reply = curl_exec($ch);
			curl_close($ch);
			//echo $reply;
			$reply = json_decode($reply);
			echo "<script type='text/javascript'>";
			echo "$('#bottomscreen', window.parent.document).removeClass('bottomnormal').addClass('bottomsuccess');";
	        echo "$('#file_container', window.parent.document).css('text-align','center');";
	        echo "$('#file_container', window.parent.document).html('<span class=\'viewlabel\'>View your album at:</span><span class=\'imglink\'><a href=\'http://imgur.com/a/".$_REQUEST['gallerylist']."\'>http://imgur.com/a/".$_REQUEST['gallerylist']."</a></span>');";
	        echo "$('#status', window.parent.document).html('Upload Successful');";
	        echo "$('#finishchoices', window.parent.document).html('');";
	        echo "$('#finishchoices', window.parent.document).attr('id','finishbtn');";
	        echo "$('#finishbtn', window.parent.document).addClass('upload uploadagain');";
	        echo "$('#description', window.parent.document).css('visibility','hidden');";
	        echo "</script>";
		break;
	}
} else {
	//this section is for upload of individual files. As mentioned above, anon users have to attach an
	//image to an album at upload - they can't do it later. So for anons, we have an album already created
	//that we're adding to, whereas for logged in users we can just add the images for now and add them
	//into the album later. This is pretty awesome, because anon users will never know their album exists
	//to begin with, and logged in users won't have an album created if they choose not to.
	if (@$_FILES['upload1']['error'] !== 0) {
	    exit;
	}
	$client_id = 'ec3d6a194cc8683';
	$filetype = explode('/',mime_content_type($_FILES['upload1']['tmp_name']));
	$image = file_get_contents($_FILES['upload1']['tmp_name']);
	if ($_COOKIE['username'] == 'Anonymous') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
		curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'image' => base64_encode($image),
													'album'	=> $_REQUEST['galleryhash']));

		$reply = curl_exec($ch);

		curl_close($ch);

		$replies[] = json_decode($reply);
	} else {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer '.$_COOKIE['accesstoken']));
		curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'image' => base64_encode($image)));
		$reply = curl_exec($ch);
		curl_close($ch);
		$replies[] = json_decode($reply);
	}
	echo "<script type='text/javascript'>";
	echo 'parent.document.getElementById("status'.$_REQUEST['incrval'].'").innerHTML = "<img height=\"66\" src=\"'.$replies[0]->data->link.'\" align=\"left\">";';
	echo 'var imgjson = jQuery.parseJSON(parent.document.getElementById("imgjson").value);';
	echo 'imgjson.images['.$_REQUEST['incrval'].']="'.$replies[0]->data->id.'";';
	echo 'parent.document.getElementById("imgjson").value = JSON.stringify(imgjson);';
    echo 'var updatejson = jQuery.parseJSON(parent.document.getElementById("updatejson").value);';
    if ($_COOKIE['username'] == 'Anonymous') {
    	echo 'updatejson.images['.$_REQUEST['incrval'].']="'.$replies[0]->data->deletehash.'";';
    } else {
    	echo 'updatejson.images['.$_REQUEST['incrval'].']="'.$replies[0]->data->id.'";';
    }
    echo 'parent.document.getElementById("updatejson").value = JSON.stringify(updatejson);';
	echo 'parent.document.getElementById("finishbtn-wait").className = parent.document.getElementById("finishbtn-wait").className.replace(/\bpleasewait\b/,"");';    
	if ($_REQUEST['incrval'] >= 1 && $_COOKIE['username'] != 'Anonymous'){
		echo 'parent.document.getElementById("finishbtn-wait").className = "";';
		echo 'parent.document.getElementById("finishbtn-wait").id = "finishchoices";';
		echo 'parent.document.getElementById("finishchoices").innerHTML = parent.document.getElementById("finishchoices").innerHTML + "<div id=\"sbimg\"></div><div id=\"sbgal\"></div><div id=\"sbadd\"></div>";';
	} else {
		echo 'parent.document.getElementById("finishbtn-wait").id = "finishbtn";';
	}
	if ($_REQUEST['incrval'] == 2 && $_COOKIE['username'] != 'Anonymous'){
		echo 'parent.document.getElementById("galleryid").value = "'.$galleryid.'";';
	}
	//here, we call back the function in index.php that adds a new file upload button to the main page for our next upload.
	echo 'parent.addbutton();';
	echo '</script>';
}
?>