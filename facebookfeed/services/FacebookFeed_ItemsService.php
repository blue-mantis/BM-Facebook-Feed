<?php
namespace Craft;

/**
 * FacebookFeed service
 */
class FacebookFeed_ItemsService extends BaseApplicationComponent
{
	public function getFeed() {

		$settings = craft()->plugins->getPlugin('facebookfeed')->getSettings();
		$itemsToShow = $settings->itemsToShow;
		$url = 'https://www.facebook.com/feeds/page.php?id=' . $settings->facebookId . '&format=rss20';

		$xml = $this->curlRequest($url);
		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		$json = json_decode(json_encode($xml->channel));
		$items = isset($json->item) ? array_slice($json->item, 0, $itemsToShow) : array();

		// lets grab any available images and create a stripped-of-images description whilst we're at it
		foreach ($items as $key => &$item) {

			//$item->descriptionImageStripped = preg_replace("/<a href[^>]<img[^>]+\>/i", "", $item->description); // old strip
			$item->descriptionImageStripped = preg_replace("/<a href[^>]+\>\<img[^>]+\>\<\/a\>/i", "", $item->description);
			$item->descriptionImageStripped = preg_replace("/<br\/><br\/><br\/>/i", "", $item->descriptionImageStripped);
			$item->descriptionStripped = strip_tags($item->description, '<br><p><b><h2>');

			// add "images" array property to current facebookFeedItem
			$item->images = array();

			// look for images in description
			preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $item->description, $images);

			// if we found some images, do some stuff
			if (!empty($images[0])) {

				$images = array_pop($images);

				foreach ($images as $imageSrc) {

					// check to see if this is an internal image (served on facebook cdn) or an externally sourced image
					if (strpos($imageSrc, 'fbexternal')) { /* EXTERNAL IMAGE SRC */
						// decode url_encoded string and extract the external image url
						$imageSrc = urldecode($imageSrc);
						$imageSrc = ltrim(strstr($imageSrc, 'url='), 'url=');
						if (strpos($imageSrc, 'scontent') || strpos($imageSrc, 'fbcdn')) $imageSrc = null; // 1px padder

					} else if (strpos($imageSrc, 'fbcdn')) { /* INTERNAL IMAGE SRC */
						// grab facebook image id
						$imageId = explode("_", $imageSrc)[1];
						// retrieve highest res version of image
						$imageSrc = $this->getInternalImageSrc($imageId);

					}

					// lets ignore any nulled images
					if ($imageSrc) $item->images[] = $imageSrc;
				}
			}

			// last step, we might still have a link to an image (not an <img>).  Let's try to source that also
			if ($start = strpos($item->descriptionImageStripped, 'fbcdn') && $end = strpos($item->descriptionImageStripped, '.jpg')) {
				$sweetSpot = substr($item->descriptionImageStripped, $start, $end - $start);
				$imageId = explode("_", $sweetSpot)[1];
				$imageSrc = $this->getInternalImageSrc($imageId);
				$item->images[] = $imageSrc;
			}

			// let's ditch any empty objects that may be passed through as a title property
			if (is_object($item->title)) {
				$item->title = null;
			}

		}

		return $items;
	}

	public function getInternalImageSrc($imageId)
	{
		$url = 'https://graph.facebook.com/' . $imageId . '?fields=images';

		$jsonObject = $this->curlRequest($url);
		$imageObject = json_decode($jsonObject);

		if (is_object($imageObject) && 
			property_exists($imageObject, 'images') &&
			is_array($imageObject->images) &&
			is_object($imageObject->images[0]) &&
			property_exists($imageObject->images[0], 'source'))
		{
			return $imageObject->images[0]->source;
		}

		return false;
	}

	public function curlRequest($url)
	{
		$curl = curl_init(); 
		$header[] = "Cache-Control: max-age=0"; 
		$header[] = "Connection: keep-alive"; 
		$header[] = "Keep-Alive: 300"; 
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; 
		$header[] = "Accept-Language: en-us,en;q=0.5"; 
		$header[] = "Pragma: ";

		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'); 
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($curl, CURLOPT_REFERER, ''); 
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); 
		curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 10); 

		$response = curl_exec($curl); 
		curl_close($curl);

		return $response;
	}

}
