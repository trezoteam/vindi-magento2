<?php

namespace Vindi\Payment\Model\Plans\Attribute;

class SourceBillingCycles extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('por tem indefinido'), 'value' => '']
            ];

            $this->_options = $this->getRangeOptions();
        }

        return $this->_options;
    }

    /**
     * Options Select range 1 of 365
     * @return array
     */
    public function getRangeOptions()
    {
        for ($number = 1; $number < 365; $number++) {
            $range[] = [
                'label' => $number,
                'value' => $number
            ];
        }

        return $range;
    }
}
