YouTube
=======

A PHP Class utilizing the [YouTube Data API] (https://developers.google.com/youtube/) to get information about a channels latest video. To initialize the script you will need to pass a channel paramater into the `get_latest_video` with the channel that you want the video from.

To initialize the class simply use the following code `$youtube = new Youtube;`. Once this is completed then you will be able to call the `get_latest_video` function by using the following code `$youtube_video_data = $youtube->get_latest_video('channel_here');`.

The class will then return a function with the following array:

     array(	'uploads' => $uploads, 
		'author' => $author, 
		'channel_id' => $channel_id,
		'video_id' => $video_id, 
		'title_attrib' => $title_attrib, 
		'title' => $title,
		'js_friendly_title' => $js_friendly_title, 
		'comment_count' => $comment_count, 
		'like_to_dislike_ratio' => $like_to_dislike_ratio,
		'view_count' => $view_count,
		'likes' => $likes,
		'dislikes' => $dislikes);
								
Once you have that you can parse it like you would any normal array! Enjoy the script. If you want additional features for this then please let me know and I'll be happy to figure it out!
