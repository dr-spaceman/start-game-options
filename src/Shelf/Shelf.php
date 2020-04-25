<?php

namespace Vgsite\Shelf;

/**
 * Class to organize, arrange, and output a shelf of products
 */

class Shelf {
	
	/** @var integer number of items currently on the shelf */
	public $num_items = 0;

	/** @var array of shelf items added */
	private $items = array();

	/** @var integer the default item hight, obviously! Can be changed with contructions params 
		If changed, also should be changed in class ShelfItem ... bad programming! */
	private $default_item_height = 120;

	/**
	 * Construct a new shelf with specified properties
	 * @param array $props Property values
	 */
	
	public function __construct($props = array()) {

		if (isset($props['default_item_height'])) $this->default_item_height = (int) $props['default_item_height'];

		//echo "Shelf::__Construct;"; print_r($props);
	}

	public function addItem(ShelfItem $shelfitem): {

		$this->items[] = $shelfitem;
		$this->num_items++;

		return this;

	}

	/**
	 * return a shelf populated with items added
	 * @param array $props Propertis to configure output [output_nav bool, output_container bool]
	 * @return string HTML
	 */
	public function output($props = array()) {

		$output_html = "<!-- Shelf:output ({$this->num_items} items) -->";

		if ($this->num_items == 0) return $output_html;

		// Add a nav panel if there's enough items
		if ($this->num_items > 5 && $props['output_nav'] !== false) {
			$output_html.= '<a href="/js.htm" title="Traverse left" class="trav prev" onclick="shelf.traverse($(this).parent(), -1, 6); return false;"></a><a href="/js.htm" title="Traverse right" class="trav next" onclick="shelf.traverse($(this).parent(), 1, 6); return false;"></a>' . PHP_EOL;
		}
		
		foreach ($this->items as $item) {
			$output_html.= $item->output();
		}
		
		if ($props['output_container'] !== false) {
			$output_html = '<div class="shelf-container" style="width:' . ($this->num_items * 198 + 800) . 'px;">' . $output_html . '</div>';
		}
		
		return $output_html;

	}

}