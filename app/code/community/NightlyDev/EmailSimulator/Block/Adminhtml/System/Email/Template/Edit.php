<?php
class NightlyDev_EmailSimulator_Block_Adminhtml_System_Email_Template_Edit extends Mage_Adminhtml_Block_System_Email_Template_Edit
{
    protected function _prepareLayout()
    {
        $this->setChild('simulator_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Run Template'),
                        'onclick' => "if($('template_select').value){\$('email_template_preview_form').action='".$this->getSimulatorUrl()."id/'+$('template_select').value+'/locale/'+$('locale_select').value;templateControl.preview();$('email_template_preview_form').action='".$this->getPreviewUrl()."';}else{alert('Please load a template before attempt to run the simulator.');}"
                    )
                )
        );
        return parent::_prepareLayout();
    }
    public function getPreviewButtonHtml()
    {
        return $this->getChildHtml('preview_button')
          . $this->getChildHtml('simulator_button');
    }
    /**
     * Return simulator action url for form
     *
     * @return string
     */
    public function getSimulatorUrl()
    {
        return $this->getUrl('*/nightlydevemailsimulator/run');
    }
}
