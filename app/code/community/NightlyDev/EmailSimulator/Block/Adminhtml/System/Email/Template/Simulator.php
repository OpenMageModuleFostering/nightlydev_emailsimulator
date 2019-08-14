<?php
class NightlyDev_EmailSimulator_Block_Adminhtml_System_Email_Template_Simulator extends Mage_Adminhtml_Block_System_Email_Template_Preview
{
    const XML_PATH_DESIGN_EMAIL_LOGO            = 'design/email/logo';
    const XML_PATH_DESIGN_EMAIL_LOGO_ALT        = 'design/email/logo_alt';
    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $template Mage_Core_Model_Email_Template */
        $template = Mage::getModel('core/email_template');
        $templateId = $this->getRequest()->getParam('id');
        $templateLocale = $this->getRequest()->getParam('locale');
        $templateStore = 1;
        foreach(Mage::app()->getStores() as $store)
          if ($templateLocale==Mage::getStoreConfig('general/locale/code', $store->getId()))
            $templateStore = $store->getId();
        $variables = array();
        $defaultTemplates = $template->getDefaultTemplates();
        if ($templateId) {
          if (isset($defaultTemplates[$templateId])) {
            $data = &$defaultTemplates[$templateId];

            $templateText = Mage::app()->getTranslator()->getTemplateFile(
                $data['file'], 'email', $templateLocale
            );
            
            if (preg_match('/<!--@vars\s*((?:.)*?)\s*@-->/us', $templateText, $matches)) {
                $variablesString = str_replace("\n", '', $matches[1]);
            }
            
            if ($variablesString && is_string($variablesString)) {
                $variablesString = str_replace("\n", '', $variablesString);
                $variables = Zend_Json::decode($variablesString);
            }
            
          }
        }
        $template->setTemplateType($this->getRequest()->getParam('type'));
        $template->setTemplateText($this->getRequest()->getParam('text'));
        $template->setTemplateStyles($this->getRequest()->getParam('styles'));

        /* @var $filter Mage_Core_Model_Input_Filter_MaliciousCode */
        $filter = Mage::getSingleton('core/input_filter_maliciousCode');

        $template->setTemplateText(
            $filter->filter($template->getTemplateText())
        );
        
        Varien_Profiler::start("email_template_proccessing");
        
        foreach($variables as $pattern => $replacement) {
          if ($pattern=='var logo_alt') $replacement = $this->_getLogoAlt($templateStore);
          if ($pattern=='var logo_url') $replacement = $this->_getLogoUrl($templateStore);
          if ($pattern=='var store.getFrontendName()') $replacement = $this->_getLogoAlt($templateStore);
          if (preg_match('/store url="(.*)"/', $pattern, $matches)) {
              $replacement = Mage::app()->getStore($templateStore)->getUrl($matches[1]);
          }
          $template->setTemplateText(
              str_replace('{{'.$pattern.'}}', $replacement, $template->getTemplateText())
          );
        }
        
        $template->setTemplateText(
            str_replace('{{var store.getFrontendName()}}', $this->_getLogoAlt($templateStore), $template->getTemplateText())
        );
        if (preg_match('/store url="(.*)"/', $template->getTemplateText(), $matches)) {
            $template->setTemplateText(
                str_replace($matches[0], Mage::app()->getStore($templateStore)->getUrl($matches[1]), $template->getTemplateText())
            );
        }
        
        $templateProcessed = $template->getProcessedTemplate(array(), true);
        
        if ($template->isPlain()) {
            $templateProcessed = "<pre>" . htmlspecialchars($templateProcessed) . "</pre>";
        }

        Varien_Profiler::stop("email_template_proccessing");

        return $templateProcessed;
    }

    /**
     * Return logo URL for emails
     * Take logo from skin if custom logo is undefined
     *
     * @param  Mage_Core_Model_Store|int|string $store
     * @return string
     */
    protected function _getLogoUrl($store)
    {
        $store = Mage::app()->getStore($store);
        $fileName = $store->getConfig(self::XML_PATH_DESIGN_EMAIL_LOGO);
        if ($fileName) {
            $uploadDir = Mage_Adminhtml_Model_System_Config_Backend_Email_Logo::UPLOAD_DIR;
            $fullFileName = Mage::getBaseDir('media') . DS . $uploadDir . DS . $fileName;
            if (file_exists($fullFileName)) {
                return Mage::getBaseUrl('media') . $uploadDir . '/' . $fileName;
            }
        }
        return Mage::getDesign()->getSkinUrl('images/logo_email.gif');
    }

    /**
     * Return logo alt for emails
     *
     * @param  Mage_Core_Model_Store|int|string $store
     * @return string
     */
    protected function _getLogoAlt($store)
    {
        $store = Mage::app()->getStore($store);
        $alt = $store->getConfig(self::XML_PATH_DESIGN_EMAIL_LOGO_ALT);
        if ($alt) {
            return $alt;
        }
        return $store->getFrontendName();
    }

}
