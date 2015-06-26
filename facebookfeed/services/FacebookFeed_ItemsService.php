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

		$xml = curl_exec($curl);
		curl_close($curl);

		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		$json = json_decode(json_encode($xml->channel));
		$items = isset($json->item) && is_array($json->item) ? array_slice($json->item, 0, $itemsToShow) : array();

		foreach ($items as &$item) {
			if (is_object($item->title)) $item->title = '';
			if (is_object($item->guid)) $item->guid = '';
			if (is_object($item->title)) $item->title = '';
			if (is_object($item->link)) $item->link = '';
			if (is_object($item->description)) $item->description = '';
			if (is_object($item->pubDate)) $item->pubDate = '';
			if (is_object($item->author)) $item->author = '';
		}

		return $items;
	}
}
