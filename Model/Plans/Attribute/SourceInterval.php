<?php

namespace Vindi\Payment\Model\Plans\Attribute;

class SourceInterval extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('mês(s)'), 'value' => 'months'],
                ['label' => __('dia(s)'), 'value' => 'days']
            ];
        }

        return $this->_options;
    }
}
