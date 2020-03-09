<?php

namespace Vindi\Payment\Model\Plans\Attribute;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class SourceInterval extends AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('month(s)'), 'value' => 'months'],
                ['label' => __('day(s)'), 'value' => 'days']
            ];
        }

        return $this->_options;
    }
}
