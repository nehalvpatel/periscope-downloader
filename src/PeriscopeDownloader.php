<?php namespace nehalvpatel;

class PeriscopeDownloader {
	private $_guzzle;
	public function __construct()
	{
		$this->_guzzle = new \GuzzleHttp\Client();
	}

	public function download($periscope_url, $destination = __DIR__, $file_name = "")
	{
		preg_match("/(.*)periscope\.tv\/w\/(.*)/", trim($periscope_url), $output_array);
		if (isset($output_array[2]))
		{
			$periscope_token = $output_array[2];
		}
		else {
			preg_match("/(.*)watchonperiscope\.com\/broadcast\/(.*)/", trim($periscope_url), $output_array);
			if (isset($output_array[2])) {
				$watchonperiscope_response = $this->_guzzle->get("https://watchonperiscope.com/api/accessChannel?broadcast_id=" . $output_array[2])->getBody();
				$watchonperiscope_json = json_decode($watchonperiscope_response, true);

				preg_match("/(.*)periscope\.tv\/w\/(.*)/", $watchonperiscope_json["share_url"], $output_array);
				$periscope_token = $output_array[2];
			} else {
				throw new \Exception("Invalid URL");
			}
		}

		// construct filename
		$periscope_details_response = $this->_guzzle->get("https://api.periscope.tv/api/v2/getBroadcastPublic?token=" . $periscope_token)->getBody();
		$periscope_details_json = json_decode($periscope_details_response, true);

		$periscope_user = $periscope_details_json["user"]["username"];
		$periscope_start_time = $periscope_details_json["broadcast"]["start"];

		if ($file_name == "")
		{
			$date = substr($periscope_start_time, 0, 10);
			$hours = substr($periscope_start_time, 11, 2);
			$mins = substr($periscope_start_time, 14, 2);
			$file_name = $periscope_user . "_" . $date . "_" . $hours . "_" . $mins . ".ts";
		} else {
			$file_name = rtrim($file_name, ".ts") . ".ts";
		}

		// set up cookies
		$periscope_cookies_response = $this->_guzzle->get("https://api.periscope.tv/api/v2/getAccessPublic?token=" . $periscope_token)->getBody();
		$periscope_cookies_json = json_decode($periscope_cookies_response, true);

		$replay_url = $periscope_cookies_json["replay_url"];
		$base_url = str_replace("/playlist.m3u8", "", $replay_url);

		$cookies = array();
		foreach ($periscope_cookies_json["cookies"] as $cookie) {
			$cookies[$cookie["Name"]] = $cookie["Value"];
		}

		$cookie_jar = new \GuzzleHttp\Cookie\CookieJar();
		$periscope_cookies = $cookie_jar::fromArray($cookies, "replay.periscope.tv");

		// download playlist and all chunks
		$periscope_playlist_response = $this->_guzzle->get($replay_url, ["cookies" => $periscope_cookies])->getBody()->getContents();
		preg_match_all("/chunk_(.*)\.ts/", $periscope_playlist_response, $chunk_array);

		$tmp_folder = __DIR__ . "/" . bin2hex(openssl_random_pseudo_bytes(16));
		shell_exec("mkdir " . $tmp_folder);

		foreach ($chunk_array[0] as $chunk)
		{
			$chunk_response = $this->_guzzle->get($base_url . "/" . $chunk, ["cookies" => $periscope_cookies])->getBody()->getContents();

			file_put_contents($tmp_folder . "/" . $chunk, $chunk_response);
		}

		// get all *.ts files in directory
		$fileList = array();
		if ($handle = opendir($tmp_folder)) {
			while (false !== ($file = readdir($handle))) {
				if (strpos($file, ".ts") !== false) {
					$fileList[] = escapeshellarg($tmp_folder . "/" . $file);
				}
			}
			closedir($handle);
		}

		natsort($fileList);

		// join the chunks into one file
		$shellScript = "cat " . implode(" ", $fileList) . " >> " . rtrim($destination, "/") . "/" . $file_name;
		shell_exec($shellScript);

		// clean up
		shell_exec("rm -rf " . $tmp_folder);
	}
}