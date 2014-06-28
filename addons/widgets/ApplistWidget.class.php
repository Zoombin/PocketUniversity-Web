<?php
/**
 * 应用Widget
 *
 */
class ApplistWidget extends Widget {

	public function render($data) {
		$content = $this->renderFile(ADDON_PATH . '/widgets/Applist.html', $data);
		return $content;
	}
}