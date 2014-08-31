<?php

/**
 * This plugin allows to define an individual background image to be used in the backend.
 * Additionally a second image (e.g. a logo) can be specified, which will be displayed
 * in the lower left corner of the screen.
 *
 * @copyright Copyright (c) 2014, VIISON GmbH
 */
class Shopware_Plugins_Backend_ViisonBackendBackgroundImage_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

	/**
	 * Returns the current version of this plugin.
	 *
	 * @return The current version of this plugin.
	 */
	public function getVersion() {
		return '1.0.0';
	}

	/**
	 * Returns descriptive information about the plugin.
	 * This method is called by the plugin manager,
	 *
	 * @return An array containing meta information about this plugin.
	 */
	public function getInfo() {
		return array(
			'label' => 'Individuelles Hintergrundbild und Logo im Backend',
			'description' => file_get_contents(__DIR__ . '/description.html'),
			'autor' => 'VIISON GmbH',
			'copyright' => 'Copyright © 2014, VIISON GmbH',
			'license' => 'All rights reserved.',
			'support' => 'http://www.viison.com/',
			'link' => 'http://www.viison.com/',
			'version' => $this->getVersion()
		);
	}

	/**
	 * Default install method, which installs the plugin and its events.
	 *
	 * @return True if installation was successful, otherwise false.
	 */
	public function install() {
		return $this->update('install');
	}

	/**
	 * Adds new events and configurations:
	 *	- since 1.0.0:
	 *		TODO
	 *
	 * @param $oldVersion The currently installed version of this plugin.
	 * @return True if the update was successful, otherwise false.
	 */
	public function update($oldVersion) {
		$form = $this->Form();

		switch ($oldVersion) {
			case 'install':
				$this->subscribeEvent(
					'Enlight_Controller_Action_PostDispatch_Backend_Index',
					'onPostDispatchBackendIndex'
				);

				// Configuration option: Background image
				$form->setElement(
					'mediaselection',
					'backgroundImage',
					array(
						'label' => 'Hintergrundbild',
						'value' => null
					)
				);
				// Configuration option: Logo
				// The logo will be displayed in the lower left corner of the screen
				$form->setElement(
					'mediaselection',
					'logo',
					array(
						'label' => 'Logo',
						'value' => null
					)
				);
				$form->save();
				break;
			default:
				return false;
		}

		return true;
	}

	/**
	 * Uninstall method.
	 * This method is called by the plugin manager.
	 *
	 * @return True if uninstallation was successfull, otherwise false.
	 */
	public function uninstall() {
		return true;
	}

	/**
	 * Extend detail template
	 *
	 * @param $args The event parameters.
	 */
	public function onPostDispatchBackendIndex(Enlight_Event_EventArgs $arguments) {
		$controller = $arguments->getSubject();
		$view = $controller->View();

		// Add plugin template directory to Smarty search path
		$view->addTemplateDir($this->Path().'Views/');

		// Extend detail template
		$view->assign('viisonBackgroundImage', Shopware()->Plugins()->Backend()->ViisonBackendBackgroundImage()->Config()->get('backgroundImage'));
		$view->assign('viisonLogo', Shopware()->Plugins()->Backend()->ViisonBackendBackgroundImage()->Config()->get('logo'));
		$view->extendsTemplate('backend/viison_backend_background_image/index/plugin.tpl');
	}

}
