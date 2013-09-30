<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Youtube {
	public function get_latest_video($username) {
		$json = file_get_contents('https://gdata.youtube.com/feeds/api/users/'.$username.'/uploads?max-results=1&alt=json&v=2');
		$channel_data = json_decode($json);

		// Prepare all metadata categories since youtube makes you typecast!
		$feeds = (array)$channel_data->feed;
		$author_data = (array)$channel_data->feed->author[0];
		$video_statistics = (array)$channel_data->feed->entry[0];
		$video_media_group = (array)$video_statistics['media$group'];

		// channel data
		$uploads = $this->get_total_uploads($feeds);
		$author = $this->get_latest_video_author($author_data);
		$channel_id = $this->get_latest_video_channel_id($author_data);


		// video information
		$video_id = $this->get_video_id($video_media_group);
		$title_attrib = str_replace(array('"',"'"), array('&quot;','&#39;'),$this->get_video_title($video_statistics));
		$title = $this->format_title($title_attrib,58);
		$js_friendly_title = str_replace("'","\'",$title_attrib);

		// video stats
		$comment_count = $this->get_latest_video_comment_count($video_statistics);
		$total_likes = $this->get_latest_video_likes($video_statistics);
		$total_dislikes = $this->get_latest_video_dislikes($video_statistics);
		$total_ratings = $this->get_total_ratings($video_statistics);
		$hours_since_video_upload = $this->hours_since_video_upload($video_media_group);

		$like_to_dislike_ratio = $this->like_to_dislike_ratio($total_likes,$total_dislikes,$total_ratings);

		$total_view_count = $this->latest_video_view_count($video_statistics);
		$view_count = $this->format_views($total_view_count,$hours_since_video_upload);

		$likes = $this->format_number($total_likes);
		$dislikes = $this->format_number($total_dislikes);


		$latest_video = array(	'uploads' => $uploads, 
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
		return $latest_video;
	}

	public function debug($array) {
		echo "<pre>". print_r($array,true). "</pre>";
	}

	// Returns the latest video's author (channel username) useful to link back to channel
	public function get_latest_video_author($author_data) {
		$author_to_cast = (array)$author_data['name'];
		$author = $author_to_cast['$t'];
		
		return $author;
	}

	// Returns the total amount of videos from the channel that provided the video
	public function get_total_uploads($feed) {
		$uploads_to_cast = (array)$feed['openSearch$totalResults'];
		$total_uploads = $uploads_to_cast['$t'];

		return $total_uploads;
	}

	// Gets the video id
	public function get_video_id($video_media_group) {
		$videoid_cast = (array)($video_media_group['yt$videoid']);
		$video_id = $videoid_cast['$t'];

		return $video_id;
	}

	// get the video title
	public function get_video_title($video_statistics) {
		$title_to_cast = (array)($video_statistics['title']);
		$video_title = $title_to_cast['$t'];



		return $video_title;
	}

	// Format the header title to display on one line
	public function format_title($title,$length) {
		$title_length = strlen($title);
		if($title_length > 58) {
			$title = substr($title,0,$length).'...';
		} else {
			$title = $title;
		}

		return $title;
	}

	// Gets the time the video was uploaded

	// Gets if the video was uploaded within 12 hours to verify view count
	public function hours_since_video_upload($video_media_group) {
		$uploaded_to_cast = (array)$video_media_group['yt$uploaded'];
		$uploaded = $uploaded_to_cast['$t'];
		$uploaded_date_time = new DateTime($uploaded);
		$since_uploaded = $uploaded_date_time->diff(new DateTime());
		$total_hours = $since_uploaded->h;

		return $total_hours;
	}

	// Gets the video id channel's id for the video provided useful for a subscribe button
	public function get_latest_video_channel_id($author_data) {
		$channel_id_to_cast =  (array)$author_data['yt$userId'];
		$channel_id = $channel_id_to_cast['$t'];

		return $channel_id;
	}

	// Gets the total amount of comments on the last video on the channel
	public function get_latest_video_comment_count($video_statistics) {
		$comment_cast = (array)($video_statistics['gd$comments']);
		$comment_count_cast = (array)($comment_cast['gd$feedLink']);
		$comment_count = $comment_count_cast['countHint'];

		return $comment_count;
	}

	// Gets the total number of likes on a video returns formated nicely
	public function get_latest_video_likes($video_statistics) {
		$likes = $video_statistics['yt$rating']->numLikes;

		return $likes;
	}

	// Gets the total number of dislikes on a video returns formated nicely
	public function get_latest_video_dislikes($video_statistics) {
		$dislikes = $video_statistics['yt$rating']->numDislikes;

		return $dislikes;
	}

	// Gets the total number of ratings on a video
	public function get_total_ratings($video_statistics) {
		$ratings = $video_statistics['gd$rating']->numRaters;

		return $ratings;
	}

	// Gets the latest videos view count formatted nicely
	public function latest_video_view_count($video_statistics) {
		$view_count = $video_statistics['yt$statistics']->viewCount;

		return $view_count;
	}

	public function format_views($total_view_count,$hours_since_video_upload) {
		if(($hours_since_video_upload <= 1 && $total_view_count == 301) || ($hours_since_video_upload <= 6 && $total_view_count == 301)) {
			$view_count = "301+";
		} else {
			$view_count = $this->format_number($total_view_count);
		}

		return $view_count;
	}

	// Formats a number nicely for display on the page.
	public function format_number($number_to_format) {
		$formated_number = number_format($number_to_format,0,0,',');

		return $formated_number;
	}

	// Calculates the like to dislike ratio of the latest video returns as a percent
	public function like_to_dislike_ratio($likes,$dislikes,$ratings) {
		if($ratings !== 0) {
			$like_percentage = 100 * ($likes / $ratings);
			$like_to_dislike_ratio = number_format($like_percentage,2);
			return $like_to_dislike_ratio;
		} else {
			return 100;
		}
	}

	// gets a playlist and all of its data
	public function get_playlist($playlist_id) {
		$json = file_get_contents('https://gdata.youtube.com/feeds/api/playlists/'.$playlist_id.'?alt=json&v=2');
		$return = json_decode($json);

		return $return;
	}
}
