<?php
class CNigesCookiesAcceptPublic
{
	/** @var string */
	protected static $bannerHtml = '';

	/**
	 * Inject cookie notice at the end of page generation.
	 */
	public static function OnEpilog()
	{
		global $APPLICATION;

		if (!CModule::IncludeModule(cookiesaccept_MODULE_ID)) {
			return;
		}

		if (COption::GetOptionString(cookiesaccept_MODULE_ID, 'ACTIVE', 'N', SITE_ID) !== 'Y') {
			return;
		}

		if (defined('PUBLIC_AJAX_MODE') && PUBLIC_AJAX_MODE === true) {
			return;
		}
		if (isset($_REQUEST['ajax']) && (string)$_REQUEST['ajax'] !== '') {
			return;
		}
		if (isset($_REQUEST['bxajaxid']) && (string)$_REQUEST['bxajaxid'] !== '') {
			return;
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'niges:cookiesaccept',
			'.default',
			array(),
			false,
			array('HIDE_ICONS' => 'Y')
		);
		$html = ob_get_clean();
		if (!is_string($html) || $html === '') {
			return;
		}

		// Epilog runs after </html> is already in the buffer. Insert the banner before </body>.
		self::$bannerHtml = $html;
		AddEventHandler('main', 'OnEndBufferContent', array(__CLASS__, 'OnEndBufferContent'));
	}

	public static function OnEndBufferContent(&$content)
	{
		if (self::$bannerHtml === '' || !is_string($content) || stripos($content, '</body>') === false) {
			return;
		}
		if (strpos($content, 'id="nca-cookiesaccept-line"') !== false) {
			return;
		}
		$content = preg_replace('/<\/body>/i', self::$bannerHtml . '</body>', $content, 1);
	}
}
