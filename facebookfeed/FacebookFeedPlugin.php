<?php
namespace Craft;

class FacebookFeedPlugin extends BasePlugin
{

	function getName()
	{
		return Craft::t('Facebook Feed');
	}

	function getVersion()
	{
		return '1.3';
	}

	function getDeveloper()
	{
		return 'Blue Mantis';
	}

	function getDeveloperUrl()
	{
		return 'http://bluemantis.com';
	}

	protected function defineSettings()
	{
		return array(
			'facebookId' => array(AttributeType::String, 'required' => true),
			'itemsToShow' => array(AttributeType::String, 'required' => true)
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('facebookfeed/_settings', array(
			'settings' => $this->getSettings()
		));
	}

	/**
	 * Registers the Twig extension.
	 *
	 * @return FacebookFeedTwigExtension
	 */
	public function addTwigExtension()
	{
		Craft::import('plugins.facebookfeed.twigextensions.FacebookFeedTwigExtension');
		return new FacebookFeedTwigExtension();
	}

}
