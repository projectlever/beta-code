<?php
$mobileDomain = "http://m.projectlever.com";
$no_redirect = @$_REQUEST['no_redirect'];
if($no_redirect != "true")
{
    $agent = @$_SERVER['HTTP_USER_AGENT'];
    @ini_set('default_socket_timeout',1);
    $handle = @fopen("http://mobile.dudamobile.com/api/public/detect?ua=" . urlencode($agent), "r");
    @stream_set_timeout($handle, 1);

    /* check if we should redirect*/
    $result = @fread ( $handle , 1 );

    @fclose($handle);
    if ($result == "y") {
        $currenturl = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $mobileUrl = $mobileDomain ."?url=" .urlencode($currenturl);
        $mobileUrl=$mobileUrl."&dm_redirected=true";
        header("Location: ".$mobileUrl);
        exit;
    }
}
?>

<?php 
/**
 * @copyright Copyright (C) 2009/2010 by pixelsparadise.com.
 * @license Commercial/Proprietery - released under a commercial license
 * design by: Holger Koenemann
 * Based on pixelsparadise.com Jooms framework version 1.0.6 - 16. June 2011
 */
defined('_JEXEC') or die;

/* The following line loads the MooTools JavaScript Library */
JHTML::_('behavior.mootools');

/* The following line gets the application object for things like displaying the site name */
$app = JFactory::getApplication();
?>

<!DOCTYPE html>
<html xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
  
<script src="http://static.dudamobile.com/DM_redirect.js" type="text/javascript"></script>
<script type="text/javascript">DM_redirect("http://m.projectlever.com");</script>
  
<jdoc:include type="head" />

<!-- Loads Master CSS -->
<link rel="stylesheet" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/basic.css" type="text/css" media="screen, projection" />
  
<!-- Loads additional CSS file to edit/customize or overwrite the base/default classes-->  
<link rel="stylesheet" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/custom.css" type="text/css" media="screen, projection" />

<?php if($this->params->get('cssThree') == 1) : ?>
<!-- Loads CSS3 file with some nice modern effects-->  
  <link rel="stylesheet" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/css3.css" type="text/css" media="screen, projection" />
<?php endif; ?>  

<!-- Loads SubTheme CSS file-->  
<link rel="stylesheet" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/subthemes/<?php echo $this->params->get('subTheme'); ?>" type="text/css" media="screen, projection" />

<!--[if IE 7]>
<link rel="stylesheet" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/ie7.css" type="text/css" media="screen, projection">
<![endif]-->

<?php if($this->params->get('readDirection') == 1) : ?>
<!-- Loads RTL stylsheet if enabled-->  
  <link rel="stylesheet" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/rtl.css" type="text/css" media="screen, projection" />
<?php endif; ?>  

<!-- Loads JQuery Framework -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

<!-- Loads Lightbox script -->
<script type="text/javascript" src="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/js/slimbox.js"></script>

<?php if($this->countModules('header1 or header2 or header3 or header4 or header5 or header6 or header7 or header8 or header9 or header10 or header11 or header12')) : ?>

<!--Starting Slider Script-->
  
  <!-- SLIDES Slider -->
    
  <script src="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/js/slides.min.jquery.js"></script>
    <script>
    $(function(){
      $('#slides').slides({
      preload: true,
      preloadImage: '<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/images/load_slide.gif',
      <?php if($this->params->get('sliderEffect') == 1) : ?>effect: 'fade',<?php endif; ?>
      <?php if($this->params->get('sliderEffect') == 2) : ?>effect: 'slide',<?php endif; ?>
      crossfade: false,
      slideSpeed: <?php echo $this->params->get('sliderChange'); ?>,
      fadeSpeed: <?php echo $this->params->get('sliderChange'); ?>,
      <?php if($this->params->get('sliderArrows') == 0) : ?>
      generateNextPrev: false,<?php endif; ?>
      <?php if($this->params->get('sliderArrows') == 1) : ?>
      generateNextPrev: true,<?php endif; ?>
      <?php if($this->params->get('sliderPagination') == 0) : ?>
      generatePagination: false,<?php endif; ?>
      <?php if($this->params->get('sliderPagination') == 1) : ?>
      generatePagination: true,<?php endif; ?>
      <?php if($this->params->get('sliderAuto') == 1) : ?>
      play: <?php echo $this->params->get('sliderTime'); ?>,
      <?php endif; ?>
      autoHeight: true,
      <?php if($this->params->get('hoverPause') == 2) : ?>hoverPause: true,<?php endif; ?>
      pause: 1000
      });
    });
  </script>
  
  <!-- SLIDES End -->
  
<!--End Slider Script-->

<?php endif; ?>

<!--Starting Suckerfish Script-->
<?php if($this->params->get('showSuckerfish') == 1) : ?>
 <script type="text/javascript"><!--//--><![CDATA[//><!--
startList = function() {
  if (document.all&&document.getElementById) {
    navRoot = document.getElementById("nav");
    for (i=0; i<navRoot.childNodes.length; i++) {
      node = navRoot.childNodes[i];
      if (node.nodeName=="span") {
        node.onmouseover=function() {
          this.className+=" over";
        }
        node.onmouseout=function() {
          this.className=this.className.replace(" over", "");
        }
      }
    }
  }
}
window.onload=startList;

//--><!]]>
</script>
<?php endif; ?>
<!--Suckerfish Script End-->

<!--Loads FavIcon-->
<link rel="shortcut icon" href="images/favicon.ico" />  
  <script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-33685683-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
  
<!-- ****************** Wrapper Start ****************** -->
<div class="wrapper">
  <!-- Overlay moduleposition "above" -->
<?php if($this->countModules('above')) : ?>  
  <?php require_once('includes/above.php'); ?>
<?php endif; ?>
  <div class="inner_wrapper">
    <div class="header_wrapper"> <!-- This container includes the whole top and header area-->

<!-- ****************** Top Area with Logo, topmenu etc.****************** -->
    <?php require_once('includes/toparea.php'); ?>
    
<!-- ****************** Header Area with Header image, top modules etc. ****************** -->
    <?php if($this->countModules('above_header or header1 or header2 or header3 or header4 or header5 or header6 or header7 or header8 or header9 or header10 or header11 or header12 or header or sub_header_left or sub_header_center or sub_header_right')) : ?>
      <!-- Including header area -->
      <?php require_once('includes/header.php'); ?>
    <?php endif; ?>
    
    </div> <!-- div.header_wrapper ends here-->
  
<!-- ****************** Main Area with all main content ****************** -->
    <div class="main <?php if($this->countModules('above_header or header1 or header2 or header3 or header4 or header5 or header6 or header7 or header8 or header9 or header10 or header11 or header12 or header or sub_header_left or sub_header_center or sub_header_right')) : ?><?php else: ?>header_inactive<?php endif; ?>">
      <div class="container">
      <hr />
    
      <?php if($this->countModules('breadcrumbsload')) : ?>
        <!-- Including breadcrumbs navigation -->
        <?php require_once('includes/breadcrumbs.php'); ?>    
      <?php endif; ?>      
    
      <?php if($this->countModules('top_left25 or top_left_center25 or top_right_center25 or top_right25 or top_left33 or top_center33 or top_center33 or top_right50 or top_left50 or top_right66 or top_left66')) : ?>   
        <!-- Including top content area -->
          <?php require_once('includes/top.php'); ?>
      <?php endif; ?>  
  
      <!-- Including inner content area -->
         <?php require_once('includes/innercontent.php'); ?>
  
      <?php if($this->countModules('bottom_left25 or bottom_left_center25 or bottom_right_center25 or bottom_right25 or bottom_right50 or bottom_left50 or bottom_left33 or bottom_center33 or bottom_center33 or bottom_left66 or bottom_right66')) : ?>   
        <!-- Including bottom content area-->
        <?php require_once('includes/bottom.php'); ?>
      <?php endif; ?>
      </div>
    </div>

<!-- ****************** Footer Area ****************** -->
    <?php if($this->countModules('footer_left or footer_center_left or footer_center or footer_center_right or footer_right')) : ?> 
      <!-- Including footer content area -->
      <?php require_once('includes/footer.php'); ?>
    <?php endif; ?>

  </div> <!-- div.inner_wrapper ends here-->
  <!-- Including subfooter content area -->
<?php require_once('includes/subfooter.php'); ?>
</div> <!-- div.wrapper ends here-->

</body>
</html>