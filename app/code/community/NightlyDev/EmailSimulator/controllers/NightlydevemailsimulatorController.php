<?php
class NightlyDev_EmailSimulator_NightlydevemailsimulatorController extends Mage_Adminhtml_Controller_Action
{
    public function runAction()
    {
        $this->loadLayout('systemPreviewNightlyDevSimulator');
        $this->renderLayout();
    }
}