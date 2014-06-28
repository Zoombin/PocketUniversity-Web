<?php

/**
 *
 */
class ParameterWidget extends Widget {

    /**
     *
     * @see Widget::render()
     */
    public function render($data) {
        $content = $this->renderFile(ADDON_PATH . '/widgets/Parameter.html', $data);
        return $content;
    }

}