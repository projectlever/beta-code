<?php
/**
 * @package 	Jtouch25
 * @copyright	Copyright (C) 2011 - 2012 MobileMeWs.com. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restricted access');

$app                	= JFactory::getApplication('site');
$document 				= JFactory::getDocument();

$thisTplUrl 			=  $this->baseurl. '/templates/' . $this->template;
$thisTplPath 			= JPATH_ROOT.DS.'templates'.DS.$this->template;
$jqmPath 				= $thisTplUrl.'/client-libs/jquerymobile/11';
$jqmVer					= '1.1.1'; // since 2.5.11
$tplVer					= 'v2512';
$pageId 				= JRequest::getInt('Itemid', 0);
$debug					= ($this->params->get('jtouch-debug', 1)  == 1)? true:false;
$minfile				= $debug? '' : '.min';

$googleAdsence			= (int)$this->params->get('jtouch-google-adsence', 0);
$jqmCssSubfix			= ( (int)$this->params->get('jtouch-jqm-css', 1) == 1)? '' : '.structure';

// @Since 2.5.10: module mapping
$mmjtouch_banner		= $this->params->get('jtouch-mm-jtouch-banner', 'jtouch-banner');
$mmjtouch_top 			= $this->params->get('jtouch-mm-jtouch-top', 'jtouch-top');
$mmjtouch_user1			= $this->params->get('jtouch-mm-jtouch-user1', 'jtouch-user1');
$mmjtouch_user2 		= $this->params->get('jtouch-mm-jtouch-user2', 'jtouch-user2');
$mmjtouch_bottom		= $this->params->get('jtouch-mm-jtouch-bottom', 'jtouch-bottom');
$mmjtouch_footer 		= $this->params->get('jtouch-mm-jtouch-footer', 'jtouch-footer');
$mmjtouch_breadcrumb	= $this->params->get('jtouch-mm-jtouch-breadcrumb', 'jtouch-breadcrumb');

// Load Jtouch libs
require_once ($thisTplPath .DS .'utils' .DS .'jtouch25.utils.php');

// For VM Only
if( (int)$this->params->get('jtouch-virtuemart-load', 0) == 1) {
	Jtouch25Utils::vmLoadJsFiles($thisTplUrl.'/html/com_virtuemart/assets', $minfile);
}

// For Kunena Only
if( (int)$this->params->get('jtouch-kunena-load', 0) == 1) {
	Jtouch25Utils::kunenaLoadJsFiles($minfile);
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="generator" content="Joomla 2.5, with Jtouch template (c) 2011 - 2012 mobilemews.com" />
		
		<?php 
			$iOSIconPath = $this->params->get('jtouch-ios-icons', '') ;
			if($iOSIconPath!=''):
				$iOSIconPath = $thisTplUrl.'/'.$iOSIconPath;
		?>
		<!-- Icons -->
	    <link rel="shortcut icon" 									href="<?php echo $iOSIconPath;?>/favicon.ico?<?php echo $tplVer;?>" />
	    <link rel="apple-touch-icon-precomposed" sizes="144x144" 	href="<?php echo $iOSIconPath;?>/apple-touch-icon-144-precomposed.png?<?php echo $tplVer;?>" />
	    <link rel="apple-touch-icon-precomposed" sizes="114x114" 	href="<?php echo $iOSIconPath;?>/apple-touch-icon-114-precomposed.png?<?php echo $tplVer;?>" />
	    <link rel="apple-touch-icon-precomposed" sizes="72x72" 		href="<?php echo $iOSIconPath;?>/apple-touch-icon-72-precomposed.png?<?php echo $tplVer;?>" />
	    <link rel="apple-touch-icon-precomposed" 					href="<?php echo $iOSIconPath;?>/apple-touch-icon-57-precomposed.png?<?php echo $tplVer;?>" />
		<?php endif;?>
		
		<!-- Css -->
		<link rel="stylesheet" href="<?php echo $jqmPath;?>/resources/jquery.mobile<?php echo $jqmCssSubfix.'-'.$jqmVer.$minfile;?>.css?<?php echo $tplVer;?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo $thisTplUrl;?>/client-libs/simpledialog2/jquery.mobile.simpledialog.min.css?<?php echo $tplVer;?>" type="text/css" />
		
		<link rel="stylesheet" href="<?php echo $thisTplUrl;?>/css/template<?php echo $minfile;?>.css?<?php echo $tplVer;?>" type="text/css" />
		<?php 
			// Check if we have a folder named 'customize' within jtouch25 template
			// If yes, will load template-overwrite.css - it is useful for doing hack to Jtouch css without overwrite by next upgrade
			if( file_exists($thisTplPath.DS.'customize'.DS.'template-overwrite.css') ):
		?>
			<link rel="stylesheet" href="<?php echo $thisTplUrl;?>/customize/template-overwrite.css?<?php echo $tplVer;?>" type="text/css" />
		<?php 
			endif;
		?>
		
		<!--[if IEMobile]>
		<![endif]--> 
		
		<?php Jtouch25Utils::writeCss(); ?>

		<!-- Js -->
		<script type="text/javascript">
			var jtouchPageId = <?php echo $pageId;?>;
		</script>
		<?php if( (int)$this->params->get('jtouch-jquery-load', 1) == 1):?>
		<script src="<?php echo $jqmPath;?>/jquery.min.js?<?php echo $tplVer;?>" type="text/javascript"></script>
		<?php endif;?>
		
		<script src="<?php echo $jqmPath;?>/jqm.init<?php echo $minfile;?>.js?<?php echo $tplVer;?>" type="text/javascript"></script>
		<script type="text/javascript">
		(function($){
			$(document).bind("mobileinit", function() {
				$.mobile.defaultPageTransition = '<?php echo $this->params->get('jtouch-page-transition', 'pop');?>';
			});
		})(jQuery);
		</script>
		<script src="<?php echo $jqmPath;?>/jquery.mobile-<?php echo $jqmVer.$minfile;?>.js?<?php echo $tplVer;?>" type="text/javascript"></script>
		<script src="<?php echo $jqmPath;?>/jtouch.core<?php echo $minfile;?>.js?<?php echo $tplVer;?>" type="text/javascript"></script>
		<script src="<?php echo $jqmPath;?>/jqm.domready<?php echo $minfile;?>.js?<?php echo $tplVer;?>" type="text/javascript"></script>
		
		<script src="<?php echo $jqmPath;?>/jquery.validate.190.min.js?<?php echo $tplVer;?>" type="text/javascript"></script>
		<script src="<?php echo $thisTplUrl;?>/client-libs/simpledialog2/jquery.mobile.simpledialog2.min.js?<?php echo $tplVer;?>" type="text/javascript"></script>
		
		<?php if( (int)$this->params->get('jtouch-ios-add-app', 0) == 1 ): ?>
		<script type="text/javascript">
		var addToHomeConfig = {
			animationIn: 'bubble',
			animationOut: 'drop',
			lifespan:10000,
			expire:2,
			touchIcon:true,
			returningVisitor: true,
			message:'<?php echo JText::_("TPL_JTOUCH25_ADD_2_HOME_TEXT");?>'
		};
		</script>
		<link rel="stylesheet" href="<?php echo $thisTplUrl; ?>/client-libs/add2home/add2home.min.css?<?php echo $tplVer;?>" />
		<script type="text/javascript" src="<?php echo $thisTplUrl; ?>/client-libs/add2home/add2home.min.js?<?php echo $tplVer;?>"></script>
		<?php endif;?>
		
		<?php if( (int)$this->params->get('jtouch-google-analytics', 0) == 1): ?>
		<script type="text/javascript">
			var _gaq = _gaq || [];
			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
		<?php endif; ?>
		
		<jdoc:include type="head" />
		
		<?php 
			// Check if there has a template override for JQM, in  clients-lib/jquerymobile/11/resources/jtouch-custom.css
			if( file_exists($thisTplPath.DS.'client-libs'.DS.'jquerymobile'.DS.'11'.DS.'resources'.DS.'jtouch-custom.css') ):
		?>
		<link rel="stylesheet" href="<?php echo $jqmPath;?>/resources/jtouch-custom.css?<?php echo $tplVer;?>" type="text/css" />
		<?php endif;?>
	</head>
	<?php
		$extraTopCls = '';
		$hasBanner = false; 
		if(strlen( $this->params->get('jtouch-banner', '') ) > 4 || $this->countModules($mmjtouch_banner)){
			$hasBanner = true;
			$extraTopCls = 'jtouch-extratop';
		}
		
		$pageTheme = $this->params->get('jtouch-theme', 'd');
		$headerTheme = $this->params->get('jtouch-header-theme', 'b');
		$footerTheme = $this->params->get('jtouch-footer-theme', 'd');
			
		$fixedHeader = '';
		if( (int)$this->params->get('jtouch-fixed-header', 0) == 1 ){
			$fixedHeader = ' data-position="fixed" ';
			$extraTopCls .= " jtouch-fixed-header";
		}
			
		$fixedFooter = '';
		if( (int)$this->params->get('jtouch-fixed-footer', 0) == 1 ){
			$fixedFooter = ' data-position="fixed" ';
		}
		
		$headerBtnView = (int)$this->params->get('jtouch-header-button-view', 1);
		$extraBtnCls = '';
		$extraBtnData = '';
		if($headerBtnView == 3){
			$extraBtnCls = 'button-no-text';
			$extraBtnData = ' data-iconpos="notext" ';
		}

	?>
	<body class="<?php echo $extraTopCls;?>">
		<!-- MAIN PAGE -->
		<?php  if( $hasBanner) : ?>
		<div id="jtouch-logo">
			<?php if( strlen( $this->params->get('jtouch-banner', '') ) > 4 ): ?>
			<a href="index.php"><img src="<?php echo $this->params->get('jtouch-banner'); ?>"/></a>
			<?php endif; ?>
			
			<?php if ($this->countModules($mmjtouch_banner)): ?>
				<jdoc:include type="modules" name="<?php echo $mmjtouch_banner; ?>" />
			<?php endif; ?>	
			<div class="clr"></div>
		</div>
		<?php endif;?>
		
		 <a href="#"  id="hidden_link" style="display:none;"></a>
		 <a id="topofpage" style="display:none;"></a>
		
		<div data-role="page" id="page-<?php echo $pageId;?>" data-add-back-btn="true" data-back-btn-text="<?php echo JText::_('TPL_JTOUCH25_BACK_BUTTON_TEXT');?>" data-theme="<?php echo $pageTheme;?>">
			<div data-role="header" <?php echo $fixedHeader;?> data-theme="<?php echo $headerTheme;?>" class="main-page-header">
				<?php 
				$backBtn = (int)$this->params->get('jtouch-always-show-back', 0);
				if( $backBtn > 0): ?>
				<div data-role="controlgroup" data-type="horizontal" class="header-tools-left" data-direction="reverse">
					<a href="index.php" data-direction="reverse" data-rel="back" <?php if($backBtn == 2): ?> data-iconpos="notext" class="button-no-text" <?php endif;?>  data-icon="arrow-l" data-role="button" id="jtouch-back-btn" > <?php echo JText::_('TPL_JTOUCH25_BACK_BUTTON_TEXT');?> </a>
				</div>
				<?php endif; ?>
				
				<?php if ($this->countModules('jtouch-menu or jtouch-search or jtouch-tools')): ?>
				<div data-role="controlgroup" data-type="horizontal" class="header-tools-center" data-direction="reverse">
					<?php if ($this->countModules('jtouch-tools')): ?>
					<a href="#page-tools-<?php echo $pageId;?>" <?php if($headerBtnView!=2): ?>data-icon="star" <?php endif;?> <?php echo $extraBtnData; ?> data-role="button" id="jtouch-tools" class="<?php echo $extraBtnCls;?>"><?php echo JText::_('TPL_JTOUCH25_MENU_TOOL');?></a>
					<?php endif;?>
					
					<?php if ($this->countModules('jtouch-search')): ?>
					<a href="#page-search-<?php echo $pageId;?>" <?php if($headerBtnView!=2): ?> data-icon="search" <?php endif;?> <?php echo $extraBtnData; ?> data-role="button" id="jtouch-search" class="<?php echo $extraBtnCls;?>"><?php echo JText::_('TPL_JTOUCH25_MENU_SEARCH');?></a>
					<?php endif;?>
					
					<?php if ($this->countModules('jtouch-menu')): ?>
					<a href="#page-menu-<?php echo $pageId;?>" <?php if($headerBtnView!=2): ?> data-icon="grid" <?php endif;?> <?php echo $extraBtnData; ?> data-role="button" id="jtouch-menu" class="<?php echo $extraBtnCls;?>"><?php echo JText::_('TPL_JTOUCH25_MENU_MENU');?></a>
					<?php endif;?>
				</div>
				<?php endif;?>
				
				<!-- No title -->
				<h1></h1>
				
				<?php if ($this->countModules('jtouch-rtools')): ?>
				<div data-role="controlgroup" data-type="horizontal" class="header-tools-right" data-direction="reverse">
					<a href="#page-rtool-<?php echo $pageId;?>" <?php if($headerBtnView!=2): ?> data-icon="gear" <?php endif;?> <?php echo $extraBtnData; ?> data-role="button" id="jtouch-rtool-btn" class="<?php echo $extraBtnCls;?>"><?php echo JText::_('TPL_JTOUCH25_MENU_RH_TOOL');?></a>
				</div>
				<?php endif;?>
			</div>
			
			<div data-role="content" id="jtouch-page-body">
				<?php if ($this->countModules('jtouch-nav')): ?>
				<div id="jtouch-nav">
					<div class="inner-nav"><jdoc:include type="modules" name="jtouch-nav" /></div>
					<div class="clr"></div>
				</div>
				<?php endif; ?>
				
				<?php if( $googleAdsence == 1 || $googleAdsence == 3): ?>
				<div id="googleadgoeshere1">
					<div class="googleadgoeshere"> </div>
					<div class="clr"></div>
				</div>
				<?php endif;?>
				
				<?php if ($this->countModules($mmjtouch_top)): ?>
				<div id="jtouch-top">
					<jdoc:include type="modules" name="<?php echo $mmjtouch_top;?>"  style="xhtml" />
				</div>
				<?php endif; ?>
				
				<?php if ($this->countModules($mmjtouch_user1)): ?>
				<div id="jtouch-user1">
					<jdoc:include type="modules" name="<?php echo $mmjtouch_user1;?>" style="xhtml" />
				</div>
				<?php endif; ?>
				
				<jdoc:include type="message" />
				
				<?php 
					$turnoffIds = trim($this->params->get('jtouch-turnoff-ids', '-1'));
					$turnedOff = false;
					
					if($turnoffIds == '-1' || $turnoffIds == '0' || $turnoffIds == ''){
						$turnedOff = false;
					}else{
						$turnoffIds = '0,'. $turnoffIds;
						$turnoffIds = explode(',', $turnoffIds);
						$currentID 	= JRequest::getInt('Itemid', 0);						
						if($currentID > 0){
							if(in_array($currentID, $turnoffIds)){
								$turnedOff = true;
							}
						}
					}
					
					if(!$turnedOff) :
				?>
					<div id="jdoc-component">
						<jdoc:include type="component" />
					</div>
					
				<?php endif;?>
				
				<?php if ( $this->countModules($mmjtouch_user2) ): ?>
				<div class="clr"></div>
				<div id="jtouch-user2">
					<jdoc:include type="modules" name="<?php echo $mmjtouch_user2;?>" style="xhtml" />
				</div>
				<?php endif; ?>				
				
				<?php if ($this->countModules($mmjtouch_breadcrumb)): ?>
				<div class="clr"></div>
				<div id="page-pathway">
					<jdoc:include type="modules" name="<?php echo $mmjtouch_breadcrumb;?>"   />
				</div>
				<?php endif; ?>
				
				<?php if ($this->countModules($mmjtouch_bottom)): ?>
				<div class="clr"></div>
				<div id="jtouch-bottom">
					<jdoc:include type="modules" name="<?php echo $mmjtouch_bottom;?>" style="xhtml" />
				</div>
				<?php endif; ?>
					
				<?php if( $googleAdsence == 2 || $googleAdsence == 3): ?>
				<div id="googleadgoeshere2">
					<div class="googleadgoeshere"> </div>
					<div class="clr"></div>
				</div>
				<?php endif;?>
			</div>
			
			<div data-role="footer" <?php echo $fixedFooter;?> data-theme="<?php echo $footerTheme;?>">
				<?php if ($this->countModules($mmjtouch_footer) || $this->params->get('jtouch-show-powerby', 1) == 1 ): ?>
				<div id="page-footer">
					<jdoc:include type="modules" name="<?php echo $mmjtouch_footer;?>" />
					
					<?php if($this->params->get('jtouch-show-powerby', 1) == 1): ?>
					<div class="jtouch-copyright"><a href="http://www.mobilemews.com" target="_blank" title="Jtouch - mobile template for Joomla, VirtuMart and Kunena">mobile template by mobilemews.com</a></div>
					<?php endif;?>
				</div>
				<?php endif; ?> 
			</div>
		</div>

		<!-- MENU PAGE -->
		<div data-role="page" id="page-menu-<?php echo $pageId;?>" data-add-back-btn="true" data-back-btn-text="<?php echo JText::_('TPL_JTOUCH25_BACK_BUTTON_TEXT');?>" data-theme="<?php echo $pageTheme;?>">
			<div data-role="header" <?php echo $fixedHeader;?> data-theme="<?php echo $headerTheme;?>">
				<h1><?php echo JText::_('TPL_JTOUCH25_MENU_MENU');?></h1>
			</div>
			<div data-role="content">
				<?php 
					$jtouchDesktopTpl = (int)$this->params->get('jtouch-desktop-template', -1);
					$url = clone JFactory::getURI();
					$params = array('jtpl' => $jtouchDesktopTpl);
					$params = array_merge( $url->getQuery( true ), $params );
					$query = $url->buildQuery( $params );
					$url->setQuery( $query );

					if( $jtouchDesktopTpl != -1 ): 
				?>
					<a href="<?php echo $url->toString();?>" data-icon="gear" data-theme="<?php echo $pageTheme;?>" data-role="button" target="_self" data-mini="true"><?php echo JText::_('TPL_JTOUCH25_SWITCH_TO_DESKTOP');?></a>
				<?php endif;?>
				<jdoc:include type="modules" name="jtouch-menu" style="xhtml" />
			</div>
		</div>
		<!-- MENU PAGE:END -->
		
		<!-- HEADER RIGHT HAND TOOL PAGE -->
		<div data-role="page" id="page-rtool-<?php echo $pageId;?>" data-add-back-btn="true" data-back-btn-text="<?php echo JText::_('TPL_JTOUCH25_BACK_BUTTON_TEXT');?>" data-theme="<?php echo $pageTheme;?>">
			<div data-role="header" <?php echo $fixedHeader;?> data-theme="<?php echo $headerTheme;?>">
				<h1><?php echo JText::_('TPL_JTOUCH25_MENU_RH_TOOL');?></h1>
			</div>
			<div data-role="content">
				<jdoc:include type="modules" name="jtouch-rtools" />
			</div>
		</div>
		<!-- HEADER RIGHT HAND TOOL PAGE:END -->
		
		<!-- SEARCH PAGE -->
		<div data-role="page" id="page-search-<?php echo $pageId;?>" data-add-back-btn="true" data-back-btn-text="<?php echo JText::_('TPL_JTOUCH25_BACK_BUTTON_TEXT');?>" data-theme="<?php echo $pageTheme;?>">
			<div data-role="header" <?php echo $fixedHeader;?> data-theme="<?php echo $headerTheme;?>">
				<h1><?php echo JText::_('TPL_JTOUCH25_MENU_SEARCH');?></h1>
			</div>
			<div data-role="content">
				<jdoc:include type="modules" name="jtouch-search" />
			</div>
		</div>
		<!-- SEARCH PAGE:END -->
		
		<!-- TOOLS PAGE -->
		<!-- tools module output -->
		<div data-role="page" id="page-tab-modules" data-add-back-btn="true" data-back-btn-text="<?php echo JText::_('TPL_JTOUCH25_BACK_BUTTON_TEXT');?>" data-theme="<?php echo $pageTheme;?>">
			<div data-role="content">
				<jdoc:include type="modules" name="jtouch-tools" theme="<?php echo $pageTheme;?>" style="jqmTabs" />
			</div>
		</div>
		<!-- tools page wrapper -->
		<div data-role="page" id="page-tools-<?php echo $pageId;?>" data-add-back-btn="true" data-back-btn-text="<?php echo JText::_('TPL_JTOUCH25_BACK_BUTTON_TEXT');?>" data-theme="<?php echo $pageTheme;?>">
			<div data-role="header" <?php echo $fixedHeader;?> data-theme="<?php echo $headerTheme;?>">
				<?php if ($this->countModules('jtouch-menu')): ?>
				<a href="#page-menu-<?php echo $pageId;?>" data-icon="gear" class="ui-btn-right"><?php echo JText::_('TPL_JTOUCH25_MENU_MENU');?></a>
				<?php endif;?>
				<h1><?php echo JText::_('TPL_JTOUCH25_MENU_TOOL');?></h1>
				<div id="page-tools-navbar"></div>
			</div>
			<div data-role="content">
				<div id="page-tools-content"></div>
			</div>
		</div>
		<!-- TOOLS PAGE:END -->
			
		<!-- EX JS SCRIPTS FOR JTOUCH -->
		<?php Jtouch25Utils::writeJs($this->params);?>
		
		<?php if( (int)$this->params->get('jtouch-google-analytics', 0) == 1){
			require_once 'page-elements/google-analytics.php';
		} ?>
		
		<?php if( $googleAdsence != 0): ?>
		<!-- Google Adsence -->
		<div id="jtouchadsense" style="display:none;">
			<?php require_once 'page-elements/google-adsence.php';?>
		</div>
		
		<script type="text/javascript">
		(function($){
			$(document).ready(function () {
				try{
					var ads_top = $("#jtouchadsense").find("iframe");
					console.log('Adsence initializing..: ');
					
					//This is where the ads will show when the page is first loaded
					$(ads_top).appendTo(".googleadgoeshere");
					$("#jtouchadsense").remove();

					$('div').live('pagehide',function(event, ui){
						//This is where the ads will show when a page transition
						$(ads_top).appendTo(".googleadgoeshere");
					});
				} catch(err) {
					console.log('Found some errors while loading Google Adsence:');
					console.log(err);
			    }
			});
		})(jQuery);
		</script>
		<!-- / Google Adsence -->
		<?php endif; ?>
		
		<!-- EX JS SCRIPTS FOR JTOUCH: END -->
		
  	</body>
</html>