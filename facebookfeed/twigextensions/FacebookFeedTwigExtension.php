<?php
namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class FacebookFeedTwigExtension extends \Twig_Extension
{

	/**
	 * Returns an array of global variables.
	 *
	 * @return array An array of global variables.
	 */
	public function getGlobals()
	{
		$globals['facebookFeedItems'] = craft()->facebookFeed_items->getFeed();
		return $globals;
	}

	public function getName()
	{
		return Craft::t('FacebookFeed');
	}

}
