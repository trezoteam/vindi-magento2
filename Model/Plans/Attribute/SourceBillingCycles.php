<?php

namespace Vindi\Payment\Model\Plans\Attribute;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class SourceBillingCycles extends AbstractSource
{

    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('indefinitely'), 'value' => '']
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
