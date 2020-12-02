<?php

namespace RandsX\InstagramDownloader;

use GuzzleHttp\Client;

class InstagramDownloader
{

	private string $type;

	private string $DownloadLink;

	private array $MetaValues = array();

	/**
	* URL to be executed
	*
	* @var	String
	*/
	private string $URL;

	/**
	* Main domain instagram
	*
	* @var String
	*/
	protected const IG_DOMAIN = "instagram.com";

	/**
	* Constructor
	*
	* @param	String
	*/
	public function __construct(string $url = "") {
		if (! empty($url) and $url !== "" and $url !== null) {
			$this->SetURL($url);
		}
	}

	/**
	* Set the URL
	*
	* @param	String
	*/
	public function SetURL(string $url): void {
		$this->ValidateURL($url);
		$this->URL = $url;
	}

	/**
	* Get the type of data: `image` or `video`.
	*
	* @return String
	*/
	public function getType(): string {
		if (! isset($this->DownloadLink)) {
			$this->Processed();
		}

		return $this->type;
	}

	/**
	* Get download link
	*
	* @param	boolean
	* @return string
	* @throws \RuntimeException
	*/
	public function getDownloadLink($ForceDownload = true): string {
		if (! isset($this->DownloadLink)) {
			$this->Processed();
		}

		if ($ForceDownload) {
			if (strpos($this->DownloadLink, '?') !== false) {
				return $this->DownloadLink . "&dl=1";
			} else {
				return $this->DownloadLink . "?dl=1";
			}
		}
		return $this->DownloadLink;
	}

	/**
	* Parse data
	*
	* @param	String
	* @return array<string>
	*/
	private function ParseData(string $data): array {
		preg_match_all('/<meta[^>]+="([^"]*)"[^>]' . '+content="([^"]*)"[^>]+>/i', $data, $RawTags);

		if (! empty($RawTags)) {
			$MultiValues = array_unique($RawTags[1]);
			$MultiValues = array_diff_assoc($RawTags[1], $MultiValues);
			$MultiValues = array_unique($MultiValues);

			foreach ($RawTags[1] as $key => $tag) {
				$HasMultiValues = (boolean) false;

				foreach ($MultiValues as $tags) {
					// code...
					if ($tag == $tags) {
						$HasMultiValues = (boolean) true;
					}
				}

				if ($HasMultiValues) {
					$this->MetaValues[$tag][] = $RawTags[2][$key];
				} else {
					$this->MetaValues[$tag] = $RawTags[2][$key];
				}
			}
		}

		if (empty($this->MetaValues)) {
			return (array) [];
		}
		return $this->MetaValues;
	}

	/**
	* Validate the URL
	*
	* @param String
	*/
	private function ValidateURL(string $RawURL): string {
		$url = parse_url($RawURL);
		if ($url == false || empty($url["host"])) {
			throw new \InvalidArgumentException("Invalid URL: " . $RawURL);
		}

		// Set host to lowercase
		$host = strtolower($url["host"]);
		if ($host !== self::IG_DOMAIN && $host !== "www." . self::IG_DOMAIN) {
			throw new \InvalidArgumentException();
		}

		// Check path
		if (empty($url["path"])) {
			throw new \InvalidArgumentException();
		}

		// Split URL
		$param = explode('/', $url["path"]);
		if (! empty($param[1]) and ($param[1] == 'p' or $param[1] == "tv") and isset($param[2][4]) and ! isset($param[2][225])) {
			return $param[2];
		}

		if (! empty($param[2]) && ($args[2] === 'p' or $args[2] === 'tv') and ! isset($param[3][255]) and isset($param[3][4], $param[1][4])) {
			return $param[3];
		}

		throw new \InvalidArgumentException('No image or video found in this URL');
	}

	/**
	* Processed
	*/
	protected function Processed(): void {
		$data = $this->FetchData($this->URL);
		if (empty($data)) {
			throw new \RuntimeException('Error fetching information. Perhaps the post is private.', 3);
		}

		// Image
		if (!empty($data["og:image"])) {
			$this->type = "image";
			$this->DownloadLink = $data["og:image"];
			return;
		}

		// Video
		if (!empty($data["og:video"])) {
			$this->type = "video";
			$this->DownloadLink = $data["og:video"];
			return;
		}

		throw new \RuntimeException('Error fetching information. Perhaps the post is private.', 4);
	}

	/**
	* Fetch data from URL
	*
	* @param	String
	* @return array<string>
	*/
	protected function FetchData(string $url): array {
		try {
			$client = new Client;
			$response = $client->get($url);
			$data = $response->getBody()->getContents();

			if (! empty($data) and is_string($data)) {
				return $this->ParseData($data);
			}

			throw new \RuntimeException('Could not fetch data.');
		} catch (\GuzzleHttp\Exception $error) {
			throw new \RuntimeException($error, 12);
		}
	}
}
