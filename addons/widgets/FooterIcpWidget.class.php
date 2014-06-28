<?php

/**
 * 应用Widget
 *
 */
class FooterIcpWidget extends Widget {

    public function render($data) {
        $content = $this->renderFile(ADDON_PATH . '/widgets/FooterIcp.html', $data);
        return $content;
    }

}