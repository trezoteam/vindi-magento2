<?php

namespace Vindi\Payment\Model\Plans\Attribute;

class SourceBillingTriggerDay extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('por tempo indeterminado'), 'value' => null],
                ['label' => __('1 vez'), 'value' => 1]
            ];

            $this->_options = $this->getRangeOptions();
        }

        return $this->_options;
    }

    /**
     * Options Select range 1 of 60
     * @return array
     */
    public function getRangeOptions()
    {
        for ($number = 2; $number < 60; $number++) {
            $range[] = [
                'label' => $number . 'vezes',
                'value' => $number
            ];
        }

        return $range;
    }
}
