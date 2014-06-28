<?php

/**
 *
 */
class ParaeditWidget extends Widget {

    /**
     *
     * @see Widget::render()
     */
    public function render($data) {
        //附件
        if($data['type']==3){
            $data['attach'] = getAttach($data['paraValue']);
        }
        $content = $this->renderFile(ADDON_PATH . '/widgets/Paraedit.html', $data);
        return $content;
    }

}