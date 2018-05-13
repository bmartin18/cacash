<?php

namespace AppBundle\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Class ContentExtension
 */
class ContentExtension extends Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('price', array($this, 'priceFilter')),
        ];
    }

    /**
     * @param int    $value
     * @param int    $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     *
     * @return string
     */
    public function priceFilter($value, $decimals = 2, $decPoint = ',', $thousandsSep = ' ')
    {
        $value = $value / 100;

        $price = number_format($value, $decimals, $decPoint, $thousandsSep);
        $price = $price.'€';

        return $price;
    }
}
