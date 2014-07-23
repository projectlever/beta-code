
    <?php if($this->params->get('basicLayout') == 1) : ?>
			<style type="text/css">
					.inner_wrapper {margin:0px auto;  width:<?php echo $this->params->get('maxWidth'); ?>px;max-width:<?php echo $this->params->get('maxWidth'); ?>px;min-width:<?php echo $this->params->get('maxWidth'); ?>px;}
			</style>
	<?php endif; ?>
	<?php if($this->params->get('basicLayout') == 2) : ?>
			<style type="text/css">
					.inner_wrapper {width:<?php echo $this->params->get('maxWidth'); ?>px;max-width:<?php echo $this->params->get('maxWidth'); ?>px;min-width:<?php echo $this->params->get('maxWidth'); ?>px;}
			</style>
	<?php endif; ?>
	<?php if($this->params->get('basicLayout') == 3) : ?>
			<style type="text/css">
					.inner_wrapper {width:100%;max-width:100%;min-width:100%;}
			</style>
	<?php endif; ?>
	
	<?php if($this->params->get('basicLayout') == 4) : ?>
			<style type="text/css">
					.inner_wrapper {margin:0px auto; width:100%;max-width:<?php echo $this->params->get('maxWidth'); ?>px;min-width:<?php echo $this->params->get('minWidth'); ?>px;}
			</style>
	<?php endif; ?>
	
	<?php if($this->params->get('basicLayout') == 5) : ?>
			<style type="text/css">
				.sidebar_area_wrapper {position:fixed; width:<?php echo $this->params->get('sidebarWidth'); ?>px;  left:0; top:0; bottom:0;min-height: 100%;height: auto !important;height: 100%;}
				.sidebar_area_wrapper .inner_wrapper {margin:0;width:100%; min-width:100%; max-width:100%;}
				.inner_wrapper {float:left; margin-left:<?php echo $this->params->get('sidebarWidth'); ?>px;max-width:<?php echo $this->params->get('maxWidth'); ?>px;min-width:<?php echo $this->params->get('maxWidth'); ?>px;}
				</style>
	<?php endif; ?>
	
	<?php if($this->params->get('basicLayout') == 6) : ?>
			<style type="text/css">
				.sidebar_area_wrapper {position:fixed; width:<?php echo $this->params->get('sidebarWidth'); ?>px;  left:0; top:0; bottom:0;min-height: 100%;height: auto !important;height: 100%;}
				.sidebar_area_wrapper .inner_wrapper {margin:0;width:100%; min-width:100%; max-width:100%;}
				.inner_wrapper {margin-left:<?php echo $this->params->get('sidebarWidth'); ?>px;max-width:<?php echo $this->params->get('maxWidth'); ?>px;min-width:<?php echo $this->params->get('minWidth'); ?>px;}
				
				</style>
	<?php endif; ?>
		
	<!-- Adds contrast color -->
	<style type="text/css">
		a{color:#<?php echo $this->params->get('contrastColor'); ?>;}
		.brand, .sourrounding_wrapper.logo_area_wrapper ul.nav > li a:hover, .rounded-background, h2.module-title:after, .pricing_table.featured_plan:before{background-color:#<?php echo $this->params->get('contrastColor'); ?>;}
		.sourrounding_wrapper.logo_area_wrapper ul.nav > li.active a {border-bottom:2px solid #<?php echo $this->params->get('contrastColor'); ?>;}
		.footer_area_wrapper h3 span, .footer_area_wrapper h4 span {border-bottom:1px solid #<?php echo $this->params->get('contrastColor'); ?>;}
		.footer_area_wrapper a {color:#ccc;}
	</style>

