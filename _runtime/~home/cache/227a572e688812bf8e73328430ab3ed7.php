<?php if (!defined('THINK_PATH')) exit();?><script>
function _widget_weibo_start(page_title, tpl_data, param_data) {
	// 弹出窗的标题
	var page_title  = page_title ? page_title : "<?php echo ($page_title); ?>";
    ui.box.load( "<?php echo ($url); ?>",{title:page_title,closeable:true},'POST','data='+tpl_data);
}
</script>